@component('mail::message')
# Hi {{ $name }} 👋

Welcome to our application! 🚀  
We’re excited to have you on board.

@component('mail::button', ['url' => url('/dashboard')])
Visit Website
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
