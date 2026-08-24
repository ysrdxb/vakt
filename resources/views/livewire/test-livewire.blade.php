<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Livewire Full Diagnostic</title>
    @livewireStyles
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; padding: 20px; }
        .card { background: #1e293b; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #334155; }
        .btn { background: #2563eb; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #1d4ed8; }
        pre { background: #090d16; padding: 12px; border-radius: 6px; overflow-x: auto; color: #38bdf8; font-size: 0.85rem; }
    </style>
</head>
<body>

<div style="max-width: 900px; margin: 0 auto;">
    <h2 style="color: #38bdf8;">🧪 Livewire 3 Complete Diagnostics & Endpoint Test</h2>

    {{-- Interactive Counter --}}
    <div class="card">
        <h3 style="color: #4ade80; margin-top:0;">1. Component State & Interaction</h3>
        <p style="font-size: 1.3rem;">Counter Value: <strong style="color: #facc15; font-size: 1.8rem;" id="counter-val">{{ $count }}</strong></p>
        <button wire:click="increment" class="btn" id="test-btn">
            ➕ Click to Increment (Livewire wire:click)
        </button>
    </div>

    {{-- HTTP Network Response Monitor --}}
    <div class="card">
        <h3 style="color: #f472b6; margin-top:0;">2. Livewire HTTP Network Monitor (Captures 200, 419, 404, 500)</h3>
        <div id="net-log" style="font-family: monospace; font-size: 0.9rem; line-height: 1.6;">
            <em>Waiting for button click / network requests...</em>
        </div>
    </div>

    {{-- Environment Info --}}
    <div class="card">
        <h3 style="color: #fbbf24; margin-top:0;">3. System & Endpoint Info</h3>
        <ul id="info-list" style="font-family: monospace; font-size: 0.85rem; line-height: 1.8;">
            <li>CSRF Token Present: <strong>{{ csrf_token() ? 'YES' : 'NO' }}</strong></li>
            <li>App URL: <strong>{{ config('app.url') }}</strong></li>
            <li>Request Base URL: <strong>{{ request()->getBaseUrl() }}</strong></li>
            <li>Update Endpoint (Computed): <strong>{{ url(app('livewire')->getUpdateUri()) }}</strong></li>
        </ul>
    </div>
</div>

@livewireScripts

<script>
    // Intercept fetch requests to record status code on screen
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        const url = args[0];
        const logDiv = document.getElementById('net-log');
        logDiv.innerHTML += `<div style="color:#cbd5e1">⏳ Sending POST request to: <code>${url}</code>...</div>`;

        try {
            const response = await originalFetch.apply(this, args);
            const statusColor = response.ok ? '#4ade80' : '#f87171';
            logDiv.innerHTML += `<div style="color:${statusColor}"><strong>[HTTP ${response.status} ${response.statusText}]</strong> URL: <code>${url}</code></div>`;
            return response;
        } catch (err) {
            logDiv.innerHTML += `<div style="color:#f87171"><strong>[FETCH ERROR]</strong> ${err.message}</div>`;
            throw err;
        }
    };
</script>

</body>
</html>
