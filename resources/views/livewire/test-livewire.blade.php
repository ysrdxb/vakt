<div>
    <h2 style="color: #38bdf8; margin-top:0;">🧪 Vue 3 & Standard Controller Diagnostic Suite</h2>

    {{-- Vue 3 Component --}}
    <div id="app">
        <vue-counter></vue-counter>
    </div>

    {{-- Endpoint Diagnostics --}}
    <div style="background: #1e293b; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #334155;">
        <h3 style="color: #fbbf24; margin-top:0;">Diagnostics</h3>
        <ul style="font-family: monospace; font-size: 0.85rem; line-height: 1.8; color: #94a3b8;">
            <li>CSRF Token: <strong style="color:#f8fafc">{{ csrf_token() ? 'VALID' : 'MISSING' }}</strong></li>
            <li>App URL: <strong style="color:#f8fafc">{{ config('app.url') }}</strong></li>
            <li>Vue 3 Setup: <strong style="color:#4ade80">INSTALLED & ACTIVE</strong></li>
        </ul>
    </div>
</div>
