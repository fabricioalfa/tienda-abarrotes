<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Caja</p>
                <h2 class="page-title mt-2">Apertura y cierre</h2>
                <p class="page-subtitle mt-3">Controla el efectivo inicial, el total esperado y la diferencia al cierre de jornada.</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="notice-success">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="notice-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="notice-error">
                <p class="font-semibold">Hay datos por corregir:</p>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="form-card space-y-5">
                <div>
                    <p class="section-kicker">Estado actual</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-900">Caja {{ $currentRegister ? 'abierta' : 'cerrada' }}</h3>
                </div>

                @if ($currentRegister)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="metric-card !shadow-none">
                            <p class="metric-label">Apertura</p>
                            <p class="metric-value">S/ {{ number_format((float) $currentRegister->opening_amount, 2) }}</p>
                            <p class="metric-meta">{{ $currentRegister->opened_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="metric-card !shadow-none">
                            <p class="metric-label">Efectivo esperado</p>
                            <p class="metric-value">S/ {{ number_format($currentRegister->expectedCash(), 2) }}</p>
                            <p class="metric-meta">Incluye apertura y ventas en efectivo.</p>
                        </div>

                        <div class="metric-card !shadow-none">
                            <p class="metric-label">Ventas en efectivo</p>
                            <p class="metric-value">S/ {{ number_format((float) $currentRegister->cash_sales_total, 2) }}</p>
                            <p class="metric-meta">Cobros directos en caja.</p>
                        </div>

                        <div class="metric-card !shadow-none">
                            <p class="metric-label">Ventas QR y crédito</p>
                            <p class="metric-value">S/ {{ number_format((float) $currentRegister->qr_sales_total + (float) $currentRegister->credit_sales_total, 2) }}</p>
                            <p class="metric-meta">Cobros fuera del efectivo de caja.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('cash-registers.close', $currentRegister) }}" class="space-y-4 border-t border-blue-100 pt-5">
                        @csrf
                        <div>
                            <label class="field-label">Efectivo contado al cierre</label>
                            <input type="number" step="0.01" min="0" name="counted_cash" class="field-input" required>
                        </div>

                        <div>
                            <label class="field-label">Nota de cierre</label>
                            <textarea name="notes" rows="3" class="field-input" placeholder="Observacion del cierre"></textarea>
                        </div>

                        <button class="btn-primary">Cerrar caja</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('cash-registers.open') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="field-label">Monto inicial para cambio</label>
                            <input type="number" step="0.01" min="0" name="opening_amount" class="field-input" required>
                        </div>

                        <div>
                            <label class="field-label">Nota de apertura</label>
                            <textarea name="notes" rows="3" class="field-input" placeholder="Ej. turno mañana"></textarea>
                        </div>

                        <button class="btn-primary">Abrir caja</button>
                    </form>
                @endif
            </article>

            <article class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Historial</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Jornadas registradas</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-shell">
                        <thead class="table-head">
                            <tr>
                                <th class="table-head-cell">Fecha</th>
                                <th class="table-head-cell">Responsable</th>
                                <th class="table-head-cell">Apertura</th>
                                <th class="table-head-cell">Esperado</th>
                                <th class="table-head-cell">Contado</th>
                                <th class="table-head-cell">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($history as $register)
                                <tr class="table-row">
                                    <td class="table-cell">{{ $register->opened_at->format('d/m/Y H:i') }}</td>
                                    <td class="table-cell">{{ $register->opener?->name }}</td>
                                    <td class="table-cell">S/ {{ number_format((float) $register->opening_amount, 2) }}</td>
                                    <td class="table-cell">S/ {{ number_format($register->expectedCash(), 2) }}</td>
                                    <td class="table-cell">{{ $register->counted_cash !== null ? 'S/ ' . number_format((float) $register->counted_cash, 2) : '-' }}</td>
                                    <td class="table-cell">
                                        <span class="{{ $register->status === 'open' ? 'badge-success' : 'badge-warning' }}">
                                            {{ $register->status === 'open' ? 'Abierta' : 'Cerrada' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="6" class="empty-state">Aun no hay aperturas o cierres de caja.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    {{ $history->links() }}
                </div>
            </article>
        </section>
    </div>
</x-app-layout>