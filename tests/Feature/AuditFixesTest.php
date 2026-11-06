<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_signs_out_every_existing_session_of_the_user(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.test']);
        $user->assignRole(Role::Author);
        DB::table('sessions')->insert([
            'id' => 'session-opened-before-reset',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'x',
            'last_activity' => time(),
        ]);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'reset@example.test',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    }

    public function test_database_rejects_a_second_unknown_country_row_for_the_same_day(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);

        $row = ['visited_on' => now()->toDateString(), 'country_code' => '', 'count' => 1];
        DB::table('page_visits')->insert($row);
        DB::table('page_visits')->insert($row);
    }

    public function test_country_header_is_ignored_when_the_request_does_not_come_from_a_trusted_proxy(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.1']);

        $this->withHeaders(['CF-IPCountry' => 'ZZ'])->get('/')->assertOk();

        $this->assertDatabaseMissing('page_visits', ['country_code' => 'ZZ']);
        $this->assertDatabaseHas('page_visits', ['country_code' => '', 'count' => 1]);
    }

    public function test_country_header_is_used_when_the_request_comes_from_a_trusted_proxy(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.1,10.0.1.0/24']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.7'])
            ->withHeaders(['CF-IPCountry' => 'ID'])
            ->get('/')
            ->assertOk();

        $this->assertDatabaseHas('page_visits', ['country_code' => 'ID', 'count' => 1]);
    }

    public function test_country_header_is_ignored_when_no_trusted_proxy_is_configured(): void
    {
        config(['trustedproxy.proxies' => null]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->withHeaders(['CF-IPCountry' => 'MY'])
            ->get('/')
            ->assertOk();

        $this->assertDatabaseMissing('page_visits', ['country_code' => 'MY']);
    }
}
