<?php

namespace Tests\Feature;

use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProceedingDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_proceeding_description_renders_markdown_emphasis(): void
    {
        $issue = Issue::factory()->create(['description' => 'Teks **tebal** dan *miring*.']);

        $this->get('/archives/'.$issue->slug)
            ->assertOk()
            ->assertSee('<strong>tebal</strong>', false)
            ->assertSee('<em>miring</em>', false)
            ->assertDontSee('**tebal**', false);
    }

    public function test_proceeding_description_strips_raw_html(): void
    {
        $issue = Issue::factory()->create(['description' => 'Aman <script>alert(1)</script> teks']);

        $this->get('/archives/'.$issue->slug)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
