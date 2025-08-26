<?php

namespace App\Console\Commands;

use App\Jobs\SendDailySummaryEmail;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily summary email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::with('product')->get();

        $this->info('Preparing to send daily summary emails to ' . $users->count() . ' users.');

        foreach ($users as $user) {
            try {
                Log::info("About to dispatch email for {$user->email}");
                SendDailySummaryEmail::dispatch($user);
                Log::info("Dispatched Email For {$user->email}");
                $this->info("Dispatched Email For {$user->email}");
            } catch (Exception $e) {
                Log::error("Failed to dispatch email for {$user->email}. Error: {$e->getMessage()}");
                $this->error("Failed to dispatch email for {$user->email}. Error: {$e->getMessage()}");
            }
        }

        $this->info('Daily Summary Emails Dispatched for ' . $users->count() . ' Users.');
    }
}
