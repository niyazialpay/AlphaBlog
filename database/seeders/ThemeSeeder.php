<?php

namespace Database\Seeders;

use App\Models\Themes;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        Themes::create([
            'name' => 'Default',
            'is_default' => true,
        ]);
    }
}
