<div>
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
</div>
@props([
    'color' => 'info', // info, success, warning, danger
    'dismissible' => false,
])

@php
    $colors = [
        'info' => 'bg-blue-100 border-blue-400 text-blue-800',
        'success' => 'bg-green-100 border-green-400 text-green-800',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-800',
        'danger' => 'bg-red-100 border-red-400 text-red-800',
    ];
    $iconSvgs = [
        'info' => '<svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>',
        'success' => '<svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
        'warning' => '<svg class="w-5 h-5 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>',
        'danger' => '<svg class="w-5 h-5 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414-1.414L12 9.172 7.05 4.222 5.636 5.636 10.586 10.586 5.636 15.536l1.414 1.414L12 12.828l4.95 4.95 1.414-1.414-4.95-4.95z"/></svg>',
    ];
    $alertClass = $colors[$color] ?? $colors['info'];
@endphp

<div {{ $attributes->merge(['class' => "flex items-start p-4 border-l-4 rounded-md shadow-sm $alertClass"]) }}>
    {!! $iconSvgs[$color] ?? $iconSvgs['info'] !!}
    <div class="flex-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="ml-4 text-lg font-bold focus:outline-none text-gray-500 hover:text-gray-700" onclick="this.closest('div').style.display='none'">&times;</button>
    @endif
</div>
