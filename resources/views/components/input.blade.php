@props([
    'label'       => '',
    'type'        => 'text',
    'placeholder' => '',
])

<div class="form-group">
    @if($label)
    <label class="form-label">{{ $label }}</label>
    @endif
    <input
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
    />
    @if($errors->has($attributes->get('name') ?? $attributes->get('wire:model') ?? ''))
    <div class="form-error">{{ $errors->first($attributes->get('name') ?? $attributes->get('wire:model') ?? '') }}</div>
    @endif
</div>
