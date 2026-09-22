<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_bootstraps(): void
    {
        $this->assertTrue(app()->bound('router'));
    }
}
