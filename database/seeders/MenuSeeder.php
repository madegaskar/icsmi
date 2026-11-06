<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Fills the header and footer with the links the site started with.
     * Does nothing when any menu item already exists, so admin edits are never overwritten.
     */
    public function run(): void
    {
        if (MenuItem::query()->exists()) {
            return;
        }

        $header = [
            ['Home', '/'],
            ['About Us', '/about'],
            ['Current', '/current'],
            ['Archives', '/archives'],
            ['Contact', '/contact'],
            ['Search', '/search'],
            ['Register', '/register'],
            ['Login', '/login'],
        ];

        foreach ($header as $order => [$label, $url]) {
            $this->leaf(MenuItem::LOCATION_HEADER, $label, $url, $order + 1);
        }

        $footer = [
            ['Conference', [['Focus and Scope', '/scope'], ['Important Dates', '/important-dates'], ['Program', '/program'], ['Speakers', '/speakers'], ['Registration and Fees', '/registration']]],
            ['Publication', [['Current Proceedings', '/current'], ['Archives', '/archives'], ['Editorial Board', '/editorial-board'], ['Author Guidelines', '/author-guidelines'], ['Publication Ethics', '/ethics']]],
            ['ICSMI', [['Contact', '/contact']]],
        ];

        foreach ($footer as $order => [$label, $children]) {
            $group = MenuItem::create([
                'location' => MenuItem::LOCATION_FOOTER,
                'label' => $label,
                'link_type' => MenuItem::TYPE_GROUP,
                'sort_order' => $order + 1,
                'is_visible' => true,
            ]);

            foreach ($children as $childOrder => [$childLabel, $url]) {
                $this->leaf(MenuItem::LOCATION_FOOTER, $childLabel, $url, $childOrder + 1, $group->id);
            }
        }
    }

    private function leaf(string $location, string $label, string $url, int $order, ?int $parentId = null): void
    {
        MenuItem::create([
            'location' => $location,
            'parent_id' => $parentId,
            'label' => $label,
            'link_type' => MenuItem::TYPE_INTERNAL,
            'url' => $url,
            'sort_order' => $order,
            'is_visible' => true,
        ]);
    }
}
