@props([
    'label' => '',
    'value' => '0',
    'trend' => null,
    'color' => 'primary',
    'icon'  => null,
])

<div class="stat-card {{ $color }}">
    <div class="stat-glow"></div>
    @if($icon)
    <div style="color: var(--color-{{ $color }}); opacity: 0.6; margin-bottom: 8px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:22px;height:22px">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    @endif
    <div class="stat-label">{{ $label }}</div>
    <div class="stat-value">{{ $value }}</div>
    @if($trend)
    <div class="stat-trend {{ str_starts_with($trend, '+') ? 'up' : 'down' }}">
        {{ $trend }}
    </div>
    @endif
</div>
