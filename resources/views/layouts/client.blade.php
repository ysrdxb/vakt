<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ isset($title) ? $title . ' — Vakt Client Portal' : 'Vakt Client Portal' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .client-layout { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .client-topbar {
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            position: sticky; top: 0; z-index: 50;
        }
        .client-topbar-inner {
            max-width: 1200px; margin: 0 auto; padding: 0 24px;
            height: 60px; display: flex; align-items: center; justify-content: space-between;
        }
    </style>
</head>
<body>

<header class="client-topbar">
    <div class="client-topbar-inner">
        <div style="display:flex;align-items:center;gap:12px">
            <div class="logo-icon" style="width:32px;height:32px;background:linear-gradient(135deg,var(--color-primary),#0095b3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px">🛡️</div>
            <div>
                <div style="font-family:var(--font-display);font-weight:700;font-size:1rem">Vakt</div>
                <div style="font-size:0.65rem;color:var(--color-muted);font-family:var(--font-mono);text-transform:uppercase;letter-spacing:.08em">Client Portal</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:0.85rem;color:var(--color-muted)">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button class="btn btn-ghost btn-sm" type="submit">Logout</button>
            </form>
        </div>
    </div>
</header>

<div class="client-layout" style="padding-top:32px;padding-bottom:48px">
    {{ $slot }}
</div>

@php
    $baseUrl = rtrim(request()->getBaseUrl(), '/');
    $livewireScriptUrl = $baseUrl . '/livewire/livewire.js';
    $livewireUpdateUrl = $baseUrl . '/livewire/update';
@endphp

@livewireScriptConfig(['url' => $baseUrl ?: '/'])
<script src="{{ $livewireScriptUrl }}" data-csrf="{{ csrf_token() }}" data-update-uri="{{ $livewireUpdateUrl }}" data-navigate-once="true"></script>
</body>
</html>
