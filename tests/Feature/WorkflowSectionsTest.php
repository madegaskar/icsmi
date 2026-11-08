<?php

namespace Tests\Feature;

use Tests\TestCase;

class WorkflowSectionsTest extends TestCase
{
    public function test_workflow_sections_link_to_section_ids(): void
    {
        $this->blade(
            '<x-workflow-sections :sections="[[\'id\' => \'files\', \'label\' => \'Files\'], [\'id\' => \'history\', \'label\' => \'History\']]" />'
        )->assertSee('aria-label="Sections"', false)
            ->assertSee('href="#files"', false)
            ->assertSee('Files')
            ->assertSee('href="#history"', false)
            ->assertSee('History');
    }
}
