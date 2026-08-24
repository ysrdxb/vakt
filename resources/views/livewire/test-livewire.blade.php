<div style="font-family: system-ui, sans-serif; padding: 30px; max-width: 800px; margin: 0 auto; background: #1e293b; color: #f8fafc; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
    <h2 style="color: #38bdf8; margin-top: 0;">🧪 Livewire Diagnostic & Interactive Test</h2>

    <div style="background: #0f172a; padding: 20px; border-radius: 8px; border: 1px solid #334155; margin-bottom: 20px;">
        <h3 style="margin-top: 0; color: #4ade80;">1. Interactive Counter Component</h3>
        <p style="font-size: 1.2rem;">Counter Value: <strong style="color: #facc15; font-size: 1.5rem;">{{ $count }}</strong></p>
        <button wire:click="increment" style="background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem;">
            ➕ Click to Increment (Livewire wire:click)
        </button>
    </div>

    <div style="background: #0f172a; padding: 20px; border-radius: 8px; border: 1px solid #334155;">
        <h3 style="margin-top: 0; color: #fbbf24;">2. Environment & Script Diagnostics</h3>
        <ul id="diag-list" style="line-height: 1.8; font-family: monospace; font-size: 0.9rem;">
            <li>Detecting environment parameters...</li>
        </ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const list = document.getElementById('diag-list');
            list.innerHTML = '';

            function addDiag(title, status, detail, isOk) {
                const li = document.createElement('li');
                li.style.color = isOk ? '#4ade80' : '#f87171';
                li.innerHTML = `<strong>[${status}]</strong> ${title}: ${detail}`;
                list.appendChild(li);
                console.log(`[Livewire-Test] ${title}: ${status} ->`, detail);
            }

            // Check Livewire global
            if (typeof window.Livewire !== 'undefined') {
                addDiag('window.Livewire', 'OK', 'Object loaded successfully', true);
            } else {
                addDiag('window.Livewire', 'MISSING', 'window.Livewire is undefined!', false);
            }

            // Check Alpine global
            if (typeof window.Alpine !== 'undefined') {
                addDiag('window.Alpine', 'OK', 'Alpine.js loaded', true);
            } else {
                addDiag('window.Alpine', 'MISSING', 'window.Alpine is undefined', false);
            }

            // Check Livewire config
            if (typeof window.livewireScriptConfig !== 'undefined') {
                addDiag('window.livewireScriptConfig', 'OK', JSON.stringify(window.livewireScriptConfig), true);
            } else {
                addDiag('window.livewireScriptConfig', 'NOT SET', 'Config object not found', false);
            }

            // Inspect script tags
            const scripts = Array.from(document.querySelectorAll('script')).map(s => s.src).filter(src => src.length > 0);
            addDiag('Loaded Script Tags', 'INFO', scripts.join(' | ') || 'No external scripts found', true);

            // Document location
            addDiag('Window Location', 'INFO', window.location.href, true);
        });
    </script>
</div>
