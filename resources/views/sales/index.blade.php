<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Ventas</p>
                <h2 class="page-title mt-2">Registro diario</h2>
                <p class="page-subtitle mt-3">Consulta las ventas realizadas en caja, filtra por fecha y revisa el historial operativo.</p>
            </div>

            <a href="{{ route('sales.create') }}" class="btn-primary">Nueva venta</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="notice-success">{{ session('status') }}</div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2">
            <article class="metric-card">
                <p class="metric-label">Ventas hoy</p>
                <p class="metric-value">{{ $todaySales }}</p>
                <p class="metric-meta">Transacciones registradas en la fecha actual.</p>
            </article>

            <article class="metric-card">
                <p class="metric-label">Monto hoy</p>
                <p class="metric-value">S/ {{ number_format($todayTotal, 2) }}</p>
                <p class="metric-meta">Importe acumulado del dia.</p>
            </article>

            <article class="metric-card">
                <p class="metric-label">Caja actual</p>
                <p class="metric-value">{{ $currentRegister ? 'Abierta' : 'Cerrada' }}</p>
                <p class="metric-meta">{{ $currentRegister ? 'Apertura: S/ ' . number_format((float) $currentRegister->opening_amount, 2) : 'Abre caja antes de vender.' }}</p>
            </article>
        </section>

        <div class="shell-panel-strong p-4">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input type="text" name="q" value="{{ $q }}" class="field-input" placeholder="Nro venta o cajero">
                <input type="date" name="from" value="{{ $from }}" class="field-input">
                <input type="date" name="to" value="{{ $to }}" class="field-input">
                <button class="btn-secondary">Filtrar historial</button>
            </form>
        </div>

        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Nro venta</th>
                            <th class="table-head-cell">Fecha</th>
                            <th class="table-head-cell">Cajero</th>
                            <th class="table-head-cell">Cliente</th>
                            <th class="table-head-cell">Pago</th>
                            <th class="table-head-cell">Items</th>
                            <th class="table-head-cell">Total</th>
                            <th class="table-head-cell">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr class="table-row">
                                <td class="table-cell font-semibold text-slate-900">{{ $sale->sale_number }}</td>
                                <td class="table-cell">{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                                <td class="table-cell">{{ $sale->user?->name }}</td>
                                <td class="table-cell">{{ $sale->customer_name ?: 'Mostrador' }}</td>
                                <td class="table-cell">{{ $sale->paymentMethodLabel() }}</td>
                                <td class="table-cell">{{ $sale->items->count() }}</td>
                                <td class="table-cell font-semibold text-slate-900">S/ {{ number_format((float) $sale->total, 2) }}</td>
                                <td class="table-cell">
                                    <a href="{{ route('sales.show', $sale) }}" class="action-link">Ver recibo</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="8" class="empty-state">No hay ventas registradas para los filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
