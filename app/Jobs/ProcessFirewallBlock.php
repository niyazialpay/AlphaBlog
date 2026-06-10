<?php

namespace App\Jobs;

use App\Models\Firewall\FirewallLogs;
use App\Models\IPFilter\IPList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFirewallBlock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $ip;

    protected string $reason;

    protected ?string $userAgent;

    protected string $url;

    protected array $requestData;

    protected ?int $blacklistRuleId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $ip, string $reason, ?string $userAgent, string $url, array $requestData, ?int $blacklistRuleId)
    {
        $this->ip = $ip;
        $this->reason = $reason;
        $this->userAgent = $userAgent;
        $this->url = $url;
        $this->requestData = $requestData;
        $this->blacklistRuleId = $blacklistRuleId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->blacklistRuleId) {
            return;
        }

        $ipList = IPList::firstOrCreate(
            [
                'filter_id' => $this->blacklistRuleId,
                'ip' => $this->ip,
            ],
            [
                'ip' => $this->ip,
                'filter_id' => $this->blacklistRuleId,
            ],
        );

        FirewallLogs::create([
            'ip' => $this->ip,
            'user_agent' => $this->userAgent ?? 'not detected',
            'url' => $this->url,
            'reason' => $this->reason,
            'request_data' => json_encode($this->redact($this->requestData)),
            'ip_filter_id' => $this->blacklistRuleId,
            'ip_list_id' => $ipList?->id,
        ]);
    }

    /**
     * Strip credential material from a captured request body before persisting it
     * to firewall_logs (which is rendered in the admin panel and included in DB
     * dumps). A login that trips a firewall rule must never leave a plaintext
     * password at rest.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function redact(array $data): array
    {
        $sensitive = ['_token', 'password', 'password_confirmation', 'current_password', 'old_password', 'new_password', 'api_key', 'secret', 'token', 'two_factor_secret', 'code'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redact($value);

                continue;
            }
            if (in_array(strtolower((string) $key), $sensitive, true) || str_contains(strtolower((string) $key), 'password')) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }
}
