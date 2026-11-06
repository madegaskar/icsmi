<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StatusBadgeTest extends TestCase
{
    public function test_rejected_status_is_shown_red_with_a_cross(): void
    {
        $html = Blade::render('<x-status-badge label="Rejected" />');

        $this->assertStringContainsString('bg-red-50 text-red-700', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('Rejected', $html);
    }

    public function test_other_statuses_keep_the_green_badge_without_a_cross(): void
    {
        $html = Blade::render('<x-status-badge label="Accepted" />');

        $this->assertStringContainsString('bg-emerald-50 text-emerald-900', $html);
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringNotContainsString('bg-red-50', $html);
    }
}
