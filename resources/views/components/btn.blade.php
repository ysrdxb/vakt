@props([
    'variant' => 'primary',
    'size'    => '',
    'type'    => 'button',
    'href'    => null,
])

@php
    $classes = "btn btn-{$variant}";
    if ($size) $classes .= " btn-{$size}";
    $tag = $href ? 'a' : 'button';
    
    // Find the wire:click action if it exists
    $wireClick = null;
    foreach ($attributes->getAttributes() as $key => $value) {
        if (str_starts_with($key, 'wire:click')) {
            $wireClick = $value;
            // Strip parenthesis if present e.g. generateReport() -> generateReport
            if (strpos($wireClick, '(') !== false) {
                $wireClick = substr($wireClick, 0, strpos($wireClick, '('));
            }
            break;
        }
    }
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
    @if($wireClick)
        wire:loading.attr="disabled"
        wire:target="{{ $wireClick }}"
    @endif
>
    @if($wireClick)
        <span wire:loading.flex wire:target="{{ $wireClick }}" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);align-items:center;justify-content:center;">
            <span class="spinner"></span>
        </span>
        <span wire:loading.class="opacity-0" wire:target="{{ $wireClick }}" style="display:inline-flex;align-items:center;gap:6px">
            {{ $slot }}
        </span>
    @else
        {{ $slot }}
    @endif
</{{ $tag }}>
