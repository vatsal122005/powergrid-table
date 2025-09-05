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
<div wire:ignore.self>
    <select
        id="{{ $selectId }}"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        {{ $multiple ? 'multiple' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        wire:model="{{ $modelKey }}"
        class="form-select select2 {{ $class }}"
    >
        @if ($placeholder && !$multiple)    
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $key => $option)
            @php
                $value = is_array($option) ? ($option['id'] ?? $option['value'] ?? $key) : $key;
                $label = is_array($option) ? ($option['text'] ?? $option['label'] ?? $option['name'] ?? $value) : $option;
            @endphp
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        window.initSelect2 = function(selectId, placeholder, modelKey) {
            let $el = $(selectId);
            
            function init() {
                // Destroy existing Select2 if it exists
                if ($el.data('select2')) {
                    $el.select2('destroy');
                }
                
                // Initialize Select2
                $el.select2({
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%'
                });
                
                // Sync to Livewire when changed
                $el.off('change.select2-livewire').on('change.select2-livewire', function () {
                    let value = $(this).val();
                    @this.set(modelKey, value);
                });
            }
            
            // Initial initialization
            init();
            
            // Re-initialize after Livewire updates
            Livewire.hook('morph.updated', ({ el, component }) => {
                if (el.contains($el[0]) || el === $el[0]) {
                    setTimeout(() => {
                        init();
                        // Restore value from Livewire
                        const currentValue = @this.get(modelKey);
                        if (currentValue !== null && currentValue !== undefined) {
                            $el.val(currentValue).trigger('change.select2');
                        }
                    }, 10);
                }
            });
        };
    });
</script>
@endpush
@endonce

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.initSelect2 === 'function') {
            window.initSelect2("#{{ $selectId }}", "{{ $placeholder }}", "{{ $modelKey }}");
        }
    });
</script>
@endpush