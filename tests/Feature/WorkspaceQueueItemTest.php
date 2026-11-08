<?php

namespace Tests\Feature;

use Tests\TestCase;

class WorkspaceQueueItemTest extends TestCase
{
    public function test_queue_item_shows_identity_status_date_and_view_action(): void
    {
        $html = $this->blade('<x-queue-item code="PUB-2026-7" title="Judul Naskah" href="/author/manuscripts/7" label="Submitted" date="7 Nov 2026" />');

        $html->assertSee('data-queue-item', false)
            ->assertSee('PUB-2026-7')
            ->assertSee('Judul Naskah')
            ->assertSee('href="/author/manuscripts/7"', false)
            ->assertSee('Submitted')
            ->assertSee('7 Nov 2026')
            ->assertSee('View');
    }
}
