@php
    $productsJson = $products->map(function ($p) {
        return [
            'id'                => $p->id,
            'name'              => $p->name,
            'barcode'           => $p->barcode,
            'price'             => (float) $p->price,
            'package_price'     => (float) ($p->package_price ?? 0),
            'package_name'      => $p->package_name ?? '',
            'package_enabled'   => $p->supportsPackageSale() ? 1 : 0,
            'units_per_package' => (int) ($p->units_per_package ?? 1),
            'sale_type'         => $p->sale_type,
            'weight_unit'       => $p->weight_unit ?? 'kg',
            'stock'             => (float) $p->stock,
            'stock_label'       => $p->stockBreakdownLabel(),
            'category'          => $p->category?->name ?? 'General',
        ];
    });
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Caja — Nueva Venta</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Manrope', sans-serif; background: #f1f5f9; }

        /* Layout POS */
        .pos-shell {
            display: grid;
            grid-template-columns: 1fr 400px;
            grid-template-rows: 56px 1fr;
            height: 100dvh;
            overflow: hidden;
        }
        @media (max-width: 900px) {
            .pos-shell { grid-template-columns: 1fr; grid-template-rows: 56px 1fr auto; }
            .pos-right  { max-height: 55vh; overflow-y: auto; }
        }

        /* Topbar */
        .pos-topbar {
            grid-column: 1 / -1;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.25rem;
            background: linear-gradient(90deg, #0f172a, #1e3a8a);
            color: white;
        }
        .pos-topbar-brand { font-size: .75rem; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
        .pos-topbar-meta  { display: flex; align-items: center; gap: .75rem; font-size: .75rem; }
        .pos-status-dot   { width: 8px; height: 8px; border-radius: 50%; }

        /* LEFT — productos */
        .pos-left {
            display: flex; flex-direction: column;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
        }
        .pos-search-bar {
            padding: .75rem 1rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
            z-index: 100;
        }
        .pos-search-input {
            width: 100%; padding: .65rem 1rem .65rem 2.8rem;
            border-radius: 10px; border: 2px solid #dbeafe;
            font-size: .9rem; font-family: inherit; font-weight: 500;
            outline: none; background: #f8fafc; color: #1e293b;
            transition: border-color .15s; box-sizing: border-box;
        }
        .pos-search-input:focus { border-color: #3b82f6; background: white; }
        .pos-search-icon {
            position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
            color: #94a3b8; width: 16px; height: 16px; pointer-events: none;
        }
        .pos-search-dropdown {
            position: absolute; left: 0; right: 0; top: 100%;
            background: white; border: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,.18);
            z-index: 200; display: none; overflow: hidden;
        }
        .pos-search-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: .65rem 1rem; cursor: pointer; border-bottom: 1px solid #f1f5f9;
            transition: background .1s;
        }
        .pos-search-item:last-child { border-bottom: none; }
        .pos-search-item:hover { background: #eff6ff; }
        .pos-search-item-name { font-size: .85rem; font-weight: 600; color: #1e293b; }
        .pos-search-item-sub  { font-size: .72rem; color: #94a3b8; margin-top: 1px; }
        .pos-search-item-price{ font-size: .9rem; font-weight: 800; color: #1d4ed8; }

        /* Lista de items en la venta */
        .pos-items-area {
            flex: 1; overflow-y: auto; padding: .75rem 1rem;
        }
        .pos-items-area::-webkit-scrollbar { width: 6px; }
        .pos-items-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }

        .pos-item-row {
            display: grid;
            grid-template-columns: 1fr auto auto auto auto;
            align-items: center; gap: .5rem;
            background: white; border-radius: 12px;
            padding: .65rem .85rem; margin-bottom: .5rem;
            border: 1px solid #e2e8f0;
            transition: box-shadow .15s;
        }
        .pos-item-row:hover { box-shadow: 0 4px 12px -6px rgba(37,99,235,.18); }
        .pos-item-name  { font-size: .85rem; font-weight: 700; color: #1e293b; }
        .pos-item-mode  { font-size: .7rem; color: #64748b; margin-top: 2px; }
        .pos-item-qty-wrap { display: flex; align-items: center; gap: .3rem; }
        .pos-qty-btn {
            width: 28px; height: 28px; border-radius: 8px; border: none; cursor: pointer;
            font-size: 1rem; font-weight: 700; display: flex; align-items: center; justify-content: center;
            transition: background .1s;
        }
        .pos-qty-btn-minus { background: #fee2e2; color: #dc2626; }
        .pos-qty-btn-minus:hover { background: #fecaca; }
        .pos-qty-btn-plus  { background: #dbeafe; color: #2563eb; }
        .pos-qty-btn-plus:hover  { background: #bfdbfe; }
        .pos-qty-input {
            width: 60px; text-align: center; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: .3rem .2rem; font-size: .85rem; font-weight: 700;
            font-family: inherit; color: #1e293b; outline: none;
        }
        .pos-qty-input:focus { border-color: #3b82f6; }
        .pos-item-price { font-size: .8rem; color: #64748b; text-align: right; white-space: nowrap; }
        .pos-item-subtotal { font-size: .9rem; font-weight: 800; color: #1d4ed8; text-align: right; white-space: nowrap; min-width: 70px; }
        .pos-item-remove {
            background: none; border: none; cursor: pointer; color: #94a3b8; padding: .2rem;
            border-radius: 6px; transition: color .1s, background .1s; display: flex;
        }
        .pos-item-remove:hover { color: #dc2626; background: #fee2e2; }

        .pos-empty {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; color: #94a3b8; text-align: center; padding: 2rem;
        }
        .pos-empty svg { width: 56px; height: 56px; margin-bottom: .75rem; opacity: .4; }
        .pos-empty p   { font-size: .85rem; font-weight: 600; }
        .pos-empty span{ font-size: .75rem; }

        /* RIGHT — cobro */
        .pos-right {
            display: flex; flex-direction: column;
            background: white;
            overflow-y: auto;
        }
        .pos-right::-webkit-scrollbar { width: 6px; }
        .pos-right::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }

        .pos-right-section {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .pos-right-label {
            font-size: .68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .18em; color: #94a3b8; margin-bottom: .6rem;
        }

        /* Total grande */
        .pos-total-box {
            background: #1e3a8a;
            padding: 1rem 1.1rem;
            color: white;
        }
        .pos-total-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .16em; color: #93c5fd; }
        .pos-total-amount { font-size: 2.4rem; font-weight: 800; line-height: 1.1; margin: .2rem 0; }
        .pos-total-items  { font-size: .75rem; color: #93c5fd; }

        /* Métodos de pago tabs */
        .pos-pay-tabs { display: flex; gap: .4rem; }
        .pos-pay-tab {
            flex: 1; padding: .5rem .3rem; border-radius: 8px; border: 2px solid #e2e8f0;
            font-size: .72rem; font-weight: 700; text-align: center; cursor: pointer;
            background: #f8fafc; color: #64748b; transition: all .15s; font-family: inherit;
        }
        .pos-pay-tab.active { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }

        /* Campos dinámicos */
        .pos-field { margin-bottom: .6rem; }
        .pos-field label { display: block; font-size: .75rem; font-weight: 600; color: #475569; margin-bottom: .3rem; }
        .pos-field input {
            width: 100%; padding: .55rem .8rem; border-radius: 9px; border: 1.5px solid #e2e8f0;
            font-size: .9rem; font-weight: 600; font-family: inherit; color: #1e293b; outline: none;
            transition: border-color .15s; box-sizing: border-box;
        }
        .pos-field input:focus { border-color: #3b82f6; }
        .pos-field input.highlight { border-color: #10b981; background: #f0fdf4; color: #065f46; }

        /* Cambio */
        .pos-change-box {
            background: #f0fdf4; border-radius: 10px; padding: .65rem .85rem;
            display: flex; align-items: center; justify-content: space-between;
            margin-top: .5rem;
        }
        .pos-change-label { font-size: .75rem; font-weight: 600; color: #065f46; }
        .pos-change-amount { font-size: 1.4rem; font-weight: 800; color: #059669; }

        /* QR panel */
        .pos-qr-box {
            background: #eff6ff; border-radius: 12px; padding: .85rem;
            display: flex; gap: .85rem; align-items: center;
        }
        .pos-qr-img { width: 90px; height: 90px; border-radius: 8px; object-fit: contain; background: white; padding: 4px; border: 2px solid #bfdbfe; flex-shrink: 0; }
        .pos-qr-placeholder {
            width: 90px; height: 90px; border-radius: 8px; border: 2px dashed #93c5fd;
            background: white; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; flex-direction: column; text-align: center; padding: .4rem;
        }
        .pos-qr-amount { font-size: 1.6rem; font-weight: 800; color: #1d4ed8; }
        .pos-qr-sub    { font-size: .72rem; color: #3b82f6; }

        /* Botón confirmar */
        .pos-confirm-btn {
            width: 100%; padding: 1rem; border: none; border-radius: 12px; cursor: pointer;
            font-size: 1rem; font-weight: 800; font-family: inherit; letter-spacing: .04em;
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
            color: white; box-shadow: 0 12px 24px -12px rgba(29,78,216,.55);
            transition: transform .12s, box-shadow .12s;
        }
        .pos-confirm-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 16px 28px -12px rgba(29,78,216,.65); }
        .pos-confirm-btn:active:not(:disabled) { transform: translateY(0); }
        .pos-confirm-btn:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; }

        /* Descuento */
        .pos-discount-row { display: flex; align-items: center; gap: .5rem; }
        .pos-discount-row input { flex: 1; }
        .pos-discount-tag { font-size: .75rem; font-weight: 700; color: #dc2626; background: #fee2e2; padding: .2rem .5rem; border-radius: 6px; white-space: nowrap; }

        /* Cliente */
        .pos-client-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }

        /* Modo select en item */
        .pos-mode-sel {
            font-size: .7rem; border: 1px solid #e2e8f0; border-radius: 6px;
            padding: .15rem .3rem; color: #64748b; background: #f8fafc; cursor: pointer;
            font-family: inherit;
        }

        /* Alerts */
        .pos-alert { padding: .6rem 1rem; font-size: .78rem; font-weight: 600; }
        .pos-alert-error { background: #fee2e2; color: #991b1b; }
        .pos-alert-warn  { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>

<script>
    const PRODUCTS_DATA = @json($productsJson->values()->all());
</script>

<form method="POST" action="{{ route('sales.store') }}" id="sale-form">
@csrf

<div class="pos-shell">

    {{-- ── TOPBAR ── --}}
    <div class="pos-topbar">
        <div class="flex items-center gap-3">
            <span style="font-size:.7rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;opacity:.6">Carnicería</span>
            <span style="color:white;font-size:.85rem;font-weight:800;">PUNTO DE VENTA</span>
        </div>
        <div class="pos-topbar-meta">
            <span class="pos-status-dot" style="background: {{ $currentRegister ? '#4ade80' : '#f87171' }}"></span>
            <span style="opacity:.8">Caja {{ $currentRegister ? 'abierta' : 'cerrada' }}</span>
            <span style="opacity:.4">|</span>
            <span style="opacity:.8">{{ auth()->user()->name }}</span>
            <a href="{{ route('dashboard') }}"
               style="background:rgba(255,255,255,.1);padding:.3rem .8rem;border-radius:8px;font-size:.72rem;font-weight:700;color:white;text-decoration:none;margin-left:.5rem">
                ← Inicio
            </a>
        </div>
    </div>

    {{-- ── LEFT: productos escaneados ── --}}
    <div class="pos-left">

        {{-- Barra de búsqueda --}}
        <div class="pos-search-bar">
            @if(! $currentRegister)
                <div class="pos-alert pos-alert-error" style="border-radius:8px;margin-bottom:.5rem">
                    Caja cerrada — <a href="{{ route('cash-registers.index') }}" style="text-decoration:underline">Abrir caja</a>
                </div>
            @endif
            <div style="position:relative">
                <svg class="pos-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                </svg>
                <input type="text" id="pos-search" class="pos-search-input"
                       placeholder="Buscar producto o escanear código de barras..."
                       autocomplete="off" autofocus>
                <div id="pos-dropdown" class="pos-search-dropdown"></div>
            </div>
        </div>

        {{-- Lista de ítems --}}
        <div class="pos-items-area" id="pos-items-area">
            <div class="pos-empty" id="pos-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4"
                          d="M2.25 3h1.386a1.5 1.5 0 0 1 1.455 1.136L5.61 6H20.25a.75.75 0 0 1 .73.92l-1.5 6A.75.75 0 0 1 18.75 13.5H7.5a.75.75 0 0 1-.73-.57L4.152 4.5H2.25"/>
                </svg>
                <p>Sin productos aún</p>
                <span>Escribe o escanea para agregar</span>
            </div>
        </div>

        {{-- Errores de validación --}}
        @if ($errors->any())
            <div class="pos-alert pos-alert-error" style="border-top:1px solid #fecaca">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Campos hidden que se llenan por JS --}}
        <div id="hidden-fields" style="display:none"></div>
    </div>

    {{-- ── RIGHT: cobro ── --}}
    <div class="pos-right">

        {{-- Total --}}
        <div class="pos-total-box">
            <div class="pos-total-label">Total a cobrar</div>
            <div class="pos-total-amount" id="disp-total">Bs. 0.00</div>
            <div class="pos-total-items" id="disp-items">0 productos</div>
        </div>

        {{-- Cliente --}}
        <div class="pos-right-section">
            <div class="pos-right-label">Cliente (opcional)</div>
            <div class="pos-client-grid">
                <div class="pos-field" style="margin:0">
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                           placeholder="Nombre" id="customer-name">
                </div>
                <div class="pos-field" style="margin:0">
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                           placeholder="Celular">
                </div>
            </div>
        </div>

        {{-- Método de pago --}}
        <div class="pos-right-section">
            <div class="pos-right-label">Método de pago</div>
            <div class="pos-pay-tabs">
                <button type="button" class="pos-pay-tab active" data-method="cash">💵 Efectivo</button>
                <button type="button" class="pos-pay-tab" data-method="qr">📱 QR</button>
                <button type="button" class="pos-pay-tab" data-method="mixed">⚡ Mixto</button>
                <button type="button" class="pos-pay-tab" data-method="credit">📋 Crédito</button>
            </div>
            <input type="hidden" name="payment_method" id="payment-method" value="{{ old('payment_method', 'cash') }}">
        </div>

        {{-- Panel Efectivo --}}
        <div class="pos-right-section" id="panel-cash">
            <div class="pos-right-label">Cobro en efectivo</div>
            <div class="pos-field">
                <label>Efectivo recibido (Bs.)</label>
                <input type="number" step="0.01" min="0" id="cash-received"
                       value="{{ old('cash_received', 0) }}"
                       placeholder="0.00" class="highlight">
            </div>
            <div class="pos-change-box">
                <span class="pos-change-label">Cambio a devolver</span>
                <span class="pos-change-amount" id="disp-change">Bs. 0.00</span>
            </div>
        </div>

        {{-- Panel QR --}}
        <div class="pos-right-section" id="panel-qr" style="display:none">
            <div class="pos-right-label">Pago por QR</div>
            <div class="pos-qr-box">
                @if(file_exists(public_path('images/qr-pago.png')))
                    <img src="{{ asset('images/qr-pago.png') }}" alt="QR" class="pos-qr-img">
                @else
                    <div class="pos-qr-placeholder">
                        <svg width="28" height="28" fill="none" stroke="#93c5fd" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Zm0 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 19.125v-4.5Zm9.75-9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/>
                        </svg>
                        <span style="font-size:.6rem;color:#93c5fd;margin-top:4px">Sube tu QR en<br>public/images/qr-pago.png</span>
                    </div>
                @endif
                <div>
                    <div class="pos-qr-sub">Monto a cobrar</div>
                    <div class="pos-qr-amount" id="disp-qr-amount">Bs. 0.00</div>
                    <div class="pos-qr-sub" style="margin-top:.3rem">Muestra este código al cliente</div>
                </div>
            </div>
        </div>

        {{-- Panel Mixto --}}
        <div class="pos-right-section" id="panel-mixed" style="display:none">
            <div class="pos-right-label">Pago mixto</div>
            <div class="pos-field">
                <label>Monto en efectivo (Bs.)</label>
                <input type="number" step="0.01" min="0" id="mixed-cash" placeholder="0.00" value="0">
            </div>
            <div class="pos-field">
                <label>Monto por QR (Bs.)</label>
                <input type="number" step="0.01" min="0" id="mixed-qr" placeholder="0.00" value="0">
            </div>
            <div class="pos-field">
                <label>Efectivo recibido (Bs.)</label>
                <input type="number" step="0.01" min="0" id="mixed-received" placeholder="0.00"
                       value="0" class="highlight">
            </div>
            <div class="pos-change-box">
                <span class="pos-change-label">Cambio a devolver</span>
                <span class="pos-change-amount" id="disp-change-mixed">Bs. 0.00</span>
            </div>
        </div>

        {{-- Panel Crédito --}}
        <div class="pos-right-section" id="panel-credit" style="display:none">
            <div class="pos-right-label">Venta a crédito</div>
            <div class="pos-field">
                <label>Nombre del cliente <span style="color:#dc2626">*</span></label>
                <input type="text" id="credit-name" placeholder="Requerido para crédito"
                       oninput="document.getElementById('customer-name').value = this.value">
            </div>
            <div style="background:#fef3c7;border-radius:8px;padding:.6rem .8rem;font-size:.75rem;color:#92400e;font-weight:600">
                ⚠ El monto quedará pendiente de cobro. Asegúrate de registrar el nombre.
            </div>
        </div>

        {{-- UN SOLO set de hidden fields de pago — escritos siempre por JS --}}
        <input type="hidden" name="cash_amount"   id="f-cash-amount"    value="0">
        <input type="hidden" name="qr_amount"     id="f-qr-amount"      value="0">
        <input type="hidden" name="cash_received" id="f-cash-received"  value="0">

        {{-- Descuento --}}
        <div class="pos-right-section">
            <div class="pos-right-label">Descuento</div>
            <div class="pos-discount-row">
                <div class="pos-field" style="margin:0;flex:1">
                    <input type="number" step="0.01" min="0" name="discount_amount"
                           id="discount" value="{{ old('discount_amount', 0) }}" placeholder="0.00">
                </div>
                <span class="pos-discount-tag" id="discount-tag" style="display:none">- Bs. 0.00</span>
            </div>
        </div>

        {{-- Nota --}}
        <div class="pos-right-section">
            <div class="pos-right-label">Nota (opcional)</div>
            <div class="pos-field" style="margin:0">
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Observación de caja">
            </div>
        </div>

        {{-- Botón confirmar --}}
        <div style="padding:1rem 1.1rem;margin-top:auto">
            <button type="submit" class="pos-confirm-btn" id="confirm-btn"
                    {{ ! $currentRegister ? 'disabled' : '' }}>
                {{ $currentRegister ? '✓ Confirmar venta' : 'Abre la caja primero' }}
            </button>
            <a href="{{ route('sales.index') }}"
               style="display:block;text-align:center;margin-top:.6rem;font-size:.75rem;color:#94a3b8;text-decoration:none;font-weight:600">
                Cancelar y volver al historial
            </a>
        </div>
    </div>

</div>{{-- /pos-shell --}}
</form>

<script>
(function () {
    /* ── Estado global ─────────────────────────────────── */
    let cart = []; // { product, qty, mode }

    const fmt  = v => `Bs. ${Number(v).toFixed(2)}`;
    const fmtN = v =>  Number(v).toFixed(2);

    /* ── DOM refs ─────────────────────────────────────── */
    const searchEl       = document.getElementById('pos-search');
    const dropdownEl     = document.getElementById('pos-dropdown');
    const itemsArea      = document.getElementById('pos-items-area');
    const emptyEl        = document.getElementById('pos-empty');
    const hiddenFields   = document.getElementById('hidden-fields');

    const dispTotal      = document.getElementById('disp-total');
    const dispItems      = document.getElementById('disp-items');
    const dispChange     = document.getElementById('disp-change');
    const dispChangeMix  = document.getElementById('disp-change-mixed');
    const dispQrAmt      = document.getElementById('disp-qr-amount');
    const discountEl     = document.getElementById('discount');
    const discountTag    = document.getElementById('discount-tag');

    const payTabs        = document.querySelectorAll('.pos-pay-tab');
    const payHidden      = document.getElementById('payment-method');

    const panelCash      = document.getElementById('panel-cash');
    const panelQr        = document.getElementById('panel-qr');
    const panelMixed     = document.getElementById('panel-mixed');
    const panelCredit    = document.getElementById('panel-credit');

    const cashReceivedEl = document.getElementById('cash-received');  // display input (no name=)

    // Únicos hidden fields de pago — siempre actualizados por JS
    const fCashAmount   = document.getElementById('f-cash-amount');
    const fQrAmount     = document.getElementById('f-qr-amount');
    const fCashReceived = document.getElementById('f-cash-received');

    const mixedCashEl    = document.getElementById('mixed-cash');
    const mixedQrEl      = document.getElementById('mixed-qr');
    const mixedRecEl     = document.getElementById('mixed-received');

    let currentMethod = '{{ old("payment_method", "cash") }}';

    /* ── Métodos de pago ──────────────────────────────── */
    function switchPayMethod(method) {
        currentMethod = method;
        payHidden.value = method;
        payTabs.forEach(t => t.classList.toggle('active', t.dataset.method === method));
        panelCash.style.display   = method === 'cash'   ? '' : 'none';
        panelQr.style.display     = method === 'qr'     ? '' : 'none';
        panelMixed.style.display  = method === 'mixed'  ? '' : 'none';
        panelCredit.style.display = method === 'credit' ? '' : 'none';
        recalc();
    }

    payTabs.forEach(tab => tab.addEventListener('click', () => switchPayMethod(tab.dataset.method)));

    /* ── Carrito ──────────────────────────────────────── */
    function getNet() {
        const gross    = cart.reduce((s, c) => s + unitPrice(c) * c.qty, 0);
        const discount = Math.max(0, parseFloat(discountEl.value) || 0);
        return Math.max(0, gross - discount);
    }

    function unitPrice(cartItem) {
        const p = cartItem.product;
        return cartItem.mode === 'package' && p.package_enabled ? p.package_price : p.price;
    }

    function recalc() {
        const net = getNet();

        dispTotal.textContent = fmt(net);
        dispItems.textContent = `${cart.length} producto${cart.length !== 1 ? 's' : ''}`;

        // Descuento tag
        const disc = parseFloat(discountEl.value) || 0;
        if (disc > 0) {
            discountTag.style.display = '';
            discountTag.textContent = `- ${fmt(disc)}`;
        } else {
            discountTag.style.display = 'none';
        }

        // Siempre actualizamos los únicos hidden fields según el método activo
        if (currentMethod === 'cash') {
            fCashAmount.value   = fmtN(net);
            fQrAmount.value     = '0';
            const rec = parseFloat(cashReceivedEl.value) || 0;
            fCashReceived.value = fmtN(rec);
            dispChange.textContent = fmt(Math.max(0, rec - net));
        } else if (currentMethod === 'qr') {
            fCashAmount.value   = '0';
            fQrAmount.value     = fmtN(net);
            fCashReceived.value = '0';
            if (dispQrAmt) dispQrAmt.textContent = fmt(net);
        } else if (currentMethod === 'mixed') {
            const eff = parseFloat(mixedCashEl.value) || 0;
            const qr  = parseFloat(mixedQrEl.value)   || 0;
            const rec = parseFloat(mixedRecEl.value)   || 0;
            fCashAmount.value   = fmtN(eff);
            fQrAmount.value     = fmtN(qr);
            fCashReceived.value = fmtN(rec);
            dispChangeMix.textContent = fmt(Math.max(0, rec - eff));
        } else if (currentMethod === 'credit') {
            fCashAmount.value   = '0';
            fQrAmount.value     = '0';
            fCashReceived.value = '0';
        }

        renderHiddenFields();
        renderCart();
    }

    /* ── Hidden fields para el form ─────────────────────── */
    function renderHiddenFields() {
        hiddenFields.innerHTML = '';
        cart.forEach((c, i) => {
            add(`items[${i}][product_id]`, c.product.id);
            add(`items[${i}][quantity]`,   c.qty);
            add(`items[${i}][pricing_mode]`, c.mode);
        });

        function add(name, value) {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = name; inp.value = value;
            hiddenFields.appendChild(inp);
        }
    }

    /* ── Renderizado del carrito ─────────────────────── */
    function renderCart() {
        // Quita filas anteriores (deja el empty state)
        itemsArea.querySelectorAll('.pos-item-row').forEach(r => r.remove());

        if (cart.length === 0) {
            emptyEl.style.display = '';
            return;
        }
        emptyEl.style.display = 'none';

        cart.forEach((c, i) => {
            const p        = c.product;
            const price    = unitPrice(c);
            const subtotal = price * c.qty;
            const isWeight = p.sale_type === 'weight';
            const pkgOk    = p.package_enabled === 1;

            const row = document.createElement('div');
            row.className = 'pos-item-row';
            row.innerHTML = `
                <div>
                    <div class="pos-item-name">${p.name}</div>
                    <div class="pos-item-mode">
                        ${pkgOk ? `<select class="pos-mode-sel" data-idx="${i}">
                            <option value="standard" ${c.mode==='standard'?'selected':''}>Unidad</option>
                            <option value="package"  ${c.mode==='package' ?'selected':''}>Paquete (${p.package_name})</option>
                        </select>` : `<span style="font-size:.7rem;color:#94a3b8">${isWeight ? `Por peso (${p.weight_unit})` : 'Por unidad'}</span>`}
                    </div>
                </div>
                <div class="pos-item-qty-wrap">
                    <button type="button" class="pos-qty-btn pos-qty-btn-minus" data-idx="${i}">−</button>
                    <input type="${isWeight ? 'number' : 'number'}"
                           step="${isWeight ? '0.001' : '1'}"
                           min="${isWeight ? '0.001' : '1'}"
                           value="${c.qty}"
                           class="pos-qty-input"
                           data-idx="${i}">
                    <button type="button" class="pos-qty-btn pos-qty-btn-plus" data-idx="${i}">+</button>
                </div>
                <div class="pos-item-price">Bs. ${fmtN(price)}</div>
                <div class="pos-item-subtotal">${fmt(subtotal)}</div>
                <button type="button" class="pos-item-remove" data-idx="${i}" title="Quitar">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;

            // Botones qty
            row.querySelector(`.pos-qty-btn-minus[data-idx="${i}"]`).addEventListener('click', () => {
                const step = isWeight ? 0.25 : 1;
                cart[i].qty = Math.max(isWeight ? 0.001 : 1, parseFloat((cart[i].qty - step).toFixed(3)));
                recalc();
            });
            row.querySelector(`.pos-qty-btn-plus[data-idx="${i}"]`).addEventListener('click', () => {
                const step = isWeight ? 0.25 : 1;
                cart[i].qty = parseFloat((cart[i].qty + step).toFixed(3));
                recalc();
            });

            // Input manual qty
            row.querySelector(`.pos-qty-input[data-idx="${i}"]`).addEventListener('change', e => {
                const v = parseFloat(e.target.value);
                if (v > 0) { cart[i].qty = v; recalc(); }
            });

            // Modo
            const modeSel = row.querySelector(`.pos-mode-sel[data-idx="${i}"]`);
            if (modeSel) modeSel.addEventListener('change', e => {
                cart[i].mode = e.target.value;
                recalc();
            });

            // Quitar
            row.querySelector(`.pos-item-remove[data-idx="${i}"]`).addEventListener('click', () => {
                cart.splice(i, 1);
                recalc();
            });

            itemsArea.appendChild(row);
        });
    }

    /* ── Agregar producto al carrito ─────────────────── */
    function addProduct(product) {
        const existing = cart.findIndex(c => c.product.id === product.id && c.mode === 'standard');
        if (existing >= 0 && product.sale_type === 'unit') {
            cart[existing].qty++;
        } else {
            cart.push({ product, qty: 1, mode: 'standard' });
        }
        recalc();
        searchEl.value = '';
        dropdownEl.style.display = 'none';
        searchEl.focus();
    }

    /* ── Búsqueda ────────────────────────────────────── */
    function findProducts(q) {
        q = q.trim().toLowerCase();
        if (!q) return [];
        return PRODUCTS_DATA.filter(p =>
            p.name.toLowerCase().includes(q) ||
            (p.barcode && p.barcode.toLowerCase().includes(q))
        ).slice(0, 7);
    }

    function renderDropdown(results) {
        dropdownEl.innerHTML = '';
        if (!results.length) { dropdownEl.style.display = 'none'; return; }

        results.forEach(p => {
            const el = document.createElement('div');
            el.className = 'pos-search-item';
            el.innerHTML = `
                <div>
                    <div class="pos-search-item-name">${p.name}</div>
                    <div class="pos-search-item-sub">${p.barcode ? 'Cód: ' + p.barcode + ' · ' : ''}Stock: ${p.stock_label}</div>
                </div>
                <div class="pos-search-item-price">${fmt(p.price)}</div>
            `;
            el.addEventListener('click', () => addProduct(p));
            dropdownEl.appendChild(el);
        });
        dropdownEl.style.display = 'block';
    }

    let searchTimer;
    searchEl.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = searchEl.value.trim();
        if (!q) { dropdownEl.style.display = 'none'; return; }
        searchTimer = setTimeout(() => {
            // Barcode exacto → agrega directo
            const exact = PRODUCTS_DATA.find(p => p.barcode && p.barcode === q);
            if (exact) { addProduct(exact); return; }
            renderDropdown(findProducts(q));
        }, 180);
    });

    searchEl.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const results = findProducts(searchEl.value);
            if (results.length === 1) addProduct(results[0]);
            else if (results.length > 1) renderDropdown(results);
        }
        if (e.key === 'Escape') dropdownEl.style.display = 'none';
    });

    document.addEventListener('click', e => {
        if (!searchEl.contains(e.target) && !dropdownEl.contains(e.target))
            dropdownEl.style.display = 'none';
    });

    /* ── Eventos ─────────────────────────────────────── */
    discountEl.addEventListener('input', recalc);
    cashReceivedEl.addEventListener('input', recalc);
    if (mixedCashEl)  mixedCashEl.addEventListener('input', recalc);
    if (mixedQrEl)    mixedQrEl.addEventListener('input', recalc);
    if (mixedRecEl)   mixedRecEl.addEventListener('input', recalc);

    // Validación antes de enviar
    document.getElementById('sale-form').addEventListener('submit', function (e) {
        if (cart.length === 0) {
            e.preventDefault();
            alert('Agrega al menos un producto antes de confirmar la venta.');
            return;
        }
        if (currentMethod === 'credit') {
            const name = document.getElementById('customer-name').value.trim() ||
                         document.getElementById('credit-name').value.trim();
            if (!name) {
                e.preventDefault();
                alert('Para ventas a crédito debes ingresar el nombre del cliente.');
                document.getElementById('credit-name').focus();
                return;
            }
        }
    });

    /* ── Init ────────────────────────────────────────── */
    switchPayMethod(currentMethod);
    recalc();
})();
</script>
</body>
</html>
