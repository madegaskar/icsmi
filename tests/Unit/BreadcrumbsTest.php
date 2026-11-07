<?php

namespace Tests\Unit;

use App\Support\Breadcrumbs;
use Illuminate\Http\Request;
use Tests\TestCase;

class BreadcrumbsTest extends TestCase
{
    public function test_home_has_no_trail(): void
    {
        $this->assertSame([], Breadcrumbs::for(Request::create('/'), null));
    }

    public function test_path_label_is_title_cased_when_no_title_is_given(): void
    {
        $trail = Breadcrumbs::for(Request::create('/article-processing-charge'), null);

        $this->assertSame('Article Processing Charge', $trail[1]['label']);
        $this->assertNull($trail[1]['url']);
    }

    public function test_given_title_overrides_the_path_label(): void
    {
        $trail = Breadcrumbs::for(Request::create('/contact'), 'Contact');

        $this->assertSame('Contact', $trail[1]['label']);
        $this->assertSame('Home', $trail[0]['label']);
    }
}
