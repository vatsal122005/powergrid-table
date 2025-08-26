<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProductCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public Product $product;

    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Product $product, User $user)
    {
        Log::info('Creating new ProductCreatedMail instance');
        Log::info('Product ID: ' . $product->id);
        Log::info('User ID: ' . $user->id);

        $this->product = $product;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        Log::debug('Creating envelope for ProductCreatedMail');
        Log::debug('Product ID: ' . $this->product->id);
        Log::debug('User ID: ' . $this->user->id);

        return new Envelope(
            subject: 'Product Created Mail',
            tags: ['product-created'],
            metadata: [
                'product_id' => $this->product->id,
                'user_id' => $this->user->id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::debug('Creating content for ProductCreatedMail');
        Log::debug('Product ID: ' . $this->product->id);
        Log::debug('User ID: ' . $this->user->id);

        return new Content(
            markdown: 'emails.product-created-mail',
            with: [
                'product' => $this->product,
                'user' => $this->user,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
