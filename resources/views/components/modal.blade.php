@props([
    'title' => '',
    'size'  => '',
])

@php $sizeClass = $size ? "modal-{$size}" : ''; @endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $title }}') open = true"
    x-on:close-modal.window="open = false"
    x-on:keydown.escape.window="open = false"
>
    @isset($trigger)
    <span x-on:click="open = true">{{ $trigger }}</span>
    @endisset

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="modal-backdrop"
        x-on:click.self="open = false"
    >
        <div class="modal {{ $sizeClass }}">
            <div class="modal-header">
                <span class="modal-title">{{ $title }}</span>
                <button class="modal-close" x-on:click="open = false" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:16px;height:16px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
            <div class="modal-footer">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>
