<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Inventario</p>
                <h2 class="page-title mt-2">Control de stock</h2>
                <p class="page-subtitle mt-3">Registra entradas y ajustes manuales con historial completo por producto.</p>
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

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="metric-card">
                <p class="metric-label">Productos</p>
                <p class="metric-value">{{ $totalProducts }}</p>
                <p class="metric-meta">Ítems en el catálogo.</p>
            </article>

            <article class="metric-card">
                <p class="metric-label">Sin stock</p>
                <p class="metric-value">{{ $outOfStock }}</p>
                <p class="metric-meta">Requieren reposición inmediata.</p>
            </article>

            <article class="metric-card">
                <p class="metric-label">Stock bajo</p>
                <p class="metric-value">{{ $lowStock }}</p>
                <p class="metric-meta">Con existencia de 1 a 5.</p>
            </article>

            <article class="metric-card">
                <p class="metric-label">Movimientos hoy</p>
                <p class="metric-value">{{ $todayMovements }}</p>
                <p class="metric-meta">Entradas, ventas y ajustes.</p>
            </article>
        </section>

        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('inventory.entries') }}" class="btn-primary flex-1 sm:auto text-center">➕ Entrada de stock</a>
            <a href="{{ route('inventory.adjustments') }}" class="btn-secondary flex-1 sm:auto text-center">⚖️ Ajuste / Merma</a>
            <a href="{{ route('inventory.movements') }}" class="btn-secondary flex-1 sm:auto text-center">📋 Historial de movimientos</a>
        </div>

        <section class="grid gap-6 md:grid-cols-2">
            <div class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Stock disponible</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Productos</h3>
                    </div>

                    <form method="GET" class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <input type="text" name="q" value="{{ $q }}" class="field-input sm:flex-1 sm:min-w-[180px]" placeholder="Nombre o código">
                        <select name="category_id" class="field-input sm:flex-1 sm:min-w-[170px]">
                            <option value="">Todas las categorías</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn-secondary">Filtrar</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-shell">
                        <thead class="table-head">
                            <tr>
                                <th class="table-head-cell">Producto</th>
                                <th class="table-head-cell">Categoría</th>
                                <th class="table-head-cell">Precio</th>
                                <th class="table-head-cell">Stock</th>
                                <th class="table-head-cell">Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr class="table-row">
                                    <td class="table-cell font-semibold text-slate-900">{{ $product->name }}</td>
                                    <td class="table-cell">{{ $product->getRelation('category')?->name ?? $product->category ?? '-' }}</td>
                                    <td class="table-cell">Bs. {{ number_format((float) $product->price, 2) }}</td>
                                    <td class="table-cell">
                                        <span class="{{ (float) $product->stock <= 0 ? 'badge-warning' : 'badge-success' }}">
                                            {{ $product->stockBreakdownLabel() }}
                                        </span>
                                    </td>
                                    <td class="table-cell">{{ $product->saleTypeLabel() }}</td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="5" class="empty-state">No hay productos para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    {{ $products->links() }}
                </div>
            </div>

            <div class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Historial</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Movimientos</h3>
                    </div>

                    <form method="GET" class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <select name="product_id" class="field-input sm:flex-1 sm:min-w-[180px]">
                            <option value="">Todos los productos</option>
                            @foreach ($productOptions as $product)
                                <option value="{{ $product->id }}" @selected((string) $productId === (string) $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <select name="movement_type" class="field-input sm:flex-1 sm:min-w-[170px]">
                            <option value="">Todos los movimientos</option>
                            <option value="entry" @selected($movementType === 'entry')>Entradas</option>
                            <option value="sale" @selected($movementType === 'sale')>Salidas por venta</option>
                            <option value="adjustment" @selected($movementType === 'adjustment')>Ajustes</option>
                        </select>
                        <button class="btn-secondary">Filtrar</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-shell">
                        <thead class="table-head">
                            <tr>
                                <th class="table-head-cell">Fecha</th>
                                <th class="table-head-cell">Producto</th>
                                <th class="table-head-cell">Movimiento</th>
                                <th class="table-head-cell">Cantidad</th>
                                <th class="table-head-cell">Stock final</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                <tr class="table-row">
                                    <td class="table-cell text-slate-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="table-cell font-semibold text-slate-900">{{ $movement->product?->name }}</td>
                                    <td class="table-cell">
                                        <p>{{ $movement->typeLabel() }}</p>
                                        <p class="text-xs text-slate-500">{{ $movement->directionLabel() }}</p>
                                        @if ($movement->unit_cost)
                                            <p class="mt-1 text-xs text-slate-500">Costo: Bs. {{ number_format((float) $movement->unit_cost, 2) }}</p>
                                        @endif
                                        @if ($movement->expires_at)
                                            <p class="text-xs text-slate-500">Vence: {{ $movement->expires_at->format('d/m/Y') }}</p>
                                        @endif
                                        @if ($movement->reason)
                                            <p class="mt-1 text-xs text-slate-500">{{ $movement->reason }}</p>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        <span class="{{ $movement->direction === 'in' ? 'badge-success' : 'badge-warning' }}">
                                            {{ $movement->direction === 'in' ? '+' : '-' }}{{ $movement->quantity }}
                                        </span>
                                    </td>
                                    <td class="table-cell font-semibold text-slate-900">{{ $movement->stock_after }}</td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="5" class="empty-state">Aun no hay movimientos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    {{ $movements->links() }}
                </div>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-2">
            <div class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Vencimientos</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Productos proximos a vencer</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-shell">
                        <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Producto</th>
                            <th class="table-head-cell">Vence</th>
                            <th class="table-head-cell">Días</th>
                            <th class="table-head-cell">Saldo</th>
                            <th class="table-head-cell">Proveedor</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse ($expiringBatches as $batch)
                                <tr class="table-row">
                                    <td class="table-cell font-semibold text-slate-900">{{ $batch->product?->name }}</td>
                                    <td class="table-cell">{{ $batch->expires_at?->format('d/m/Y') }}</td>
                                    <td class="table-cell">{{ max(0, (int) $batch->expires_at?->diffInDays(now())) }}</td>
                                    <td class="table-cell">{{ $batch->remaining_quantity }}</td>
                                    <td class="table-cell">{{ $batch->supplier_name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="4" class="empty-state">No hay lotes por vencer en los próximos 10 días.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Costos</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Historial reciente</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-shell">
                        <thead class="table-head">
                            <tr>
                                <th class="table-head-cell">Producto</th>
                                <th class="table-head-cell">Fecha</th>
                                <th class="table-head-cell">Costo</th>
                                <th class="table-head-cell">Referencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($costHistory as $batch)
                                <tr class="table-row">
                                    <td class="table-cell font-semibold text-slate-900">{{ $batch->product?->name }}</td>
                                    <td class="table-cell">{{ $batch->created_at->format('d/m/Y') }}</td>
                                    <td class="table-cell">Bs. {{ number_format((float) $batch->unit_cost, 2) }}</td>
                                    <td class="table-cell">{{ $batch->reference ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="4" class="empty-state">Todavia no hay historial de costos registrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
