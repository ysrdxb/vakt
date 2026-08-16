@props(['label' => ''])

<label class="checkbox-wrap">
    <input type="checkbox" {{ $attributes }} />
    @if($label)
    <span style="font-size: 0.875rem; color: var(--color-text);">{{ $label }}</span>
    @endif
</label>
