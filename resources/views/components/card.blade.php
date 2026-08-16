@props([
    'title' => null,
    'icon'  => null,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || $icon)
    <div class="card-header">
        <div class="card-title">
            @if($icon)
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                @switch($icon)
                    @case('shield')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    @break
                    @case('list')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    @break
                    @case('chart')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    @break
                    @default
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                @endswitch
            </svg>
            @endif
            {{ $title }}
        </div>
        @isset($actions)
        <div>{{ $actions }}</div>
        @endisset
    </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
    @isset($footer)
    <div class="card-footer">{{ $footer }}</div>
    @endisset
</div>
