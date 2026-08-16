@props([
    'label' => '',
    'rows'  => 4,
])

<div class="form-group">
    @if($label)
    <label class="form-label">{{ $label }}</label>
    @endif
    <textarea
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
    >{{ $slot }}</textarea>
    @if($errors->has($attributes->get('name') ?? $attributes->get('wire:model') ?? ''))
    <div class="form-error">{{ $errors->first($attributes->get('name') ?? $attributes->get('wire:model') ?? '') }}</div>
    @endif
</div>
