<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_urls_use_app_url_even_when_the_request_host_differs(): void
    {
        config(['app.url' => 'https://share.example.test']);
        (new AppServiceProvider($this->app))->boot();

        $this->withServerVariables(['HTTP_HOST' => 'localhost:8001'])
            ->get('/')
            ->assertOk();

        $this->assertSame('https://share.example.test/build/app.css', url('/build/app.css'));
    }
}
