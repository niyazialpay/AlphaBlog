<?php

namespace Tests;

use App\Support\Panel\PanelMenu;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * `PanelMenu::$ledger` sürec ömrü boyunca memoize edilir. Bir test defteri
     * daralttıysa (bkz. PanelTestCase::migrateScreens) bu daraltma sonraki test
     * sınıflarına sızar ve Inertia beklenen yerlerde Blade yanıtı alınır.
     * Her testten önce sıfırlamak sıralama bağımlılığını ortadan kaldırır.
     */
    protected function setUp(): void
    {
        parent::setUp();

        PanelMenu::flushLedger();
    }
}
