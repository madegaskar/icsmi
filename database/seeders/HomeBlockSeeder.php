<?php

namespace Database\Seeders;

use App\Models\HomeBlock;
use Illuminate\Database\Seeder;

class HomeBlockSeeder extends Seeder
{
    /**
     * Recreates the home page blocks. The hero banner is no longer part of the default layout. Does nothing when any block already exists.
     */
    public function run(): void
    {
        if (HomeBlock::query()->exists()) {
            return;
        }

        HomeBlock::create([
            'type' => HomeBlock::TYPE_PROCEEDINGS,
            'sort_order' => 2,
            'is_visible' => true,
            'heading' => 'Latest Proceedings',
        ]);

        HomeBlock::create([
            'type' => HomeBlock::TYPE_ANNOUNCEMENTS,
            'sort_order' => 3,
            'is_visible' => true,
            'heading' => 'Announcements',
        ]);
    }
}
