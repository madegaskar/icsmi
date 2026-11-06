<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStatus;
use App\Enums\Role;
use App\Models\Manuscript;
use App\Models\User;
use App\Notifications\ManuscriptStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileTabsAndBellTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create(['name' => 'Penulis Uji', 'email' => 'penulis@example.test']);
        $this->author->assignRole(Role::Author);
    }

    private function manuscript(): Manuscript
    {
        return Manuscript::create([
            'code' => 'ICSMI-TEST-0001',
            'submitter_id' => $this->author->id,
            'title' => 'Naskah Uji',
            'abstract' => 'Abstrak uji.',
            'keywords' => 'uji',
            'status' => ManuscriptStatus::Screening,
            'current_version' => 1,
            'submitted_at' => now(),
        ]);
    }

    public function test_every_profile_tab_opens_for_an_author(): void
    {
        foreach (['identity', 'contact', 'roles', 'public', 'password', 'notifications', 'api'] as $tab) {
            $this->actingAs($this->author)->get(route('profile.edit', ['tab' => $tab]))->assertOk();
        }

        $this->actingAs($this->author)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Identity')
            ->assertSee('Contact')
            ->assertSee('Roles')
            ->assertSee('Public')
            ->assertSee('Password')
            ->assertSee('Notifications')
            ->assertSee('API Key');
    }

    public function test_api_key_tab_says_it_is_not_available_yet(): void
    {
        $this->actingAs($this->author)->get(route('profile.edit', ['tab' => 'api']))
            ->assertSee('Belum tersedia');
    }

    public function test_roles_tab_lists_the_accounts_roles(): void
    {
        $this->actingAs($this->author)->get(route('profile.edit', ['tab' => 'roles']))
            ->assertSee('Author');
    }

    public function test_contact_tab_saves_the_phone_number(): void
    {
        $this->actingAs($this->author)->patch(route('profile.update'), [
            'email' => $this->author->email,
            'phone' => '081234567890',
        ])->assertSessionHasNoErrors();

        $this->assertSame('081234567890', $this->author->fresh()->phone);
    }

    public function test_identity_tab_saves_name_affiliation_and_country_without_touching_email(): void
    {
        $this->actingAs($this->author)->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'affiliation' => 'Universitas Jenderal Soedirman',
            'country' => 'Indonesia',
        ])->assertSessionHasNoErrors();

        $fresh = $this->author->fresh();
        $this->assertSame('Nama Baru', $fresh->name);
        $this->assertSame('Universitas Jenderal Soedirman', $fresh->affiliation);
        $this->assertSame('penulis@example.test', $fresh->email);
    }

    public function test_notification_setting_can_turn_off_status_emails(): void
    {
        $this->actingAs($this->author)->patch(route('profile.update'), [
            'email' => $this->author->email,
            'mail_status_updates' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertFalse((bool) $this->author->fresh()->mail_status_updates);
    }

    public function test_status_change_is_kept_in_the_bell_even_when_email_is_off(): void
    {
        Notification::fake();
        $this->author->update(['mail_status_updates' => false]);
        $manuscript = $this->manuscript();

        $this->author->notify(new ManuscriptStatusChanged($manuscript, ManuscriptStatus::InReview, null));

        Notification::assertSentTo($this->author, ManuscriptStatusChanged::class, function ($notification, $channels) {
            return $channels === ['database'];
        });
    }

    public function test_bell_shows_the_unread_count_and_marks_them_read(): void
    {
        $manuscript = $this->manuscript();
        $this->author->notify(new ManuscriptStatusChanged($manuscript, ManuscriptStatus::InReview, null));

        $this->actingAs($this->author)->get('/author')
            ->assertOk()
            ->assertSee('data-unread-count="1"', false);

        $this->actingAs($this->author)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('In Review');

        $this->actingAs($this->author)->post(route('notifications.read'))->assertRedirect();

        $this->assertSame(0, $this->author->fresh()->unreadNotifications()->count());
    }
}
