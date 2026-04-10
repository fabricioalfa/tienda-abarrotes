<x-app-layout>
    @php $user = auth()->user(); @endphp

    <x-slot name="header">
        <div class="flex min-w-0 flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <p class="section-kicker">Administrador</p>
                <h2 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-[1.95rem]">Panel de control</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Resumen operativo — {{ now()->format('d/m/Y') }}</p>
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

        {{-- KPIs principales --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="dashboard-stat">
                <div class="dashboard-stat-head">
                    <span>Ventas hoy</span>
                    <span class="dashboard-mini-dot"></span>
                </div>
                <div class="dashboard-stat-body">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Total Bs.</p>
                    <p class="dashboard-value">{{ number_format($todayTotal, 2) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $todaySales }} venta{{ $todaySales !== 1 ? 's' : '' }}</p>
                </div>
            </article>

            <article class="dashboard-stat">
                <div class="dashboard-stat-head">
                    <span>Productos</span>
                    <span class="dashboard-mini-dot"></span>
                </div>
                <div class="dashboard-stat-body">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Activos</p>
                    <p class="dashboard-value">{{ $activeProductCount }}</p>
                    <p class="mt-1 text-xs text-slate-400">de {{ $productCount }} en total</p>
                </div>
            </article>

            <article class="dashboard-stat {{ $expiringInFive > 0 ? 'border-red-200 bg-red-50' : '' }}">
                <div class="dashboard-stat-head">
                    <span>Vencen en 5 dias</span>
                    <span class="h-2 w-2 rounded-full {{ $expiringInFive > 0 ? 'bg-red-500' : 'bg-slate-300' }}"></span>
                </div>
                <div class="dashboard-stat-body">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Lotes criticos</p>
                    <p class="dashboard-value {{ $expiringInFive > 0 ? '!text-red-600' : '' }}">{{ $expiringInFive }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $expiringInFive > 0 ? 'Accion requerida' : 'Sin alertas' }}</p>
                </div>
            </article>

            <article class="dashboard-stat {{ $expiringInTen > 0 ? 'border-yellow-200 bg-yellow-50' : '' }}">
                <div class="dashboard-stat-head">
                    <span>Vencen en 10 dias</span>
                    <span class="h-2 w-2 rounded-full {{ $expiringInTen > 0 ? 'bg-yellow-400' : 'bg-slate-300' }}"></span>
                </div>
                <div class="dashboard-stat-body">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Lotes proximos</p>
                    <p class="dashboard-value {{ $expiringInTen > 0 ? '!text-yellow-600' : '' }}">{{ $expiringInTen }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $expiringInTen > 0 ? 'Revisar pronto' : 'Sin alertas' }}</p>
                </div>
            </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_320px]">
            {{-- Gráfico de ventas últimos 7 días --}}
            <div class="dashboard-panel overflow-hidden">
                <div class="dashboard-panel-head">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100">Ventas</p>
                        <h3 class="mt-1 text-lg font-bold">Ultimos 7 dias</h3>
                    </div>
                    <a href="{{ route('reports.sales') }}" class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white hover:bg-white/20">Ver reporte</a>
                </div>

                <div class="p-5">
                    @php
                        $maxTotal = $salesLast7->max('total') ?: 1;
                    @endphp
                    <div class="flex items-end gap-2 h-40">
                        @foreach ($salesLast7 as $day)
                            @php
                                $heightPct = max(4, round(($day['total'] / $maxTotal) * 100));
                            @endphp
                            <div class="flex flex-1 flex-col items-center gap-1">
                                <span class="text-[10px] font-semibold text-slate-500">
                                    {{ $day['total'] > 0 ? number_format($day['total'], 0) : '' }}
                                </span>
                                <div class="w-full rounded-t-md bg-blue-500 transition-all hover:bg-blue-600"
                                     style="height: {{ $heightPct }}%"
                                     title="Bs. {{ number_format($day['total'], 2) }} — {{ $day['count'] }} ventas">
                                </div>
                                <span class="text-[10px] text-slate-400">{{ $day['date'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Accesos rápidos --}}
                    <div class="dashboard-rail mt-5">
                        <div class="dashboard-action-strip">
                            <a href="{{ route('sales.create') }}" class="btn-primary whitespace-nowrap">Nueva venta</a>
                            <a href="{{ route('products.index') }}" class="btn-secondary whitespace-nowrap">Productos</a>
                            <a href="{{ route('inventory.index') }}" class="btn-secondary whitespace-nowrap">Inventario</a>
                            <a href="{{ route('cash-registers.index') }}" class="btn-secondary whitespace-nowrap">Caja</a>
                            <a href="{{ route('categories.index') }}" class="btn-secondary whitespace-nowrap">Categorias</a>
                            <a href="{{ route('users.index') }}" class="btn-secondary whitespace-nowrap">Usuarios</a>
                            <a href="{{ route('reports.sales') }}" class="btn-secondary whitespace-nowrap">Reportes</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel lateral --}}
            <aside class="space-y-4">
                {{-- Estado de caja --}}
                <div class="dashboard-panel overflow-hidden">
                    <div class="dashboard-panel-head">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100">Estado</p>
                            <h3 class="mt-1 text-lg font-bold">Caja</h3>
                        </div>
                        <span class="h-2.5 w-2.5 rounded-full {{ $currentRegister ? 'bg-green-400' : 'bg-red-400' }}"></span>
                    </div>
                    <div class="p-5">
                        @if ($currentRegister)
                            <p class="text-sm font-semibold text-green-700">Caja abierta</p>
                            <p class="mt-1 text-xs text-slate-500">Fondo inicial: Bs. {{ number_format((float) $currentRegister->opening_amount, 2) }}</p>
                            <p class="mt-1 text-xs text-slate-500">Desde: {{ $currentRegister->opened_at?->format('H:i') }}</p>
                        @else
                            <p class="text-sm font-semibold text-red-600">Caja cerrada</p>
                            <p class="mt-1 text-xs text-slate-500">Abre la caja antes de vender.</p>
                            <a href="{{ route('cash-registers.index') }}" class="mt-3 inline-block btn-primary text-xs">Abrir caja</a>
                        @endif
                    </div>
                </div>

                {{-- Alertas --}}
                <div class="dashboard-panel overflow-hidden">
                    <div class="dashboard-panel-head">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100">Alertas</p>
                            <h3 class="mt-1 text-lg font-bold">Vencimientos</h3>
                        </div>
                    </div>
                    <div class="p-5">
                        <ul class="quick-list">
                            @if ($expiringInFive > 0)
                                <li class="text-red-600 font-semibold">Rojo: {{ $expiringInFive }} lote{{ $expiringInFive !== 1 ? 's' : '' }} vencen en 5 dias o menos.</li>
                            @endif
                            @if ($expiringInTen > 0)
                                <li class="text-yellow-600 font-semibold">Amarillo: {{ $expiringInTen }} lote{{ $expiringInTen !== 1 ? 's' : '' }} vencen entre 6 y 10 dias.</li>
                            @endif
                            @if ($expiringInFive === 0 && $expiringInTen === 0)
                                <li class="text-green-600">Sin alertas de vencimiento activas.</li>
                            @endif
                            <li>Revisa reportes al cierre de jornada.</li>
                        </ul>
                        @if ($expiringInFive + $expiringInTen > 0)
                            <a href="{{ route('reports.sales') }}" class="mt-3 inline-block text-xs action-link">Ver lotes por vencer</a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
