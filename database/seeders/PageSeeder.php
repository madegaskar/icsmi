<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Create every public page with placeholder text; existing content is left untouched.
     */
    public function run(): void
    {
        $titles = [
            'about' => 'About the Conference',
            'scope' => 'Focus and Scope',
            'call-for-papers' => 'Call for Papers',
            'important-dates' => 'Important Dates',
            'registration' => 'Registration and Fees',
            'program' => 'Program',
            'author-guidelines' => 'Author Guidelines',
            'ethics' => 'Publication Ethics',
            'contact' => 'Contact',
            'privacy-statement' => 'Privacy Statement',
        ];

        foreach ($titles as $slug => $title) {
            Page::firstOrCreate(['slug' => $slug], [
                'title' => $title,
                'body' => '*This content has not been provided yet. An administrator will publish it here.*',
                'published_at' => now(),
            ]);
        }
    }
}
