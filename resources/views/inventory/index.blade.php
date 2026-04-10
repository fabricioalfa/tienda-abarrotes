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

        @if (auth()->user()->isAdmin())
        <section class="grid gap-6 md:grid-cols-2">

            {{-- ENTRADA DE STOCK --}}
            <form method="POST" action="{{ route('inventory.entries.store') }}" class="form-card flex flex-col gap-4">
                @csrf

                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <p class="section-kicker">Entrada</p>
                        <h3 class="text-base font-bold text-slate-900">Ingreso de stock</h3>
                    </div>
                </div>

                <div>
                    <label class="field-label">Producto</label>
                    <select name="product_id" class="field-input" required>
                        <option value="">Seleccione un producto</option>
                        @foreach ($productOptions as $product)
                            <option value="{{ $product->id }}" @selected((int) old('product_id', $productId) === $product->id)>
                                {{ $product->name }} ({{ $product->stock }} {{ $product->stockUnitLabel() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Cantidad</label>
                        <input type="number" step="0.001" min="0.001" name="quantity" value="{{ old('quantity') }}" class="field-input" required placeholder="0.000">
                    </div>
                    <div>
                        <label class="field-label">Costo unitario (Bs.)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost') }}" class="field-input" placeholder="0.00">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Fecha de vencimiento</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Proveedor</label>
                        <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" class="field-input" placeholder="Nombre del proveedor">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Referencia (opcional)</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="field-input" placeholder="Factura, guía, lote...">
                    </div>
                    <div>
                        <label class="field-label">Detalle (opcional)</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" class="field-input" placeholder="Compra a proveedor">
                    </div>
                </div>

                <div class="mt-auto pt-2">
                    <button class="btn-primary w-full">Registrar entrada</button>
                </div>
            </form>

            {{-- AJUSTE / MERMA --}}
            <form method="POST" action="{{ route('inventory.adjustments.store') }}" class="form-card flex flex-col gap-4">
                @csrf

                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="section-kicker">Ajuste</p>
                        <h3 class="text-base font-bold text-slate-900">Merma o corrección</h3>
                    </div>
                </div>

                <div>
                    <label class="field-label">Producto</label>
                    <select name="product_id" class="field-input" required>
                        <option value="">Seleccione un producto</option>
                        @foreach ($productOptions as $product)
                            <option value="{{ $product->id }}" @selected((int) old('product_id', $productId) === $product->id)>
                                {{ $product->name }} ({{ $product->stock }} {{ $product->stockUnitLabel() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label">Tipo de ajuste</label>
                    <select name="direction" class="field-input" required>
                        <option value="out" @selected(old('direction') === 'out')>Descontar stock (merma, pérdida)</option>
                        <option value="in" @selected(old('direction') === 'in')>Aumentar stock (corrección)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Cantidad</label>
                        <input type="number" step="0.001" min="0.001" name="quantity" value="{{ old('quantity') }}" class="field-input" required placeholder="0.000">
                    </div>
                    <div>
                        <label class="field-label">Costo unitario (si aumenta)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost') }}" class="field-input" placeholder="0.00">
                    </div>
                </div>

                <div>
                    <label class="field-label">Vencimiento (si aumenta stock)</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="field-input">
                </div>

                <div>
                    <label class="field-label">Motivo <span class="text-red-500">*</span></label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="field-input" placeholder="Ej: Merma por vencimiento, rotura..." required>
                </div>

                <div class="mt-auto pt-2">
                    <button class="btn-primary w-full">Aplicar ajuste manual</button>
                </div>
            </form>

        </section>
        @endif

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
                                <th class="table-head-cell">Saldo</th>
                                <th class="table-head-cell">Proveedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expiringBatches as $batch)
                                <tr class="table-row">
                                    <td class="table-cell font-semibold text-slate-900">{{ $batch->product?->name }}</td>
                                    <td class="table-cell">{{ $batch->expires_at?->format('d/m/Y') }}</td>
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
