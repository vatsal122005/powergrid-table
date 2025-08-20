<div style="font-family: Arial, sans-serif; color: #333;">
    <h2 style="color: #c0392b;">🚨 Email Sending Failure Alert</h2>
    <p>
        An error occurred while attempting to send a product notification email.
    </p>
    @isset($product)
        <h4>Product Details:</h4>
        <ul>
            <li><strong>ID:</strong> {{ $product->id ?? 'N/A' }}</li>
            <li><strong>Name:</strong> {{ $product->name ?? 'N/A' }}</li>
            <li><strong>SKU:</strong> {{ $product->sku ?? 'N/A' }}</li>
        </ul>
    @endisset

    @isset($user)
        <h4>Initiated By:</h4>
        <ul>
            <li><strong>User ID:</strong> {{ $user->id ?? 'N/A' }}</li>
            <li><strong>Name:</strong> {{ $user->name ?? 'N/A' }}</li>
            <li><strong>Email:</strong> {{ $user->email ?? 'N/A' }}</li>
        </ul>
    @endisset

    @isset($exception)
        <h4>Error Details:</h4>
        <ul>
            <li><strong>Message:</strong> {{ $exception->getMessage() }}</li>
            <li><strong>Code:</strong> {{ $exception->getCode() }}</li>
        </ul>
        <details>
            <summary style="cursor:pointer;">View Stack Trace</summary>
            <pre style="background:#f4f4f4; color:#555; padding:10px; border-radius:4px; font-size:12px;">{{ $exception->getTraceAsString() }}</pre>
        </details>
    @endisset

    <p>
        Please investigate the issue as soon as possible.
    </p>
</div>
