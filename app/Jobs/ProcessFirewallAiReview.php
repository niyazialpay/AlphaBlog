<?php

namespace App\Jobs;

use App\Ai\Agents\FirewallRequestReviewAgent;
use App\Models\Firewall\Firewall;
use App\Models\Firewall\FirewallLogs;
use App\Models\IPFilter\IPList;
use App\Support\AiChatModelCatalog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class ProcessFirewallAiReview implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $maxExceptions = 1;

    public function __construct(protected array $payload, protected int $firewallId) {}

    /**
     * Execute the job.
     */
    public function handle(AiChatModelCatalog $modelCatalog): void
    {
        $firewall = Firewall::query()->find($this->firewallId);

        if (! $firewall || ! $firewall->is_active || ! $firewall->ai_review_enabled) {
            return;
        }

        if (! $firewall->blacklist_rule_id) {
            return;
        }

        $providerCatalog = $modelCatalog->getAvailableTextProviders();

        if ($providerCatalog === []) {
            return;
        }

        [$provider, $model] = $this->resolveProviderAndModel($firewall, $providerCatalog, $modelCatalog);

        if (! $provider || ! $model) {
            return;
        }

        $cachedDecision = Cache::get($this->decisionCacheKey());

        if (is_array($cachedDecision)) {
            $this->applyDecision($cachedDecision, $firewall, fromCache: true);

            return;
        }

        try {
            $response = FirewallRequestReviewAgent::make()->prompt(
                prompt: $this->buildPrompt(),
                provider: $provider,
                model: $model,
                timeout: $this->resolveTimeout($firewall),
            );
        } catch (Throwable $e) {
            Log::warning('[FIREWALL_AI] Review failed', [
                'message' => $e->getMessage(),
                'provider' => $provider,
                'model' => $model,
                'ip' => $this->payload['ip'] ?? null,
                'fingerprint' => $this->payload['fingerprint'] ?? null,
            ]);

            return;
        }

        $decision = $this->extractDecision($response);

        if (! $decision) {
            return;
        }

        $decision['provider'] = $provider;
        $decision['model'] = $model;

        Cache::put(
            $this->decisionCacheKey(),
            $decision,
            now()->addSeconds($this->resolveCacheTtl($firewall)),
        );

        $this->applyDecision($decision, $firewall, fromCache: false);
    }

    protected function resolveProviderAndModel(
        Firewall $firewall,
        array $providerCatalog,
        AiChatModelCatalog $modelCatalog
    ): array {
        $provider = $firewall->ai_provider;

        if (! $provider || ! isset($providerCatalog[$provider])) {
            $provider = $modelCatalog->getDefaultProviderName($providerCatalog);
        }

        if (! $provider || ! isset($providerCatalog[$provider])) {
            return [null, null];
        }

        $availableModels = collect($providerCatalog[$provider]['models'])
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        $model = $firewall->ai_model;

        if (! $model || ! in_array($model, $availableModels, true)) {
            $model = $providerCatalog[$provider]['default_model']
                ?? $modelCatalog->getDefaultModelName($providerCatalog, $provider);
        }

        if (! $model) {
            return [null, null];
        }

        return [$provider, $model];
    }

    protected function resolveTimeout(Firewall $firewall): int
    {
        return max(1, min(30, (int) $firewall->ai_timeout_seconds));
    }

    protected function resolveCacheTtl(Firewall $firewall): int
    {
        return max(60, min(86400, (int) $firewall->ai_cache_ttl_seconds));
    }

    protected function buildPrompt(): string
    {
        $context = [
            'request' => [
                'ip' => Arr::get($this->payload, 'ip'),
                'method' => Arr::get($this->payload, 'method'),
                'url' => Arr::get($this->payload, 'url'),
                'path' => Arr::get($this->payload, 'path'),
                'query' => Arr::get($this->payload, 'query'),
                'user_agent' => Arr::get($this->payload, 'user_agent'),
                'headers' => Arr::get($this->payload, 'headers', []),
                'body_preview' => Arr::get($this->payload, 'body_preview'),
                'is_ajax' => Arr::get($this->payload, 'is_ajax'),
                'is_secure' => Arr::get($this->payload, 'is_secure'),
            ],
            'signals' => Arr::get($this->payload, 'signals', []),
        ];

        $jsonContext = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Analyze the following HTTP request context and classify it.

Decision policy:
- malicious: clear attack indicators exist.
- benign: normal traffic characteristics dominate.
- uncertain: mixed or insufficient evidence.

Do not include markdown. Return structured output only.

Context:
{$jsonContext}
PROMPT;
    }

    protected function extractDecision(mixed $response): ?array
    {
        $decoded = [];

        if ($response instanceof StructuredAgentResponse) {
            $decoded = $response->toArray();
        } elseif (is_object($response) && property_exists($response, 'text')) {
            $jsonDecoded = json_decode((string) $response->text, true);
            if (is_array($jsonDecoded)) {
                $decoded = $jsonDecoded;
            }
        } elseif (is_string($response)) {
            $jsonDecoded = json_decode($response, true);
            if (is_array($jsonDecoded)) {
                $decoded = $jsonDecoded;
            }
        }

        if (! is_array($decoded)) {
            return null;
        }

        $decision = Str::lower((string) Arr::get($decoded, 'decision', 'uncertain'));

        if (! in_array($decision, ['malicious', 'benign', 'uncertain'], true)) {
            $decision = 'uncertain';
        }

        $attackType = Str::lower((string) Arr::get($decoded, 'attack_type', 'unknown'));

        if (! in_array($attackType, [
            'sql_injection',
            'xss',
            'path_traversal',
            'command_injection',
            'credential_stuffing',
            'bot_abuse',
            'probe',
            'unknown',
        ], true)) {
            $attackType = 'unknown';
        }

        $confidence = max(0, min(100, (int) Arr::get($decoded, 'confidence', 0)));
        $reason = Str::limit(trim((string) Arr::get($decoded, 'reason', 'No reason provided')), 180);

        return [
            'decision' => $decision,
            'confidence' => $confidence,
            'attack_type' => $attackType,
            'reason' => $reason,
        ];
    }

    protected function applyDecision(array $decision, Firewall $firewall, bool $fromCache): void
    {
        $shouldAutoBlock = $firewall->ai_enforcement_enabled
            && $decision['decision'] === 'malicious'
            && (int) $decision['confidence'] >= $this->resolveConfidenceThreshold($firewall);

        if ($shouldAutoBlock) {
            if ($this->isIpAlreadyBlocked($firewall->blacklist_rule_id)) {
                return;
            }

            ProcessFirewallBlock::dispatch(
                $this->resolveIp(),
                $this->buildAutoBlockReason($decision, $fromCache),
                $this->resolveUserAgent(),
                $this->resolveUrl(),
                $this->buildRequestData($decision, $fromCache),
                $firewall->blacklist_rule_id,
            );

            return;
        }

        if ($fromCache) {
            return;
        }

        FirewallLogs::query()->create([
            'ip' => $this->resolveIp(),
            'user_agent' => $this->resolveUserAgent(),
            'url' => $this->resolveUrl(),
            'reason' => $this->buildReviewReason($decision),
            'request_data' => $this->encodeJson($this->buildRequestData($decision, fromCache: false)),
            'ip_filter_id' => $firewall->blacklist_rule_id,
            'ip_list_id' => null,
        ]);
    }

    protected function resolveConfidenceThreshold(Firewall $firewall): int
    {
        return max(1, min(100, (int) $firewall->ai_confidence_threshold));
    }

    protected function buildAutoBlockReason(array $decision, bool $fromCache): string
    {
        $cacheLabel = $fromCache ? ' (cached)' : '';
        $reason = Str::limit((string) ($decision['reason'] ?? 'malicious request detected'), 120);

        return sprintf(
            'AI Firewall%s: %s (%d%%) - %s',
            $cacheLabel,
            strtoupper((string) $decision['attack_type']),
            (int) $decision['confidence'],
            $reason
        );
    }

    protected function buildReviewReason(array $decision): string
    {
        $reason = Str::limit((string) ($decision['reason'] ?? ''), 120);

        return sprintf(
            'AI Review: %s (%d%%) - %s',
            strtoupper((string) $decision['decision']),
            (int) $decision['confidence'],
            $reason
        );
    }

    protected function buildRequestData(array $decision, bool $fromCache): array
    {
        return [
            'ai' => [
                'decision' => $decision['decision'] ?? 'uncertain',
                'confidence' => (int) ($decision['confidence'] ?? 0),
                'attack_type' => $decision['attack_type'] ?? 'unknown',
                'reason' => $decision['reason'] ?? null,
                'provider' => $decision['provider'] ?? null,
                'model' => $decision['model'] ?? null,
                'from_cache' => $fromCache,
            ],
            'request' => Arr::only($this->payload, [
                'fingerprint',
                'method',
                'path',
                'query',
                'url',
                'ip',
                'user_agent',
                'headers',
                'signals',
                'body_preview',
            ]),
        ];
    }

    protected function isIpAlreadyBlocked(?int $blacklistRuleId): bool
    {
        if (! $blacklistRuleId) {
            return false;
        }

        return IPList::query()
            ->where('filter_id', $blacklistRuleId)
            ->where('ip', $this->resolveIp())
            ->exists();
    }

    protected function decisionCacheKey(): string
    {
        $fingerprint = (string) Arr::get($this->payload, 'fingerprint', '');

        if ($fingerprint === '') {
            $fingerprint = hash('sha256', json_encode($this->payload) ?: '');
        }

        return 'firewall:ai:decision:'.$fingerprint;
    }

    protected function resolveIp(): string
    {
        return (string) Arr::get($this->payload, 'ip', '0.0.0.0');
    }

    protected function resolveUrl(): string
    {
        return (string) Arr::get($this->payload, 'url', '/');
    }

    protected function resolveUserAgent(): string
    {
        $userAgent = trim((string) Arr::get($this->payload, 'user_agent', ''));

        return $userAgent !== '' ? $userAgent : 'not detected';
    }

    protected function encodeJson(array $value): ?string
    {
        $encoded = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return is_string($encoded) ? $encoded : null;
    }
}
