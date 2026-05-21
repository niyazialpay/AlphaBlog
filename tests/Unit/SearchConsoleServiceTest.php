<?php

namespace Tests\Unit;

use App\Models\Settings\GeneralSettings;
use App\Services\GoogleSearchConsoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchConsoleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        GeneralSettings::firstOrCreate([], []);
    }

    public function test_returns_empty_performance_when_credentials_file_missing(): void
    {
        $credPath = storage_path('app/analytics/service-account-credentials.json');
        $exists = file_exists($credPath);
        if ($exists) {
            rename($credPath, $credPath.'.bak');
        }

        try {
            $service = new GoogleSearchConsoleService;
            $result = $service->getPerformance(now()->subDays(7), now());
            $this->assertSame(['current' => [], 'previous' => []], $result);
        } finally {
            if ($exists) {
                rename($credPath.'.bak', $credPath);
            }
        }
    }

    public function test_returns_empty_keywords_when_credentials_file_missing(): void
    {
        $credPath = storage_path('app/analytics/service-account-credentials.json');
        $exists = file_exists($credPath);
        if ($exists) {
            rename($credPath, $credPath.'.bak');
        }

        try {
            $service = new GoogleSearchConsoleService;
            $result = $service->getKeywords(now()->subDays(7), now());
            $this->assertIsArray($result);
            $this->assertEmpty($result);
        } finally {
            if ($exists) {
                rename($credPath.'.bak', $credPath);
            }
        }
    }

    public function test_returns_empty_trend_when_credentials_file_missing(): void
    {
        $credPath = storage_path('app/analytics/service-account-credentials.json');
        $exists = file_exists($credPath);
        if ($exists) {
            rename($credPath, $credPath.'.bak');
        }

        try {
            $service = new GoogleSearchConsoleService;
            $result = $service->getClicksTrend(now()->subDays(7), now());
            $this->assertIsArray($result);
            $this->assertEmpty($result);
        } finally {
            if ($exists) {
                rename($credPath.'.bak', $credPath);
            }
        }
    }
}
