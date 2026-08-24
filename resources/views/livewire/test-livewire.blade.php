<div>
    <h2 style="color: #38bdf8; margin-top:0;">🧪 Livewire 3 Diagnostic Suite</h2>

    {{-- Test 1: Livewire Counter --}}
    <div style="background: #1e293b; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #334155;">
        <h3 style="color: #4ade80; margin-top:0;">1. Livewire Component (wire:click)</h3>
        <p style="font-size: 1.3rem;">Livewire Count: <strong style="color: #facc15; font-size: 1.8rem;">{{ $count }}</strong></p>
        <button type="button" wire:click="increment" style="background: #2563eb; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem;">
            ➕ Click to Increment (Livewire wire:click)
        </button>
    </div>

    {{-- Test 2: Pure Alpine Counter --}}
    <div style="background: #1e293b; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #334155;" x-data="{ alpineCount: 0 }">
        <h3 style="color: #60a5fa; margin-top:0;">2. Pure Alpine.js Component (x-on:click)</h3>
        <p style="font-size: 1.3rem;">Alpine Count: <strong style="color: #facc15; font-size: 1.8rem;" x-text="alpineCount">0</strong></p>
        <button type="button" x-on:click="alpineCount++" style="background: #059669; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem;">
            ⚡ Click (Pure Alpine x-on:click)
        </button>
    </div>

    {{-- Test 3: System Info --}}
    <div style="background: #1e293b; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #334155;">
        <h3 style="color: #fbbf24; margin-top:0;">3. Endpoint Diagnostics</h3>
        <ul style="font-family: monospace; font-size: 0.85rem; line-height: 1.8; color: #94a3b8;">
            <li>CSRF Token Present: <strong style="color:#f8fafc">{{ csrf_token() ? 'YES' : 'NO' }}</strong></li>
            <li>App URL: <strong style="color:#f8fafc">{{ config('app.url') }}</strong></li>
            <li>Request Base URL: <strong style="color:#f8fafc">{{ request()->getBaseUrl() }}</strong></li>
            <li>Livewire Update Endpoint: <strong style="color:#4ade80">{{ url(app('livewire')->getUpdateUri()) }}</strong></li>
        </ul>
    </div>
</div>
