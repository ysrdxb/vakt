@props(['label' => ''])

<label class="toggle-wrap">
    <span class="toggle">
        <input type="checkbox" {{ $attributes }} />
        <span class="toggle-slider"></span>
    </span>
    @if($label)
    <span class="toggle-label">{{ $label }}</span>
    @endif
</label>
