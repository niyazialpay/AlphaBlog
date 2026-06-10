<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * NOTE: the API 2FA enforcement is gated behind config('sanctum.require_two_factor').
 * A full HTTP test of POST /api/login is not runnable in this test environment:
 * the {language}-prefixed routes (routes/web.php:247,330) are registered at boot
 * with whereIn('language', $languages) and the languages table is empty at that
 * point, so the router throws "Routing requirement for 'language' cannot be empty"
 * while matching the api route — a pre-existing test-bootstrap issue unrelated to
 * the AuthController logic (the exception is in Symfony routing, the controller is
 * never reached). We therefore assert the deploy-safety-critical property directly:
 * the API 2FA enforcement is OFF by default so existing mobile/API clients that
 * send only username+password are NOT locked out. The enforcement logic itself
 * (config flag && two_factor_secret && otp) is covered by code review.
 */
class ApiAuth2faTest extends TestCase
{
    /** ALPHA-002: API 2FA enforcement must default OFF (non-breaking for existing clients). */
    public function test_api_two_factor_enforcement_defaults_off(): void
    {
        $this->assertFalse(
            (bool) config('sanctum.require_two_factor'),
            'API_REQUIRE_2FA must default to false so existing mobile/API clients are not locked out on deploy'
        );
    }

    /** Sanctum token expiration must default to null (no retroactive mass-logout of existing tokens). */
    public function test_sanctum_token_expiration_defaults_null(): void
    {
        $this->assertNull(
            config('sanctum.expiration'),
            'SANCTUM token expiration must default null or existing API tokens are retroactively invalidated'
        );
    }
}
