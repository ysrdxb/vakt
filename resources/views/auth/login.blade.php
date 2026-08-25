<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Vakt SOC — Secure Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-color: #0b0c10;
            --surface-color: rgba(22, 24, 31, 0.65);
            --surface-border: rgba(255, 255, 255, 0.08);
            --primary: #00f2fe;
            --primary-glow: rgba(0, 242, 254, 0.4);
            --secondary: #4facfe;
            --text-main: #f8f9fa;
            --text-muted: #94a3b8;
            --danger: #ff4757;
            --success: #2ed573;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .bg-animated {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            animation: float 20s infinite alternate ease-in-out;
        }
        .blob-1 {
            top: -10%; left: -10%;
            width: 500px; height: 500px;
            background: rgba(0, 242, 254, 0.15);
            animation-delay: 0s;
        }
        .blob-2 {
            bottom: -20%; right: -10%;
            width: 600px; height: 600px;
            background: rgba(79, 172, 254, 0.15);
            animation-delay: -5s;
        }
        .blob-3 {
            top: 40%; left: 60%;
            width: 400px; height: 400px;
            background: rgba(106, 17, 203, 0.15);
            animation-delay: -10s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -50px) scale(1.1); }
        }

        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            background: var(--surface-color);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--surface-border);
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05) inset;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUp {
            to { transform: translateY(0); opacity: 1; }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            box-shadow: 0 10px 25px var(--primary-glow);
            position: relative;
            overflow: hidden;
        }
        
        .login-logo-icon::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: skewX(-20deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            20% { left: 200%; }
            100% { left: 200%; }
        }

        .login-logo h1 {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 4px;
            background: linear-gradient(to right, #fff, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-logo p {
            font-family: 'Space Mono', monospace;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--primary);
            opacity: 0.9;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 8px;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 16px;
            color: var(--text-main);
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 0 4px rgba(0, 242, 254, 0.1), inset 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-group:focus-within .form-label {
            color: var(--primary);
        }

        .pw-wrap {
            position: relative;
        }
        
        .pw-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            transition: color 0.2s;
        }
        
        .pw-toggle:hover {
            color: var(--primary);
        }

        .checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            margin-bottom: 32px;
        }

        .checkbox-wrap input {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.2);
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }

        .checkbox-wrap input:checked {
            background: var(--primary);
            border-color: var(--primary);
        }

        .checkbox-wrap input:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 2px;
            width: 4px;
            height: 8px;
            border: solid #000;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .checkbox-wrap span {
            font-size: 0.85rem;
            color: var(--text-muted);
            user-select: none;
            transition: color 0.2s;
        }
        
        .checkbox-wrap:hover span {
            color: var(--text-main);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #000;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 242, 254, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 242, 254, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(0,0,0,0.2);
            border-radius: 50%;
            border-top-color: #000;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .login-footer {
            margin-top: 32px;
            text-align: center;
            font-family: 'Space Mono', monospace;
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .login-footer::before, .login-footer::after {
            content: '';
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            flex: 1;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert.danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ff4757;
        }
        
        .alert.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #2ed573;
        }
    </style>
</head>
<body>

<div class="bg-animated">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

@php
    $vueProps = [
        'actionUrl' => route('login'),
        'csrfToken' => csrf_token(),
        'oldEmail' => old('email') ?? '',
        'errorMessage' => $errors->first('email') ?? '',
        'statusMessage' => session('status') ?? ''
    ];
@endphp

<script>
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        document.body.innerHTML += '<div style="position:absolute;top:0;left:0;z-index:9999;background:red;color:white;padding:20px;">' + msg + '<br>' + (error ? error.stack : '') + '</div>';
    };
    window.addEventListener("unhandledrejection", function(e) {
        document.body.innerHTML += '<div style="position:absolute;top:50px;left:0;z-index:9999;background:red;color:white;padding:20px;">Promise Rejection: ' + e.reason + '</div>';
    });

    window.__VUE_PROPS__ = window.__VUE_PROPS__ || {};
    window.__VUE_PROPS__['vue-login-form'] = @json($vueProps);
</script>

<div id="vue-login-form"></div>

</body>
</html>
