<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Reportes</p>
                <h2 class="page-title mt-2">Ventas</h2>
                <p class="page-subtitle mt-3">Analiza monto total, totales diarios y el historial consolidado de ventas del periodo seleccionado.</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="shell-panel-strong p-4">
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                <input type="date" name="from" value="{{ $from }}" class="field-input">
                <input type="date" name="to" value="{{ $to }}" class="field-input">
                <button class="btn-secondary">Actualizar reporte</button>
            </form>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="metric-card">
                <p class="metric-label">Total del periodo</p>
                <p class="metric-value">S/ {{ number_format($totalSalesAmount, 2) }}</p>
                <p class="metric-meta">Suma de ventas entre fechas.</p>
            </article>

            <article class="metric-card">
                <p class="metric-label">Cantidad de ventas</p>
                <p class="metric-value">{{ $totalSalesCount }}</p>
                <p class="metric-meta">Transacciones registradas.</p>
            </article>

            <article class="metric-card">
                <p class="metric-label">Ticket promedio</p>
                <p class="metric-value">S/ {{ number_format($averageTicket, 2) }}</p>
                <p class="metric-meta">Promedio por venta.</p>
            </article>

            <article class="metric-card">
                <p class="metric-label">Total de hoy</p>
                <p class="metric-value">S/ {{ number_format($todayTotal, 2) }}</p>
                <p class="metric-meta">Acumulado del dia actual.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Totales diarios</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Consolidado por fecha</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-shell">
                        <thead class="table-head">
                            <tr>
                                <th class="table-head-cell">Fecha</th>
                                <th class="table-head-cell">Nro ventas</th>
                                <th class="table-head-cell">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dailyTotals as $daily)
                                <tr class="table-row">
                                    <td class="table-cell">{{ \Carbon\Carbon::parse($daily->day)->format('d/m/Y') }}</td>
                                    <td class="table-cell">{{ $daily->sales_count }}</td>
                                    <td class="table-cell font-semibold text-slate-900">S/ {{ number_format((float) $daily->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="3" class="empty-state">No hay datos diarios para el periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Historial</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Ventas del periodo</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-shell">
                        <thead class="table-head">
                            <tr>
                                <th class="table-head-cell">Nro venta</th>
                                <th class="table-head-cell">Fecha</th>
                                <th class="table-head-cell">Caja</th>
                                <th class="table-head-cell">Total</th>
                                <th class="table-head-cell">Recibo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salesHistory as $sale)
                                <tr class="table-row">
                                    <td class="table-cell font-semibold text-slate-900">{{ $sale->sale_number }}</td>
                                    <td class="table-cell">{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                                    <td class="table-cell">{{ $sale->user?->name }}</td>
                                    <td class="table-cell font-semibold text-slate-900">S/ {{ number_format((float) $sale->total, 2) }}</td>
                                    <td class="table-cell">
                                        <a href="{{ route('sales.show', $sale) }}" class="action-link">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="5" class="empty-state">No hay ventas registradas en el periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    {{ $salesHistory->links() }}
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
