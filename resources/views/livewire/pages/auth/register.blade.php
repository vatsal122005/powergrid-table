<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Support\Facades\Log;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        Log::info('Registration attempt', [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Log::info('User registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Dispatch the welcome email job to the queue
        SendWelcomeEmail::dispatch($user);

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div dusk="register-page">
    <form wire:submit="register" dusk="register-form">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required
                autofocus autocomplete="name" dusk="register-name-input" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" dusk="register-name-error" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required
                autocomplete="username" dusk="register-email-input" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" dusk="register-email-error" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password"
                required autocomplete="new-password" dusk="register-password-input" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" dusk="register-password-error" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                type="password" name="password_confirmation" required autocomplete="new-password"
                dusk="register-password-confirmation-input" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2"
                dusk="register-password-confirmation-error" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}" wire:navigate dusk="register-already-registered-link">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4" dusk="register-submit-button">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>