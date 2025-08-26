<?php

namespace Tests\Browser\Auth;

use Illuminate\Support\Facades\Log;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class RegistrationTest extends DuskTestCase
{
    #[Test]
    public function user_can_view_the_registration_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertPresent('@register-page')
                ->assertPresent('@register-form')
                ->assertPresent('@register-name-input')
                ->assertPresent('@register-email-input')
                ->assertPresent('@register-password-input')
                ->assertPresent('@register-password-confirmation-input')
                ->assertPresent('@register-submit-button');
        });
    }

    #[Test]
    public function already_registered_link_redirects_to_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->click('@register-already-registered-link')
                ->pause(3000)
                ->assertPathIs('/login')
                ->screenshot('login-page');
        });
    }

    #[Test]
    public function user_cannot_register_with_existing_email()
    {
        Log::info('Starting test: user_cannot_register_with_existing_email');

        $this->browse(function (Browser $browser) {
            Log::info('Visiting register page');
            $browser->visit('/register')

                ->type('@register-name-input', 'Another User')
                ->type('@register-email-input', 'test@example.com')
                ->type('@register-password-input', 'password123')
                ->type('@register-password-confirmation-input', 'password123')

                ->screenshot('register-page-before-submit')

                ->press('@register-submit-button')

                ->waitForText('The email has already been taken.')

                ->screenshot('register-page-after-submit')

                ->assertPresent('@register-email-error');
        });

        Log::info('Finished test: user_cannot_register_with_existing_email');
    }

    #[Test]
    public function user_cannot_register_with_mismatched_passwords()
    {
        Log::info('Starting test: user_cannot_register_with_mismatched_passwords');

        $this->browse(function (Browser $browser) {
            Log::info('Visiting register page');
            $browser->visit('/register')
                ->type('@register-name-input', 'Mismatch User')
                ->type('@register-email-input', 'mismatch@example.com')
                ->type('@register-password-input', 'password123')
                ->type('@register-password-confirmation-input', 'differentpass')
                ->screenshot('register-page-before-submit')
                ->press('@register-submit-button')
                ->pause(3000)
                ->screenshot('register-page-after-submit')
                ->waitForText('The password field confirmation does not match.')
                ->assertPresent('@register-password-error');
        });

        Log::info('Finished test: user_cannot_register_with_mismatched_passwords');
    }

    #[Test]
    public function user_can_register_with_valid_credentials()
    {
        Log::info('Starting test: user_can_register_with_valid_credentials');

        // Generate a random email for this test run
        $randomEmail = 'test'.uniqid().'@example.com';

        $this->browse(function (Browser $browser) use ($randomEmail) {
            Log::info('Visiting register page');
            $browser->visit('/register')
                ->type('@register-name-input', 'Test User')
                ->type('@register-email-input', $randomEmail)
                ->type('@register-password-input', 'password123')
                ->type('@register-password-confirmation-input', 'password123')
                ->screenshot('register-page')
                ->click('@register-submit-button')
                ->pause(3000)
                ->screenshot('after-register-click')
                ->assertPathIs('/dashboard') // redirected
                ->assertAuthenticated()
                ->screenshot('dashboard-page');

            Log::info('User registered successfully');
        });

        $this->assertDatabaseHas('users', [
            'email' => $randomEmail,
        ]);

        Log::info('Finished test: user_can_register_with_valid_credentials');
    }
}
