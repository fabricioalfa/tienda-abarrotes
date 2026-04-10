<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Manrope', sans-serif;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ── Panel izquierdo ── */
        .l-panel {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
            background: #0a0f1e;
            overflow: hidden;
        }

        /* Mesh gradient animado */
        .l-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 30%, rgba(37,99,235,0.45) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 70%, rgba(99,102,241,0.35) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 50% 100%, rgba(14,165,233,0.2) 0%, transparent 60%);
            animation: meshMove 12s ease-in-out infinite alternate;
        }

        @keyframes meshMove {
            0%   { transform: scale(1)   translateY(0); }
            100% { transform: scale(1.08) translateY(-20px); }
        }

        /* Grid de puntos */
        .l-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .l-content { position: relative; z-index: 2; }

        /* Logo marca */
        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 14px;
        }
        .brand-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: rgba(255,255,255,0.12);
            border: 1.5px solid rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(8px);
        }
        .brand-icon svg { width: 24px; height: 24px; color: #fff; }
        .brand-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.01em;
        }
        .brand-sub {
            font-size: 0.72rem;
            font-weight: 500;
            color: rgba(148,163,184,0.9);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        /* Hero text */
        .hero { margin-top: auto; margin-bottom: auto; padding: 60px 0; }
        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px 5px 8px;
            border-radius: 100px;
            background: rgba(59,130,246,0.2);
            border: 1px solid rgba(59,130,246,0.3);
            font-size: 0.72rem;
            font-weight: 600;
            color: #93c5fd;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .hero-label span {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #3b82f6;
            display: inline-block;
            box-shadow: 0 0 6px #3b82f6;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .hero-title {
            font-size: clamp(2.4rem, 4vw, 3.4rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            letter-spacing: -0.03em;
        }
        .hero-title em {
            font-style: normal;
            background: linear-gradient(90deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-desc {
            margin-top: 18px;
            font-size: 1rem;
            color: rgba(148,163,184,0.85);
            line-height: 1.65;
            max-width: 380px;
        }

        /* Features */
        .features {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 40px;
        }
        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(203,213,225,0.85);
            font-size: 0.88rem;
            font-weight: 500;
        }
        .feature-dot {
            width: 20px; height: 20px;
            border-radius: 50%;
            background: rgba(59,130,246,0.2);
            border: 1px solid rgba(59,130,246,0.4);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .feature-dot::after {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #60a5fa;
        }

        /* Footer izquierdo */
        .l-footer {
            font-size: 0.78rem;
            color: rgba(100,116,139,0.7);
        }

        /* ── Panel derecho ── */
        .r-panel {
            width: 480px;
            flex-shrink: 0;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 52px;
            position: relative;
            box-shadow: -24px 0 80px rgba(0,0,0,0.15);
        }

        .r-header { margin-bottom: 36px; }
        .r-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #3b82f6;
            margin-bottom: 8px;
        }
        .r-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .r-sub {
            margin-top: 6px;
            font-size: 0.9rem;
            color: #64748b;
        }

        /* Form */
        .field { margin-bottom: 20px; }
        .field label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            width: 17px; height: 17px;
            pointer-events: none;
        }
        .field input {
            width: 100%;
            padding: 11px 42px 11px 40px;
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            background: #f9fafb;
            font-size: 0.93rem;
            font-family: 'Manrope', sans-serif;
            color: #111827;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }
        .field input:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .field input.is-error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .field input::placeholder { color: #9ca3af; }

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            padding: 2px;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }
        .toggle-pw:hover { color: #4b5563; }
        .toggle-pw svg { width: 17px; height: 17px; }

        .field-error {
            margin-top: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #ef4444;
        }

        /* Remember */
        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 24px;
            margin-top: -4px;
        }
        .remember input[type="checkbox"] {
            width: 16px; height: 16px;
            border-radius: 5px;
            border: 1.5px solid #d1d5db;
            cursor: pointer;
            accent-color: #3b82f6;
        }
        .remember label {
            font-size: 0.84rem;
            color: #6b7280;
            cursor: pointer;
            user-select: none;
        }

        /* Status */
        .alert-success {
            margin-bottom: 20px;
            padding: 11px 16px;
            border-radius: 10px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            font-size: 0.85rem;
            font-weight: 500;
            color: #15803d;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-family: 'Manrope', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #fff;
            background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
            box-shadow: 0 4px 20px rgba(37,99,235,0.4);
            transition: transform 0.13s, box-shadow 0.15s, opacity 0.15s;
            position: relative;
            overflow: hidden;
        }
        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(37,99,235,0.5);
        }
        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(37,99,235,0.35);
        }

        .r-footer {
            margin-top: 36px;
            text-align: center;
            font-size: 0.75rem;
            color: #9ca3af;
        }

        /* Responsive */
        @media (max-width: 900px) {
            body { overflow: auto; }
            .l-panel { display: none; }
            .r-panel {
                width: 100%;
                padding: 40px 28px;
                box-shadow: none;
                min-height: 100vh;
                background: linear-gradient(160deg, #f8faff 0%, #fff 100%);
            }
        }
    </style>
</head>
<body>

    {{-- Panel izquierdo --}}
    <div class="l-panel">
        <div class="l-content">
            <div class="brand-mark">
                <div class="brand-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>
                </div>
                <div>
                    <div class="brand-name">Carnicería Colcapirhua</div>
                </div>
            </div>
        </div>

        <div class="l-content hero">
            <div class="hero-label">
                <span></span>
                Bienvenido
            </div>
            <h1 class="hero-title">
                La mejor carne,<br><em>al mejor precio</em>
            </h1>
            <p class="hero-desc">
                Productos frescos de la más alta calidad. Tu carnicería de confianza en Colcapirhua.
            </p>
            <div class="features">
                <div class="feature"><div class="feature-dot"></div>Cortes frescos todos los días</div>
                <div class="feature"><div class="feature-dot"></div>Atención rápida y personalizada</div>
                <div class="feature"><div class="feature-dot"></div>Los mejores precios del mercado</div>
            </div>
        </div>

        <div class="l-content l-footer">
            &copy; {{ date('Y') }} Carnicería Colcapirhua
        </div>
    </div>

    {{-- Panel derecho --}}
    <div class="r-panel">
        <div class="r-header">
            <div class="r-eyebrow">Acceso al sistema</div>
            <div class="r-title">Iniciar sesión</div>
            <div class="r-sub">Ingresa tus credenciales para continuar.</div>
        </div>

        @if (session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        {{ $slot }}

        <div class="r-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; Panel administrativo
        </div>
    </div>

    <script>
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) window.location.reload();
        });
    </script>
</body>
</html>
