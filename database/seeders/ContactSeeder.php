<?php

namespace Database\Seeders;

use App\Models\ContactPage;
use App\Models\Languages;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Languages::all() as $language) {
            ContactPage::create([
                'language' => $language->code,
                'title' => 'Contact '.$language->code,
                'slug' => 'contact-'.$language->code,
                'description' => 'Contact us',
                'meta_description' => 'Contact us',
                'meta_keywords' => 'Contact us',
                'maps' => 'https://maps.google.com/maps?q=40.712776,-74.005974',
            ]);
        }
    }
}
