@props([
    'label'   => '',
    'options' => [],
])

<div class="form-group">
    @if($label)
    <label class="form-label">{{ $label }}</label>
    @endif
    <select {{ $attributes->merge(['class' => 'form-control']) }}>
        {{ $slot }}
        @foreach($options as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
    @if($errors->has($attributes->get('name') ?? $attributes->get('wire:model') ?? ''))
    <div class="form-error">{{ $errors->first($attributes->get('name') ?? $attributes->get('wire:model') ?? '') }}</div>
    @endif
</div>
