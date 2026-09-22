<?php

namespace Tests;

use App\Support\Panel\PanelMenu;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        PanelMenu::flushLedger();
    }
}
