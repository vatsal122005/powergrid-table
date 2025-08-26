<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    private User $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password123'),
        ]);

        // Mock reCAPTCHA configuration
        config([
            'services.recaptcha.site_key' => 'test-site-key',
            'services.recaptcha.secret_key' => 'test-secret-key',
        ]);
    }

    #[Test]
    public function login_page_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Email');
        $response->assertSee('Password');
        $response->assertSee('Log in');
        $response->assertSee('Remember me');
    }

    #[Test]
    public function login_page_contains_recaptcha_script()
    {
        $response = $this->get('/login');

        $response->assertSee('https://www.google.com/recaptcha/api.js');
        $response->assertSee('grecaptcha.execute');
    }

    #[Test]
    public function user_can_login_with_valid_credentials_and_recaptcha()
    {
        // Mock successful reCAPTCHA response
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Log::shouldReceive('info')->times(5);
        Log::shouldReceive('debug')->once();

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'valid-token')
            ->call('login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    #[Test]
    public function login_fails_with_invalid_email()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'nonexistent@example.com')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'valid-token')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    #[Test]
    public function login_fails_with_invalid_password()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'wrongpassword')
            ->set('recaptcha_token', 'valid-token')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    #[Test]
    public function login_fails_when_recaptcha_verification_fails()
    {
        // Mock failed reCAPTCHA response
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('debug')->once();
        Log::shouldReceive('warning')->once();

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'invalid-token')
            ->call('login')
            ->assertHasErrors(['form.email' => 'reCAPTCHA verification failed. Please try again.']);

        $this->assertGuest();
    }

    #[Test]
    public function login_fails_when_recaptcha_score_is_too_low()
    {
        // Mock low score reCAPTCHA response
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.3, // Below 0.5 threshold
            ], 200),
        ]);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('debug')->once();
        Log::shouldReceive('warning')->once();

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'low-score-token')
            ->call('login')
            ->assertHasErrors(['form.email' => 'reCAPTCHA verification failed. Please try again.']);

        $this->assertGuest();
    }

    #[Test]
    public function login_requires_email()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', '')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'valid-token')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    #[Test]
    public function login_requires_password()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', '')
            ->set('recaptcha_token', 'valid-token')
            ->call('login')
            ->assertHasErrors(['form.password']);

        $this->assertGuest();
    }

    #[Test]
    public function email_must_be_valid_format()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'invalid-email')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'valid-token')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();
    }

    #[Test]
    public function remember_me_functionality_works()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('form.remember', true)
            ->set('recaptcha_token', 'valid-token')
            ->call('login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();

        // Check if remember token is set
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->remember_token);
    }

    #[Test]
    public function session_is_regenerated_after_successful_login()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        $originalSessionId = Session::getId();

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'valid-token')
            ->call('login');

        $newSessionId = Session::getId();
        $this->assertNotEquals($originalSessionId, $newSessionId);
    }

    #[Test]
    public function login_logs_are_written_correctly()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Log::shouldReceive('info')
            ->with('Login attempt started', Mockery::type('array'))
            ->once();

        Log::shouldReceive('debug')
            ->with('reCAPTCHA verification response', Mockery::type('array'))
            ->once();

        Log::shouldReceive('info')
            ->with('Login form validated', Mockery::type('array'))
            ->once();

        Log::shouldReceive('info')
            ->with('User authenticated successfully', Mockery::type('array'))
            ->once();

        Log::shouldReceive('info')
            ->with('Session regenerated after login', Mockery::type('array'))
            ->once();

        Log::shouldReceive('info')
            ->with('Redirecting user after successful login', Mockery::type('array'))
            ->once();

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'valid-token')
            ->call('login');
    }

    #[Test]
    public function recaptcha_http_request_is_made_with_correct_parameters()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.8,
            ], 200),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'test-token')
            ->call('login');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.google.com/recaptcha/api/siteverify' &&
                $request['secret'] === 'test-secret-key' &&
                $request['response'] === 'test-token' &&
                isset($request['remoteip']);
        });
    }

    #[Test]
    public function user_is_redirected_to_dashboard_after_successful_login()
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.5,
            ], 200),
        ]);

        Livewire::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('recaptcha_token', 'valid-token')
            ->call('login')
            ->assertRedirect('/dashboard');
    }

    #[Test]
    public function forgot_password_link_is_visible()
    {
        $response = $this->get('/login');

        $response->assertSee('Forgot your password?');
        $response->assertSee(route('password.request'));
    }
}
