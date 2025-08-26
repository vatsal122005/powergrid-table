<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Log;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function test_guest_cannot_access_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->assertPathIs('/login')
                ->assertSee('Email');
        });
    }

    #[Test]
    public function user_cannot_submit_login_with_empty_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->click('@login-button')
                ->pause(6000)
                ->screenshot('empty-fields')
                ->waitFor('@email-error', 5) // waits for email error
                ->screenshot('empty-fields-error')
                ->assertSeeIn('@email-error', 'The email field is required.');
            // ->assertSee( 'The password field is required.');
        });
    }

    #[Test]
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            Log::info('Starting invalid login test');
            $browser->visit('/login')
                ->waitFor('@email-input', 10)
                ->type('@email-input', 'nherman@example.com')
                ->type('@password-input', 'wrong-password')
                ->click('@login-button')
                ->screenshot('invalid-credentials');
            Log::info('Invalid login test completed');
        });
    }

    #[Test]
    public function test_user_can_login(): void
    {
        $this->browse(function (Browser $browser) {
            Log::info('Starting login test');

            $browser->visit('/login')
                ->waitFor('@email-input') // waits for email input
                ->type('@email-input', 'test@example.com') // using dusk selector
                ->type('@password-input', 'password123')
                ->click('@login-button') // click login button
                ->pause(6000)
                ->screenshot('after-login-click')
                ->waitForLocation('/dashboard', 20)
                ->assertPathIs('/dashboard')
                ->assertSee("You're logged in!");

            Log::info('User logged Test in successfully');
        });
    }

    #[Test]
    public function test_user_can_logout(): void
    {
        $this->browse(function (Browser $browser) {
            Log::info('Starting logout test');
            $browser->visit('/dashboard')
                ->screenshot('before-logout-click')
                ->press('@nav-user-dropdown-trigger')
                ->pause(3000)
                ->screenshot('before-logout-click')
                ->click('@nav-logout-button')
                ->pause(3000)
                ->screenshot('after-logout-click')
                ->assertPathIs('/');
            Log::info('User logged out successfully');
        });
    }
}
