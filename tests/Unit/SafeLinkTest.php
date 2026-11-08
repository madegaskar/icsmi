<?php

namespace Tests\Unit;

use App\Rules\SafeLink;
use PHPUnit\Framework\TestCase;

class SafeLinkTest extends TestCase
{
    public function test_accepts_a_plain_site_path_and_https_link(): void
    {
        $this->assertTrue(SafeLink::accepts('/archives'));
        $this->assertTrue(SafeLink::accepts('https://example.org/page'));
    }

    public function test_rejects_a_value_with_a_trailing_newline(): void
    {
        $this->assertFalse(SafeLink::accepts("/archives\n"));
        $this->assertFalse(SafeLink::accepts("https://example.org/page\n"));
    }
}
