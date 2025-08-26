<?php

namespace App\Jobs;

use App\Mail\WelcomeMail;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting welcome email job', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'attempt' => $this->attempts(),
            ]);

            // Check if user still exists and email is valid
            if (! $this->user->exists || ! filter_var($this->user->email, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Welcome email job skipped - invalid user or email', [
                    'user_id' => $this->user->id ?? 'null',
                    'email' => $this->user->email ?? 'null',
                ]);

                return;
            }

            // Send the welcome email
            Mail::to($this->user->email)
                ->send(new WelcomeMail($this->user));

            Log::info('Welcome email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        } catch (Exception $e) {
            Log::error('Welcome email job failed', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw the exception to trigger retry logic
            throw $e;
        }
    }

    public function failed(?Exception $exception): void
    {
        Log::error('Welcome email job permanently failed', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception?->getMessage(),
            'attempts' => $this->attempts(),
        ]);
        // Optionally, you could notify administrators about the failure
    }
}
