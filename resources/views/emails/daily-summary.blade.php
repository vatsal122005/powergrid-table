<x-mail::message>
# Hello {{ $user->name }},

Here is your daily summary for {{ \Carbon\Carbon::now()->setTimezone('Asia/Kolkata')->format('F j, Y') }} at {{ \Carbon\Carbon::now()->setTimezone('Asia/Kolkata')->format('g:i A') }} (Indian Standard Time).

---

**Account Email:** {{ $user->email }}

**Total Products:** {{ $user->product->count() ?? 'N/A' }}

@if(isset($customMessage))
{{ $customMessage }}
@endif

<x-mail::button :url="route('dashboard')">
View Your Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
