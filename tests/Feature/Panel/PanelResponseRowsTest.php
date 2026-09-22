<?php

namespace Tests\Feature\Panel;

use App\Support\Panel\PanelResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PanelResponseRowsTest extends TestCase
{
    public function test_rows_does_not_mutate_the_source_paginator(): void
    {
        $paginator = new LengthAwarePaginator(
            new Collection([(object) ['id' => 1], (object) ['id' => 2]]),
            total: 2,
            perPage: 15,
            currentPage: 1,
        );

        $projected = PanelResponse::rows($paginator, fn ($row) => ['id' => $row->id]);

        $this->assertSame(
            [['id' => 1], ['id' => 2]],
            $projected->getCollection()->all(),
            'Projeksiyon beklenen duz diziyi uretmedi.'
        );

        $this->assertIsObject(
            $paginator->getCollection()->first(),
            'Kaynak paginator YERINDE degistirildi: blade fallback bozulur.'
        );

        $this->assertNotSame(
            $paginator,
            $projected,
            'rows() ayni nesneyi dondurdu; klon uretmiyor.'
        );
    }

    public function test_through_mutates_in_place_which_is_why_rows_exists(): void
    {
        $paginator = new LengthAwarePaginator(
            new Collection([(object) ['id' => 1]]),
            total: 1,
            perPage: 15,
            currentPage: 1,
        );

        $paginator->through(fn ($row) => ['id' => $row->id]);

        $this->assertIsArray(
            $paginator->getCollection()->first(),
            'through() artik yerinde degistirmiyor; rows() yardimcisi gozden gecirilebilir.'
        );
    }

    public function test_rows_preserves_pagination_metadata(): void
    {
        $paginator = new LengthAwarePaginator(
            new Collection([(object) ['id' => 1]]),
            total: 42,
            perPage: 10,
            currentPage: 3,
        );

        $projected = PanelResponse::rows($paginator, fn ($row) => ['id' => $row->id]);

        $this->assertSame(42, $projected->total());
        $this->assertSame(10, $projected->perPage());
        $this->assertSame(3, $projected->currentPage());
    }
}
