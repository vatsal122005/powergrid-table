<?php

namespace App\Jobs;

use App\Mail\EmailFailureAlert;
use App\Mail\ProductCreatedMail;
use App\Models\Product;
use App\Models\User;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendProductAddedMail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Product $product;

    public User $user;

    public array $recipients;

    public array $ccRecipients;

    public int $tries = 3;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product, User $user, array $recipients, array $ccRecipients)
    {
        $this->product = $product;
        $this->user = $user;
        $this->recipients = $recipients ?: [config('mail.admin_email', 'admin@gmail.com')];
        $this->ccRecipients = $ccRecipients ?: [];

        Log::info('Scheduling Product Added Email', [
            'product_id' => $this->product->id,
            'user_id' => $this->user->id,
            'recipients' => $this->recipients,
            'cc_recipients' => $this->ccRecipients,
        ]);

        $this->delay(now()->addSeconds(5));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting To Send Product Created Email', [
                'job_id' => $this->job->getJobId(),
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'user_id' => $this->user->id,
                'recipients' => $this->recipients,
                'cc_recipients' => $this->ccRecipients,
                'attempts' => $this->attempts(),
            ]);

            $this->product->refresh();

            $this->product->load('category');

            $mail = Mail::to($this->recipients);

            if (! empty($this->ccRecipients)) {
                $mail->cc($this->ccRecipients);
            }

            try {
                $mail->send(new ProductCreatedMail($this->product, $this->user));
            } catch (Exception $e) {
                Bugsnag::notifyException($e, function ($report) {
                    $report->setMetaData([
                        'product' => [
                            'id' => $this->product->id,
                            'name' => $this->product->name,
                        ],
                    ]);
                });
            }

            Log::info('Product created email sent successfully', [
                'job_id' => $this->job->getJobId(),
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'user_id' => $this->user->id,
                'recipients_count' => count($this->recipients),
                'cc_recipients_count' => count($this->ccRecipients),
                'attempt' => $this->attempts(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send product created email', [
                'job_id' => $this->job->getJobId(),
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'user_id' => $this->user->id,
                'error_message' => $e->getMessage(),
                'error_codes' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'attempts' => $this->attempts(),
                'tries' => $this->tries,
            ]);
            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        Log::critical('Product Created Email Job Failed Permanently', [
            'job_id' => $this->job->getJobId(),
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'user_id' => $this->user->id,
            'recipients' => $this->recipients,
            'cc_recipients' => $this->ccRecipients,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
            'final_attempts' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);

        try {
            Mail::to(config('mail.admin_email', 'admin@gmail.com'))
                ->send(new EmailFailureAlert($this->product, $this->user, $exception));
        } catch (Exception $e) {
            Log::error('Failed To send Email Failure Alert', [
                'original_error' => $exception->getMessage(),
                'alert_error' => $e->getMessage(),
                'error_code' => $exception->getCode(),
            ]);
        }
    }

    public function tags(): array
    {
        return [
            'email',
            'Product-created',
            'product:'.$this->product->id,
            'user:'.$this->user->id,
        ];
    }
}
