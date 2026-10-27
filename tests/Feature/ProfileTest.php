<?php

namespace Tests\Feature;

use App\Filament\Pages\SuperAdminProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_super_admin_profile_page_is_available(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this
            ->actingAs($user)
            ->get('/admin/super-admin-profile')
            ->assertOk();
    }

    public function test_super_admin_can_change_email_and_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $user->assignRole('super_admin');

        Livewire::actingAs($user)
            ->test(SuperAdminProfile::class)
            ->set('data.new_email', 'new-admin@example.com')
            ->set('data.new_password', 'NewPassword123!')
            ->set('data.new_password_confirmation', 'NewPassword123!')
            ->set('data.current_password', 'password')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('new-admin@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    public function test_super_admin_cannot_change_credentials_without_current_password(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);
        $user->assignRole('super_admin');

        Livewire::actingAs($user)
            ->test(SuperAdminProfile::class)
            ->set('data.new_email', 'new-admin@example.com')
            ->set('data.current_password', 'wrong-password')
            ->call('save')
            ->assertHasErrors(['data.current_password']);

        $this->assertSame('admin@example.com', $user->fresh()->email);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'admin_title' => 'Operations Lead',
                'admin_bio' => 'Managing the jersey store operations.',
                'phone' => '+8801700000000',
                'timezone' => 'Asia/Dhaka',
                'theme' => 'dark',
                'preferred_language' => 'bn',
                'signature' => 'Dhakebir Jersey House',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('Operations Lead', $user->admin_title);
        $this->assertSame('Managing the jersey store operations.', $user->admin_bio);
        $this->assertSame('+8801700000000', $user->phone);
        $this->assertSame('Asia/Dhaka', $user->timezone);
        $this->assertSame('dark', $user->theme);
        $this->assertSame('bn', $user->preferred_language);
        $this->assertSame('Dhakebir Jersey House', $user->signature);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
