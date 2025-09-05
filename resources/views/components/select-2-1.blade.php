@props([
    'id' => null,
    'name' => null,
    'options' => [],
    'placeholder' => 'Select an option',
    'multiple' => false,
    'disabled' => false,
    'class' => '',
    'wireModel' => null,
])

@php
    $selectId = $id ?? 'select2-' . uniqid();
    $modelKey = $wireModel ?? $name;
@endphp

<div wire:ignore
     x-data
     x-init="
        let el = $refs.select;

        function initSelect2() {
            if ($(el).hasClass('select2-hidden-accessible')) {
                $(el).off('.select2').select2('destroy');
            }

            $(el).select2({
                placeholder: @js($placeholder),
                allowClear: true,
                width: '100%',
            });

            // UI → Livewire
            $(el).on('change.select2', () => {
                let val = $(el).val();
                if(@js($multiple)) {
                    $wire.set(@js($modelKey), val ?? []);
                } else {
                    $wire.set(@js($modelKey), val);
                }
            });
        }

        initSelect2();

        // Livewire → UI
        $watch('$wire.{{ $modelKey }}', value => {
            $(el).val(value ?? (@js($multiple) ? [] : '')).trigger('change.select2');
        });

        // DOM re-render hook
        Livewire.hook('message.processed', (el, component) => {
            if(el.querySelector && el.querySelector('#{{ $selectId }}')) {
                initSelect2();
                $(el).find(el).val($wire.get(@js($modelKey)) ?? (@js($multiple) ? [] : '')).trigger('change.select2');
            }
        });
     "
>
    <select
        x-ref="select"
        id="{{ $selectId }}"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        {{ $multiple ? 'multiple' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if($wireModel) wire:model.defer="{{ $wireModel }}" @endif
        {{ $attributes->merge(['class' => "form-select select2 $class"]) }}
    >
        @if ($placeholder && !$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $key => $option)
            @php
                $value = is_array($option) ? ($option['id'] ?? $option['value'] ?? $key) : $key;
                $label = is_array($option) ? ($option['text'] ?? $option['label'] ?? $option['name'] ?? $value) : $option;
                $currentValue = $wireModel ? $this->{$wireModel} : ($attributes->get('value') ?? null);
                $isSelected = $multiple
                    ? in_array($value, (array) $currentValue ?? [])
                    : $value == $currentValue;
            @endphp
            <option value="{{ $value }}" @selected($isSelected)>{{ $label }}</option>
        @endforeach
    </select>
</div>