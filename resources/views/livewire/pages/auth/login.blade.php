<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;
    public string $recaptcha_token = '';

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $email = $this->form->email ?? null;
        $ip = request()->ip();

        Log::info('Login attempt started', compact('email', 'ip'));

        // ✅ Check if reCAPTCHA should be bypassed (for Dusk testing only)
        $bypassRecaptcha = app()->environment('dusk') && config('services.recaptcha.bypass_for_dusk', false);

        if ($bypassRecaptcha) {
            Log::info('Bypassing reCAPTCHA for Dusk/testing only', compact('email', 'ip'));
            $this->recaptcha_token = 'dummy-token';
        } else {
            // ✅ Verify reCAPTCHA with Google API
            try {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => config('services.recaptcha.secret_key'),
                    'response' => $this->recaptcha_token,
                    'remoteip' => $ip,
                ]);

                $recaptchaData = $response->json();

                Log::debug('reCAPTCHA response', $recaptchaData);

                if (!($recaptchaData['success'] ?? false) || ($recaptchaData['score'] ?? 0) < 0.1) {
                    $this->addError('recaptcha', 'reCAPTCHA verification failed. Please try again.');
                    Log::warning('reCAPTCHA failed', compact('email', 'ip', 'recaptchaData'));
                    return;
                }
            } catch (\Exception $e) {
                Log::error('reCAPTCHA verification error', [
                    'email' => $email,
                    'ip' => $ip,
                    'exception' => $e->getMessage(),
                ]);
                $this->addError('recaptcha', 'reCAPTCHA verification failed due to a server error.');
                return;
            }
        }

        // ✅ Validate form input
        try {
            $this->validate();
            Log::info('Login form validated', compact('email'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Login form validation failed', [
                'email' => $email,
                'errors' => $e->errors(),
            ]);
            throw $e; // Let Livewire handle showing validation errors
        }

        // ✅ Authenticate user
        try {
            $this->form->authenticate();
            Log::info('User authenticated successfully', compact('email'));
        } catch (\Exception $e) {
            Log::warning('User authentication failed', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
            $this->addError('form.email', 'Invalid credentials.');
            return;
        }

        // ✅ Regenerate session for security
        Session::regenerate();
        Log::info('Session regenerated after login', compact('email'));

        // ✅ Redirect to intended page or dashboard
        $redirectUrl = route('dashboard', absolute: false);
        Log::info('Redirecting user after successful login', compact('email', 'redirectUrl'));

        $this->redirectIntended($redirectUrl, navigate: true);
    }
};  
?>


<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="login-form" data-testid="login-form" dusk="login-form">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email"
                autofocus autocomplete="username" data-testid="email-input" dusk="email-input" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" data-testid="email-error"
                dusk="email-error" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full" type="password"
                name="password" autocomplete="current-password" data-testid="password-input" dusk="password-input" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" data-testid="password-error"
                dusk="password-error" />
        </div>

        <!-- Hidden Recaptcha -->
        <input type="hidden" id="recaptcha_token" wire:model="recaptcha_token" name="recaptcha_token"
            data-testid="recaptcha-input" dusk="recaptcha-input">

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember"
                    data-testid="remember-checkbox" dusk="remember-checkbox">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('password.request') }}" wire:navigate data-testid="forgot-password-link"
                    dusk="forgot-password-link">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3" id="login" data-testid="login-button" dusk="login-button">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div>

<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
    document.getElementById('login-form').addEventListener('submit', function (e) {
        e.preventDefault();
        grecaptcha.ready(function () {
            grecaptcha.execute("{{ config('services.recaptcha.site_key') }}", { action: 'login' })
                .then(function (token) {
                    @this.set('recaptcha_token', token).then(() => {
                        @this.call('login');
                    });
                });
        });
    });
</script>