<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Ventas</p>
                <h2 class="page-title mt-2">Recibo simple</h2>
                <p class="page-subtitle mt-3">Comprobante interno de caja sin facturacion electronica.</p>
            </div>

            <div class="module-header-actions">
                <a href="{{ route('sales.index') }}" class="btn-secondary">Volver</a>
                <button type="button" onclick="window.print()" class="btn-primary">Imprimir recibo</button>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6 print:max-w-none">
        @if (session('status'))
            <div class="notice-success print:hidden">{{ session('status') }}</div>
        @endif

        <article class="form-card print:shadow-none print:border print:border-slate-200 print:bg-white" id="receipt">
            <div class="flex flex-col gap-4 border-b border-blue-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="section-kicker">{{ config('app.name', 'Tienda') }}</p>
                    <h3 class="mt-1 text-2xl font-bold text-slate-900">Recibo de venta</h3>
                </div>
                <div class="text-left text-sm text-slate-600 sm:text-right">
                    <p><span class="font-semibold text-slate-800">Nro:</span> {{ $sale->sale_number }}</p>
                    <p><span class="font-semibold text-slate-800">Fecha:</span> {{ $sale->sold_at->format('d/m/Y H:i') }}</p>
                    <p><span class="font-semibold text-slate-800">Caja:</span> {{ $sale->user?->name }}</p>
                    <p><span class="font-semibold text-slate-800">Pago:</span> {{ $sale->paymentMethodLabel() }}</p>
                    <p><span class="font-semibold text-slate-800">Cliente:</span> {{ $sale->customer_name ?: 'Mostrador' }}</p>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Producto</th>
                            <th class="table-head-cell">Cantidad</th>
                            <th class="table-head-cell">Precio</th>
                            <th class="table-head-cell">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr class="table-row">
                                <td class="table-cell">{{ $item->product_name }}</td>
                                <td class="table-cell">{{ $item->quantityLabel() }}</td>
                                <td class="table-cell">Bs. {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="table-cell font-semibold text-slate-900">Bs. {{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <div class="w-full max-w-xs space-y-2 rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
                    <div class="flex justify-between text-sm text-slate-700">
                        <span>Subtotal</span>
                        <span>Bs. {{ number_format((float) $sale->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-700">
                        <span>Descuento</span>
                        <span>Bs. {{ number_format((float) $sale->discount_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-slate-900">
                        <span>Total</span>
                        <span>Bs. {{ number_format((float) $sale->total, 2) }}</span>
                    </div>
                    <div class="border-t border-blue-100 pt-2 text-sm text-slate-700">
                        <div class="flex justify-between">
                            <span>Efectivo</span>
                            <span>Bs. {{ number_format((float) $sale->cash_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>QR</span>
                            <span>Bs. {{ number_format((float) $sale->qr_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Cambio</span>
                            <span>Bs. {{ number_format((float) $sale->change_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($sale->notes)
                <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50/60 p-4 text-sm text-slate-700">
                    <p class="font-semibold text-slate-900">Nota</p>
                    <p class="mt-1">{{ $sale->notes }}</p>
                </div>
            @endif
        </article>
    </div>
</x-app-layout>
