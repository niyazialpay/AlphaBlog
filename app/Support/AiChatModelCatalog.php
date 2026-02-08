<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Ai\Ai;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Providers\Provider;
use Throwable;

class AiChatModelCatalog
{
    public function getAvailableTextProviders(): array
    {
        $providers = [];

        foreach (array_keys(config('ai.providers', [])) as $providerName) {
            $provider = $this->resolveTextProvider($providerName);

            if (! $provider || ! $this->isProviderConfigured($provider)) {
                continue;
            }

            $models = $this->buildModelOptions($provider);

            if ($models === []) {
                continue;
            }

            $providers[$providerName] = [
                'key' => $providerName,
                'label' => Str::headline($providerName),
                'default_model' => $provider->defaultTextModel(),
                'models' => $models,
            ];
        }

        return $providers;
    }

    public function hasAvailableTextProvider(): bool
    {
        return $this->getAvailableTextProviders() !== [];
    }

    public function getDefaultProviderName(array $providers): ?string
    {
        if ($providers === []) {
            return null;
        }

        $configuredDefault = config('ai.default');

        if (is_string($configuredDefault) && array_key_exists($configuredDefault, $providers)) {
            return $configuredDefault;
        }

        return array_key_first($providers);
    }

    public function getDefaultModelName(array $providers, ?string $providerName): ?string
    {
        if (! $providerName || ! array_key_exists($providerName, $providers)) {
            return null;
        }

        return $providers[$providerName]['default_model'] ?? null;
    }

    protected function resolveTextProvider(string $providerName): ?TextProvider
    {
        try {
            return Ai::textProvider($providerName);
        } catch (Throwable) {
            return null;
        }
    }

    protected function isProviderConfigured(TextProvider $provider): bool
    {
        if (! $provider instanceof Provider) {
            return true;
        }

        $key = trim((string) Arr::get($provider->providerCredentials(), 'key', ''));

        return $key !== '';
    }

    protected function buildModelOptions(TextProvider $provider): array
    {
        $models = [];

        foreach ([
            ['name' => $provider->defaultTextModel(), 'kind' => 'default'],
            ['name' => $provider->cheapestTextModel(), 'kind' => 'cheapest'],
            ['name' => $provider->smartestTextModel(), 'kind' => 'smartest'],
        ] as $definition) {
            if (! filled($definition['name'])) {
                continue;
            }

            if (! isset($models[$definition['name']])) {
                $models[$definition['name']] = [
                    'name' => $definition['name'],
                    'kinds' => [],
                ];
            }

            $models[$definition['name']]['kinds'][] = $definition['kind'];
        }

        return array_values($models);
    }
}
