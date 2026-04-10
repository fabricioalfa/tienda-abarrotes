<x-app-layout>
    @php $user = auth()->user(); @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="section-kicker">Caja / Ventas</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Bienvenida, {{ $user->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold
                    {{ $currentRegister ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    <span class="h-2 w-2 rounded-full {{ $currentRegister ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    {{ $currentRegister ? 'Caja abierta' : 'Caja cerrada' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">
        @if (session('status'))
            <div class="notice-success">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="notice-error">{{ session('error') }}</div>
        @endif

        {{-- Alerta si la caja está cerrada --}}
        @if (! $currentRegister)
            <div class="notice-error flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <div>
                    <p class="font-semibold text-red-800">La caja esta cerrada</p>
                    <p class="mt-0.5 text-sm text-red-700">Debes abrir la caja antes de registrar ventas.</p>
                    <a href="{{ route('cash-registers.index') }}" class="mt-2 inline-block text-sm font-semibold text-red-800 underline hover:text-red-900">Ir a apertura de caja</a>
                </div>
            </div>
        @endif

        {{-- Alerta de vencimientos críticos --}}
        @if ($expiringInFive > 0)
            <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
                <span class="mt-0.5 h-3 w-3 shrink-0 rounded-full bg-red-500"></span>
                <p class="text-sm text-red-700">
                    <span class="font-semibold">Atencion:</span>
                    {{ $expiringInFive }} lote{{ $expiringInFive !== 1 ? 's' : '' }} vence{{ $expiringInFive === 1 ? '' : 'n' }} en 5 dias o menos. Consulta al administrador.
                </p>
            </div>
        @endif

        {{-- Botón principal: NUEVA VENTA --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('sales.create') }}"
               class="group relative overflow-hidden rounded-2xl
                      {{ $currentRegister ? 'bg-blue-600 hover:bg-blue-700' : 'bg-slate-300 cursor-not-allowed' }}
                      p-6 text-white shadow-lg transition-all duration-150 active:scale-[0.98] sm:col-span-2 lg:col-span-2"
               @if(! $currentRegister) onclick="return false;" @endif>
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/20">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386a1.5 1.5 0 0 1 1.455 1.136L5.61 6H20.25a.75.75 0 0 1 .73.92l-1.5 6A.75.75 0 0 1 18.75 13.5H7.5a.75.75 0 0 1-.73-.57L4.152 4.5H2.25M7.5 18.75A1.125 1.125 0 1 1 5.25 18.75a1.125 1.125 0 0 1 2.25 0Zm12 0a1.125 1.125 0 1 1-2.25 0 1.125 1.125 0 0 1 2.25 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-white/70">Accion principal</p>
                        <p class="mt-1 text-2xl font-extrabold">Nueva venta</p>
                        <p class="mt-0.5 text-sm text-white/80">
                            {{ $currentRegister ? 'Registrar productos y cobrar al cliente' : 'Abre la caja primero para poder vender' }}
                        </p>
                    </div>
                </div>
            </a>

            <a href="{{ route('cash-registers.index') }}"
               class="group rounded-2xl bg-white border border-slate-200 p-6 shadow-sm hover:border-blue-200 hover:shadow-md transition-all duration-150 active:scale-[0.98]">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 group-hover:bg-blue-50">
                        <svg class="h-6 w-6 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25v7.5A2.25 2.25 0 0 1 18.75 18H5.25A2.25 2.25 0 0 1 3 15.75v-7.5A2.25 2.25 0 0 1 5.25 6h13.5A2.25 2.25 0 0 1 21 8.25ZM7.5 12h9" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800">Caja</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $currentRegister ? 'Cerrar turno' : 'Abrir turno' }}
                        </p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Accesos secundarios --}}
        <div class="grid gap-3 sm:grid-cols-3">
            <a href="{{ route('sales.index') }}"
               class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 hover:border-blue-200 hover:shadow-sm transition-all">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Historial ventas</p>
                    <p class="text-xs text-slate-500">Ver todas las ventas</p>
                </div>
            </a>

            <a href="{{ route('inventory.index') }}"
               class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 hover:border-blue-200 hover:shadow-sm transition-all">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50">
                    <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5H3.75A1.5 1.5 0 0 0 2.25 9v9.75a1.5 1.5 0 0 0 1.5 1.5h16.5a1.5 1.5 0 0 0 1.5-1.5V9a1.5 1.5 0 0 0-1.5-1.5ZM6 7.5V6a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3v1.5M9.75 12h4.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Inventario</p>
                    <p class="text-xs text-slate-500">Ver stock actual</p>
                </div>
            </a>

            <a href="{{ route('reports.sales') }}"
               class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 hover:border-blue-200 hover:shadow-sm transition-all">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50">
                    <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.5h3.75v7.5H3v-7.5Zm7.125-6h3.75V21h-3.75V7.5Zm7.125-4.5H21V21h-3.75V3Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">Reportes</p>
                    <p class="text-xs text-slate-500">Ventas del dia</p>
                </div>
            </a>
        </div>

        {{-- Resumen del día + Últimas ventas --}}
        <div class="grid gap-4 lg:grid-cols-[1fr_340px]">
            {{-- Últimas ventas del día --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h3 class="font-bold text-slate-800">Ultimas ventas de hoy</h3>
                    <a href="{{ route('sales.index') }}" class="text-xs text-blue-600 hover:underline">Ver todas</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse ($lastSales as $sale)
                        <a href="{{ route('sales.show', $sale) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 transition-colors">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $sale->sale_number }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $sale->sold_at->format('H:i') }}
                                    — {{ $sale->items_count ?? $sale->items->count() }} item{{ ($sale->items_count ?? $sale->items->count()) !== 1 ? 's' : '' }}
                                    — {{ ucfirst($sale->payment_method) }}
                                </p>
                            </div>
                            <span class="text-sm font-bold text-slate-900">Bs. {{ number_format((float) $sale->total, 2) }}</span>
                        </a>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <svg class="h-10 w-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386a1.5 1.5 0 0 1 1.455 1.136L5.61 6H20.25a.75.75 0 0 1 .73.92l-1.5 6A.75.75 0 0 1 18.75 13.5H7.5a.75.75 0 0 1-.73-.57L4.152 4.5H2.25" />
                            </svg>
                            <p class="mt-3 text-sm font-semibold text-slate-400">Sin ventas hoy</p>
                            <p class="mt-1 text-xs text-slate-300">Las ventas del dia apareceran aqui.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Resumen numérico del día --}}
            <div class="space-y-3">
                <div class="rounded-2xl bg-blue-600 p-5 text-white shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-100">Total del dia</p>
                    <p class="mt-2 text-4xl font-extrabold">Bs. {{ number_format($todayTotal, 2) }}</p>
                    <p class="mt-1 text-sm text-blue-100">{{ $todaySales }} venta{{ $todaySales !== 1 ? 's' : '' }} registrada{{ $todaySales !== 1 ? 's' : '' }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Estado de caja</p>
                    @if ($currentRegister)
                        <p class="mt-2 text-lg font-bold text-green-700">Abierta</p>
                        <p class="mt-1 text-sm text-slate-500">Fondo: Bs. {{ number_format((float) $currentRegister->opening_amount, 2) }}</p>
                        <p class="text-sm text-slate-500">Desde las {{ $currentRegister->opened_at?->format('H:i') }}</p>
                        <a href="{{ route('cash-registers.index') }}" class="mt-3 inline-block text-xs font-semibold text-blue-600 hover:underline">Cerrar caja</a>
                    @else
                        <p class="mt-2 text-lg font-bold text-red-600">Cerrada</p>
                        <a href="{{ route('cash-registers.index') }}" class="mt-3 inline-block btn-primary text-xs">Abrir caja ahora</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal stock bajo --}}
    @if ($lowStockProducts->isNotEmpty())
    <div id="low-stock-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeLowStockModal()"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl">
            {{-- Header --}}
            <div class="flex items-center gap-3 rounded-t-2xl bg-amber-50 px-5 py-4 border-b border-amber-100">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-semibold uppercase tracking-widest text-amber-600">Aviso de inventario</p>
                    <h3 class="text-sm font-bold text-slate-900">
                        {{ $lowStockProducts->count() }} producto{{ $lowStockProducts->count() !== 1 ? 's' : '' }} con stock bajo
                    </h3>
                </div>
                <button onclick="closeLowStockModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Lista de productos --}}
            <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                @foreach ($lowStockProducts as $p)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $p->name }}</p>
                            <p class="text-xs text-slate-400">Mínimo: {{ rtrim(rtrim(number_format((float)$p->minimum_stock, 3), '0'), '.') }} {{ $p->sale_type === 'weight' ? ($p->weight_unit ?? 'kg') : 'unid' }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold
                            {{ (float)$p->stock <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ rtrim(rtrim(number_format((float)$p->stock, 3), '0'), '.') }}
                            {{ $p->sale_type === 'weight' ? ($p->weight_unit ?? 'kg') : 'unid' }}
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="rounded-b-2xl border-t border-slate-100 bg-slate-50 px-5 py-4">
                <p class="text-xs text-slate-500 mb-3">Informa al administrador para reponer stock.</p>
                <button onclick="closeLowStockModal()" class="btn-primary w-full">Entendido</button>
            </div>
        </div>
    </div>

    <script>
        function closeLowStockModal() {
            document.getElementById('low-stock-modal').remove();
        }
    </script>
    @endif

</x-app-layout>
