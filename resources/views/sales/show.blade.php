<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Ventas</p>
                <h2 class="page-title mt-2">Recibo de venta</h2>
                <p class="page-subtitle mt-3">Comprobante interno de caja sin facturación electrónica.</p>
            </div>

            <div class="module-header-actions print:hidden">
                <a href="{{ route('sales.index') }}" class="btn-secondary">Volver</a>
                <button type="button" onclick="window.print()" class="btn-primary">Imprimir recibo</button>
            </div>
        </div>
    </x-slot>

    {{-- Estilos de impresión --}}
    <style>
        @media print {
            body { background: white !important; }
            .print-receipt {
                max-width: 80mm !important;
                margin: 0 auto !important;
                font-size: 11px !important;
                font-family: 'Courier New', Courier, monospace !important;
            }
            .print-receipt .receipt-header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
            .print-receipt .receipt-header h1 { font-size: 14px; font-weight: bold; margin: 0; }
            .print-receipt .receipt-header p  { font-size: 10px; margin: 2px 0; }
            .print-receipt .receipt-meta td  { padding: 1px 0; font-size: 10px; }
            .print-receipt .receipt-items th,
            .print-receipt .receipt-items td { padding: 2px 0; font-size: 10px; }
            .print-receipt .receipt-totals { border-top: 1px dashed #000; padding-top: 6px; }
            .print-receipt .receipt-totals td { padding: 2px 0; font-size: 10px; }
            .print-receipt .receipt-footer { text-align: center; border-top: 1px dashed #000; margin-top: 8px; padding-top: 6px; font-size: 9px; color: #555; }
        }
    </style>

    <div class="mx-auto max-w-4xl space-y-6">
        @if (session('status'))
            <div class="notice-success print:hidden">{{ session('status') }}</div>
        @endif

        <article class="form-card print:shadow-none print:p-0 print:border-0 print-receipt" id="receipt">

            {{-- Cabecera del negocio (visible en impresión) --}}
            <div class="receipt-header hidden print:block mb-4">
                <h1>{{ config('app.name', 'Carnicería') }}</h1>
                <p>Colcapirhua, Cochabamba - Bolivia</p>
                <p>Recibo de venta</p>
            </div>

            {{-- Cabecera normal (pantalla) --}}
            <div class="flex flex-col gap-4 border-b border-blue-100 pb-5 sm:flex-row sm:items-start sm:justify-between print:hidden">
                <div>
                    <p class="section-kicker">{{ config('app.name', 'Tienda') }}</p>
                    <h3 class="mt-1 text-2xl font-bold text-slate-900">Recibo de venta</h3>
                </div>
                <div class="text-left text-sm text-slate-600 sm:text-right">
                    <p><span class="font-semibold text-slate-800">Nro:</span> {{ $sale->sale_number }}</p>
                    <p><span class="font-semibold text-slate-800">Fecha:</span> {{ $sale->sold_at->format('d/m/Y H:i') }}</p>
                    <p><span class="font-semibold text-slate-800">Cajero:</span> {{ $sale->user?->name }}</p>
                    <p><span class="font-semibold text-slate-800">Pago:</span> {{ $sale->paymentMethodLabel() }}</p>
                    <p><span class="font-semibold text-slate-800">Cliente:</span> {{ $sale->customer_name ?: 'Mostrador' }}</p>
                </div>
            </div>

            {{-- Meta impresión --}}
            <table class="receipt-meta w-full hidden print:table mb-3" style="border-collapse:collapse">
                <tr><td style="font-weight:bold">Nro:</td><td>{{ $sale->sale_number }}</td></tr>
                <tr><td style="font-weight:bold">Fecha:</td><td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td></tr>
                <tr><td style="font-weight:bold">Cajero:</td><td>{{ $sale->user?->name }}</td></tr>
                <tr><td style="font-weight:bold">Pago:</td><td>{{ $sale->paymentMethodLabel() }}</td></tr>
                <tr><td style="font-weight:bold">Cliente:</td><td>{{ $sale->customer_name ?: 'Mostrador' }}</td></tr>
            </table>

            {{-- Alerta crédito --}}
            @if ($sale->payment_method === 'credit')
                <div class="mt-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 print:border print:border-black print:bg-white print:rounded-none">
                    <svg class="h-5 w-5 shrink-0 text-amber-600 print:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div>
                        <p class="font-semibold text-amber-800 print:font-bold">*** VENTA A CRÉDITO ***</p>
                        <p class="text-sm text-amber-700">Monto pendiente de cobro: <strong>Bs. {{ number_format((float) $sale->total, 2) }}</strong></p>
                        <p class="text-sm text-amber-700">Cliente: <strong>{{ $sale->customer_name }}</strong></p>
                    </div>
                </div>
            @endif

            {{-- Tabla de ítems --}}
            <div class="mt-5 overflow-x-auto">
                <table class="table-shell receipt-items print:w-full" style="border-collapse:collapse">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell print:text-left print:border-b print:border-black print:pb-1">Producto</th>
                            <th class="table-head-cell print:text-right print:border-b print:border-black print:pb-1">Cant.</th>
                            <th class="table-head-cell print:text-right print:border-b print:border-black print:pb-1">Precio</th>
                            <th class="table-head-cell print:text-right print:border-b print:border-black print:pb-1">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr class="table-row">
                                <td class="table-cell print:py-1">{{ $item->product_name }}</td>
                                <td class="table-cell print:text-right print:py-1">{{ $item->quantityLabel() }}</td>
                                <td class="table-cell print:text-right print:py-1">Bs. {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="table-cell font-semibold text-slate-900 print:text-right print:py-1">Bs. {{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totales --}}
            <div class="mt-6 flex justify-end print:mt-2">
                <div class="w-full max-w-xs space-y-2 rounded-2xl border border-blue-100 bg-blue-50/60 p-4 print:max-w-full print:rounded-none print:border-0 print:bg-white print:p-0 receipt-totals">
                    <div class="flex justify-between text-sm text-slate-700">
                        <span>Subtotal</span>
                        <span>Bs. {{ number_format((float) $sale->subtotal, 2) }}</span>
                    </div>
                    @if ((float) $sale->discount_amount > 0)
                    <div class="flex justify-between text-sm text-slate-700">
                        <span>Descuento</span>
                        <span>- Bs. {{ number_format((float) $sale->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold text-slate-900 print:text-sm">
                        <span>TOTAL</span>
                        <span>Bs. {{ number_format((float) $sale->total, 2) }}</span>
                    </div>
                    <div class="border-t border-blue-100 pt-2 text-sm text-slate-700 print:border-t print:border-black print:pt-1">
                        @if ($sale->payment_method === 'cash' || $sale->payment_method === 'mixed')
                            @if ((float) $sale->cash_amount > 0)
                            <div class="flex justify-between">
                                <span>Efectivo</span>
                                <span>Bs. {{ number_format((float) $sale->cash_amount, 2) }}</span>
                            </div>
                            @endif
                            @if ((float) $sale->cash_received > 0)
                            <div class="flex justify-between">
                                <span>Recibido</span>
                                <span>Bs. {{ number_format((float) $sale->cash_received, 2) }}</span>
                            </div>
                            @endif
                        @endif
                        @if ($sale->payment_method === 'qr' || $sale->payment_method === 'mixed')
                            @if ((float) $sale->qr_amount > 0)
                            <div class="flex justify-between">
                                <span>QR</span>
                                <span>Bs. {{ number_format((float) $sale->qr_amount, 2) }}</span>
                            </div>
                            @endif
                        @endif
                        @if ((float) $sale->change_amount > 0)
                        <div class="flex justify-between font-semibold text-green-700">
                            <span>Cambio</span>
                            <span>Bs. {{ number_format((float) $sale->change_amount, 2) }}</span>
                        </div>
                        @endif
                        @if ($sale->payment_method === 'credit')
                        <div class="flex justify-between font-bold text-amber-700">
                            <span>Crédito pendiente</span>
                            <span>Bs. {{ number_format((float) $sale->total, 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($sale->notes)
                <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50/60 p-4 text-sm text-slate-700 print:border print:border-black print:bg-white print:rounded-none print:mt-3">
                    <p class="font-semibold text-slate-900">Nota</p>
                    <p class="mt-1">{{ $sale->notes }}</p>
                </div>
            @endif

            {{-- Pie del recibo (solo impresión) --}}
            <div class="receipt-footer hidden print:block mt-4">
                <p>¡Gracias por su compra!</p>
                <p>{{ config('app.name') }} — Colcapirhua</p>
            </div>
        </article>
    </div>
</x-app-layout>
