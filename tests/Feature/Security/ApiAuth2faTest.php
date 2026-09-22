<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class ApiAuth2faTest extends TestCase
{
    public function test_api_two_factor_enforcement_defaults_off(): void
    {
        $this->assertFalse(
            (bool) config('sanctum.require_two_factor'),
            'API_REQUIRE_2FA must default to false so existing mobile/API clients are not locked out on deploy'
        );
    }

    public function test_sanctum_token_expiration_defaults_null(): void
    {
        $this->assertNull(
            config('sanctum.expiration'),
            'SANCTUM token expiration must default null or existing API tokens are retroactively invalidated'
        );
    }
}
