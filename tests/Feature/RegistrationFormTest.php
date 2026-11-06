<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function registration(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Dr. Contoh Pendaftar',
            'affiliation' => 'Universitas Contoh',
            'country' => 'Indonesia',
            'email' => 'daftar@example.test',
            'password' => 'rahasia-panjang-123',
            'password_confirmation' => 'rahasia-panjang-123',
        ];
    }

    public function test_register_page_has_profile_login_and_consent_sections(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Profile')
            ->assertSee('Login')
            ->assertSee('Consent')
            ->assertSee('privacy statement', false);
    }

    public function test_registration_needs_the_privacy_consent(): void
    {
        $this->post('/register', $this->registration())->assertSessionHasErrors('privacy_consent');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_consent_is_recorded_and_the_announcement_choice_is_saved(): void
    {
        $this->post('/register', $this->registration([
            'privacy_consent' => '1',
            'mail_announcements' => '1',
        ]))->assertSessionHasNoErrors();

        $user = User::where('email', 'daftar@example.test')->firstOrFail();
        $this->assertNotNull($user->privacy_consent_at);
        $this->assertTrue($user->mail_announcements);
    }

    public function test_announcement_emails_stay_off_unless_chosen(): void
    {
        $this->post('/register', $this->registration(['privacy_consent' => '1']))->assertSessionHasNoErrors();

        $this->assertFalse(User::where('email', 'daftar@example.test')->firstOrFail()->mail_announcements);
    }
}
