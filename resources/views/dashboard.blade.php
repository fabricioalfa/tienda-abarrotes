{{-- filepath: /home/fabri/Documentos/tienda/resources/views/dashboard.blade.php --}}
<x-app-layout>
    @php
        $user = auth()->user();
        $hasProducts = \Illuminate\Support\Facades\Schema::hasTable('products');
        $hasCategories = \Illuminate\Support\Facades\Schema::hasTable('categories');
        $hasUsers = \Illuminate\Support\Facades\Schema::hasTable('users');
        $hasBatches = \Illuminate\Support\Facades\Schema::hasTable('product_batches');
        $hasCashRegisters = \Illuminate\Support\Facades\Schema::hasTable('cash_registers');

        $productCount = $hasProducts ? \App\Models\Product::count() : 0;
        $activeProductCount = $hasProducts ? \App\Models\Product::where('is_active', true)->count() : 0;
        $categoryCount = $hasCategories ? \App\Models\Category::count() : 0;
        $userCount = $hasUsers ? \App\Models\User::count() : 0;
        $expiringInFive = $hasBatches ? \App\Models\ProductBatch::where('remaining_quantity', '>', 0)->whereNotNull('expires_at')->whereDate('expires_at', '>=', now()->toDateString())->whereDate('expires_at', '<=', now()->addDays(5)->toDateString())->count() : 0;
        $expiringInTen = $hasBatches ? \App\Models\ProductBatch::where('remaining_quantity', '>', 0)->whereNotNull('expires_at')->whereDate('expires_at', '>', now()->addDays(5)->toDateString())->whereDate('expires_at', '<=', now()->addDays(10)->toDateString())->count() : 0;
        $currentRegister = $hasCashRegisters ? \App\Models\CashRegister::current() : null;
    @endphp

    <x-slot name="header">
        <div class="flex min-w-0 flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <p class="section-kicker">Vista general</p>
                <h2 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-[1.95rem]">Dashboard Ejecutivo</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Resumen operativo con foco en inventario, estado y accesos rápidos.</p>
            </div>

            <div class="w-full min-w-0 xl:max-w-md">
                <div class="dashboard-search">
                    <svg class="h-4 w-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                    </svg>
                    <input type="text" class="dashboard-search-input" value="Buscar módulo o vista" readonly>
                    <button class="btn-primary px-4 py-2">Buscar</button>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
            @if (session('status'))
                <div class="notice-success">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="notice-error">
                    {{ session('error') }}
                </div>
            @endif

            <section class="grid gap-5 2xl:grid-cols-[minmax(0,1.2fr)_320px]">
                <div class="space-y-5">
                    <div class="dashboard-rail">
                        <div class="dashboard-rail-track lg:grid lg:grid-cols-3 lg:gap-4 lg:overflow-visible">
                            <article class="dashboard-stat dashboard-rail-card">
                                <div class="dashboard-stat-head">
                                    <span>Productos</span>
                                    <span class="dashboard-mini-dot"></span>
                                </div>
                                <div class="dashboard-stat-body">
                                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Total</p>
                                    <p class="dashboard-value">{{ $productCount }}</p>
                                    <div class="dashboard-bar-track">
                                        <div class="dashboard-bar-fill" style="width: {{ max(12, min(100, $productCount * 8)) }}%"></div>
                                    </div>
                                </div>
                            </article>

                            <article class="dashboard-stat dashboard-rail-card">
                                <div class="dashboard-stat-head">
                                    <span>Activos</span>
                                    <span class="dashboard-mini-dot"></span>
                                </div>
                                <div class="dashboard-stat-body">
                                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Disponibles</p>
                                    <p class="dashboard-value">{{ $activeProductCount }}</p>
                                    <div class="dashboard-bar-track">
                                        <div class="dashboard-bar-fill" style="width: {{ $productCount > 0 ? round(($activeProductCount / max($productCount, 1)) * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </article>

                            <article class="dashboard-stat dashboard-rail-card">
                                <div class="dashboard-stat-head">
                                    <span>Alertas</span>
                                    <span class="dashboard-mini-dot"></span>
                                </div>
                                <div class="dashboard-stat-body">
                                    <p class="text-xs uppercase tracking-[0.22em] text-slate-400">Vencimientos</p>
                                    <p class="dashboard-value">{{ $expiringInFive + $expiringInTen }}</p>
                                    <div class="dashboard-bar-track">
                                        <div class="dashboard-bar-fill" style="width: {{ max(14, min(100, ($expiringInFive + $expiringInTen) * 18)) }}%"></div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div class="dashboard-panel overflow-hidden">
                        <div class="dashboard-panel-head">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100">Resumen operativo</p>
                                <h3 class="mt-1 text-lg font-bold">Tendencia</h3>
                            </div>
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]">{{ \App\Models\User::roles()[$user->role] ?? $user->role }}</span>
                        </div>

                        <div class="p-5">
                                <p class="text-sm text-slate-500">Bienvenido, {{ $user->name }}. Evolución referencial del rendimiento operativo del panel.</p>

                                <div class="mt-5 overflow-hidden rounded-[16px] border border-blue-100 bg-gradient-to-b from-blue-50 to-white p-4">
                                    <svg viewBox="0 0 520 220" class="h-52 w-full">
                                        <defs>
                                            <linearGradient id="areaFill" x1="0" x2="0" y1="0" y2="1">
                                                <stop offset="0%" stop-color="#60a5fa" stop-opacity="0.45" />
                                                <stop offset="100%" stop-color="#60a5fa" stop-opacity="0.04" />
                                            </linearGradient>
                                        </defs>
                                        <path d="M20 170 L85 138 L140 168 L195 88 L245 148 L300 104 L355 162 L410 92 L500 110" fill="none" stroke="#2563eb" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M20 170 L85 138 L140 168 L195 88 L245 148 L300 104 L355 162 L410 92 L500 110 L500 200 L20 200 Z" fill="url(#areaFill)" />
                                        <g fill="#1e3a8a">
                                            <circle cx="20" cy="170" r="6" />
                                            <circle cx="85" cy="138" r="6" />
                                            <circle cx="140" cy="168" r="6" />
                                            <circle cx="195" cy="88" r="6" />
                                            <circle cx="245" cy="148" r="6" />
                                            <circle cx="300" cy="104" r="6" />
                                            <circle cx="355" cy="162" r="6" />
                                            <circle cx="410" cy="92" r="6" />
                                            <circle cx="500" cy="110" r="6" />
                                        </g>
                                    </svg>
                                </div>

                                <div class="dashboard-rail mt-5">
                                    <div class="dashboard-action-strip">
                                        <a href="{{ route('products.index') }}" class="btn-primary whitespace-nowrap">Productos</a>
                                        <a href="{{ route('inventory.index') }}" class="btn-secondary whitespace-nowrap">Inventario</a>
                                        <a href="{{ route('cash-registers.index') }}" class="btn-secondary whitespace-nowrap">Caja</a>
                                        <a href="{{ route('sales.index') }}" class="btn-secondary whitespace-nowrap">Ventas</a>
                                        <a href="{{ route('reports.sales') }}" class="btn-secondary whitespace-nowrap">Reportes</a>
                                        @if ($user->isAdmin())
                                            <a href="{{ route('categories.index') }}" class="btn-secondary whitespace-nowrap">Categorías</a>
                                            <a href="{{ route('users.index') }}" class="btn-secondary whitespace-nowrap">Usuarios</a>
                                        @endif
                                        <a href="{{ route('profile.edit') }}" class="btn-secondary whitespace-nowrap">Perfil</a>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>

                <aside class="grid gap-6 md:grid-cols-2 2xl:grid-cols-1">
                    <div class="dashboard-panel overflow-hidden">
                        <div class="dashboard-panel-head">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100">Estado</p>
                                <h3 class="mt-1 text-lg font-bold">Cobertura</h3>
                            </div>
                            <span class="dashboard-mini-dot"></span>
                        </div>
                        <div class="p-5 text-center">
                            @php
                                $coverage = $productCount > 0 ? round(($activeProductCount / max($productCount, 1)) * 100) : 0;
                            @endphp
                            <div class="relative mx-auto h-36 w-36 dashboard-ring rounded-full">
                                <div class="dashboard-ring-center"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-3xl font-extrabold text-blue-800">{{ $coverage }}%</div>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-slate-500">Proporción de productos activos del inventario.</p>
                        </div>
                    </div>

                    <div class="dashboard-panel overflow-hidden">
                        <div class="dashboard-panel-head">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-100">Alertas operativas</p>
                                <h3 class="mt-1 text-lg font-bold">Qué revisar</h3>
                            </div>
                            <span class="dashboard-mini-dot"></span>
                        </div>
                        <div class="p-5">
                            <ul class="quick-list">
                                <li>Rojo: {{ $expiringInFive }} lotes vencen en 5 dias o menos.</li>
                                <li>Amarillo: {{ $expiringInTen }} lotes vencen entre 6 y 10 dias.</li>
                                <li>Caja: {{ $currentRegister ? 'abierta con S/ ' . number_format((float) $currentRegister->opening_amount, 2) : 'cerrada, abre antes de vender' }}.</li>
                                <li>Confirma productos, stock y costos antes de operar.</li>
                                @if ($user->isAdmin())
                                    <li>Revisa categorías, usuarios y vencimientos pendientes.</li>
                                @else
                                    <li>Utiliza productos y caja para una venta rapida.</li>
                                @endif
                                <li>Verifica reportes al cierre de jornada.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </section>
    </div>
</x-app-layout>