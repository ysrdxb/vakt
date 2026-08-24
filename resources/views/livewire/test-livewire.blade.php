<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Livewire Full Diagnostic</title>

    <script>
        // Intercept fetch requests BEFORE Livewire loads
        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            const url = args[0];
            const logDiv = document.getElementById('net-log');
            if (logDiv) {
                logDiv.innerHTML += `<div style="color:#cbd5e1">⏳ Fetch request to: <code>${url}</code></div>`;
            }
            console.log('[Livewire-Fetch-Request]', url);

            try {
                const response = await originalFetch.apply(this, args);
                if (logDiv) {
                    const statusColor = response.ok ? '#4ade80' : '#f87171';
                    logDiv.innerHTML += `<div style="color:${statusColor}"><strong>[HTTP ${response.status} ${response.statusText}]</strong> URL: <code>${url}</code></div>`;
                }
                console.log('[Livewire-Fetch-Response]', response.status, url);
                return response;
            } catch (err) {
                if (logDiv) {
                    logDiv.innerHTML += `<div style="color:#f87171"><strong>[FETCH ERROR]</strong> ${err.message}</div>`;
                }
                console.error('[Livewire-Fetch-Error]', err);
                throw err;
            }
        };
    </script>

    @livewireStyles
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; padding: 20px; }
        .card { background: #1e293b; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #334155; }
        .btn { background: #2563eb; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #1d4ed8; }
        .btn-green { background: #059669; }
        .btn-green:hover { background: #047857; }
    </style>
</head>
<body>

<div style="max-width: 900px; margin: 0 auto;">
    <h2 style="color: #38bdf8;">🧪 Livewire & Alpine Diagnostic Suite</h2>

    {{-- Test 1: Livewire Counter --}}
    <div class="card">
        <h3 style="color: #4ade80; margin-top:0;">1. Livewire Component (wire:click)</h3>
        <p style="font-size: 1.3rem;">Livewire Count: <strong style="color: #facc15; font-size: 1.8rem;">{{ $count }}</strong></p>
        <button type="button" wire:click="increment" class="btn">
            ➕ Click (Livewire wire:click)
        </button>
    </div>

    {{-- Test 2: Pure Alpine Counter --}}
    <div class="card" x-data="{ alpineCount: 0 }">
        <h3 style="color: #60a5fa; margin-top:0;">2. Pure Alpine.js Component (x-on:click)</h3>
        <p style="font-size: 1.3rem;">Alpine Count: <strong style="color: #facc15; font-size: 1.8rem;" x-text="alpineCount">0</strong></p>
        <button type="button" x-on:click="alpineCount++" class="btn btn-green">
            ⚡ Click (Pure Alpine x-on:click)
        </button>
    </div>

    {{-- Test 3: HTTP Network Response Monitor --}}
    <div class="card">
        <h3 style="color: #f472b6; margin-top:0;">3. Livewire HTTP Network Monitor</h3>
        <div id="net-log" style="font-family: monospace; font-size: 0.9rem; line-height: 1.6;">
            <em>Waiting for button clicks...</em>
        </div>
    </div>
</div>

@livewireScripts

</body>
</html>
