<?php

namespace Tests\Feature;

use Tests\TestCase;

class WorkflowHeaderTest extends TestCase
{
    public function test_workflow_header_shows_back_link_identification_and_status(): void
    {
        $this->blade(
            '<x-workflow-header back-href="/author/dashboard" back-label="Back to My Manuscripts" code="ICSMI-7" title="Judul Naskah" status-label="Submitted" />'
        )->assertSee('data-workflow-header', false)
            ->assertSee('href="/author/dashboard"', false)
            ->assertSee('Back to My Manuscripts')
            ->assertSee('ICSMI-7')
            ->assertSee('Judul Naskah')
            ->assertSee('Submitted');
    }
}
