<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log; // 👈 add Log facade
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
        Log::info('Login attempt started', [
            'ip' => request()->ip(),
            'email' => $this->form->email ?? null,
            'token' => $this->recaptcha_token,
        ]);

        // Verify reCAPTCHA
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $this->recaptcha_token,
            'remoteip' => request()->ip(),
        ]);

        $recaptchaData = $response->json();
        Log::debug('reCAPTCHA verification response', [
            'response' => $recaptchaData,
        ]);

        // Check reCAPTCHA verification
        if (!($recaptchaData['success'] ?? false) || ($recaptchaData['score'] ?? 0) < 0.5) {
            Log::warning('reCAPTCHA verification failed', [
                'email' => $this->form->email ?? null,
                'ip' => request()->ip(),
                'success' => $recaptchaData['success'] ?? null,
                'score' => $recaptchaData['score'] ?? null,
                'errors' => $recaptchaData['error-codes'] ?? null,
            ]);

            $this->addError('form.email', 'reCAPTCHA verification failed. Please try again.');
            return;
        }

        // Validate form
        try {
            $this->validate();
            Log::info('Login form validated', [
                'email' => $this->form->email ?? null,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Login form validation failed', [
                'email' => $this->form->email ?? null,
                'errors' => $e->errors(),
            ]);
            throw $e;
        }

        // Authenticate user
        try {
            $this->form->authenticate();
            Log::info('User authenticated successfully', [
                'email' => $this->form->email ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('User authentication failed', [
                'email' => $this->form->email ?? null,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }

        // Regenerate session
        Session::regenerate();
        Log::info('Session regenerated after login', [
            'email' => $this->form->email ?? null,
        ]);

        // Redirect to intended page
        Log::info('Redirecting user after successful login', [
            'email' => $this->form->email ?? null,
            'redirect_to' => route('dashboard', absolute: false),
        ]);
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; 
?>


<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="login-form">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email"
                required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full" type="password"
                name="password" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <input type="hidden" id="recaptcha_token" wire:model="recaptcha_token" name="recaptcha_token">

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
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