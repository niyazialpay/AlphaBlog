<?php

namespace Tests\Unit;

use App\Services\GoogleSearchConsoleService;
use Tests\TestCase;

class SearchConsoleServiceTest extends TestCase
{
    public function test_returns_empty_performance_when_credentials_file_missing(): void
    {
        // In test env, the credentials file won't exist
        $service = new GoogleSearchConsoleService;
        $result = $service->getPerformance(
            now()->subDays(7),
            now()
        );
        $this->assertSame(['current' => [], 'previous' => []], $result);
    }

    public function test_returns_empty_keywords_when_credentials_file_missing(): void
    {
        $service = new GoogleSearchConsoleService;
        $result = $service->getKeywords(now()->subDays(7), now());
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_returns_empty_trend_when_credentials_file_missing(): void
    {
        $service = new GoogleSearchConsoleService;
        $result = $service->getClicksTrend(now()->subDays(7), now());
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
