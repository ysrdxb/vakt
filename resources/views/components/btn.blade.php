@props([
    'variant' => 'primary',
    'size'    => '',
    'loading' => false,
    'type'    => 'button',
    'href'    => null,
])

@php
    $classes = "btn btn-{$variant}";
    if ($size) $classes .= " btn-{$size}";
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
    @if($loading || $attributes->get('wire:loading.attr') === 'disabled') wire:loading.attr="disabled" @endif
>
    @if($loading)
        <span class="spinner" wire:loading.remove></span>
        <span wire:loading><span class="spinner"></span></span>
    @endif
    <span @if($loading) wire:loading.remove @endif>{{ $slot }}</span>
</{{ $tag }}>
