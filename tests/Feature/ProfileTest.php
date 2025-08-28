<?php

namespace Tests\Feature;

use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    #[Test]
    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get('/profile')
            ->assertOk()
            ->assertSee('profile.update-profile-information-form')
            ->assertSee('profile.update-password-form')
            ->assertSee('profile.delete-user-form');
    }

    #[Test]
    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', 'test120405@example.com')
            ->call('updateProfileInformation')
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test120405@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    #[Test]
    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation')
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    #[Test]
    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    #[Test]
    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh());
    }
}
