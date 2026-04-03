<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Inventario</p>
                <h2 class="page-title mt-2">Control de stock</h2>
                <p class="page-subtitle mt-3">Registra entradas, salidas por venta y ajustes manuales con historial completo por producto.</p>
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

        <section class="grid gap-4 xl:grid-cols-3">
            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('inventory.entries.store') }}" class="form-card space-y-4">
                    @csrf
                    <div>
                        <p class="section-kicker">Entrada</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Ingreso de stock</h3>
                    </div>

                    <div>
                        <label class="field-label">Producto</label>
                        <select name="product_id" class="field-input" required>
                            <option value="">Seleccione</option>
                            @foreach ($productOptions as $product)
                                <option value="{{ $product->id }}" @selected((int) old('product_id', $productId) === $product->id)>
                                    {{ $product->name }} ({{ $product->stock }} {{ $product->stockUnitLabel() }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="field-label">Cantidad</label>
                        <input type="number" step="0.001" min="0.001" name="quantity" value="{{ old('quantity') }}" class="field-input" required>
                    </div>

                    <div>
                        <label class="field-label">Referencia (opcional)</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="field-input" placeholder="Factura, guía, lote...">
                    </div>

                    <div>
                        <label class="field-label">Detalle (opcional)</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" class="field-input" placeholder="Compra a proveedor">
                    </div>

                    <button class="btn-primary w-full">Registrar entrada</button>
                </form>
            @endif

            <form method="POST" action="{{ route('inventory.sales.store') }}" class="form-card space-y-4">
                @csrf
                <div>
                    <p class="section-kicker">Salida</p>
                    <h3 class="mt-1 text-lg font-bold text-slate-900">Registrar venta</h3>
                </div>

                <div>
                    <label class="field-label">Producto</label>
                    <select name="product_id" class="field-input" required>
                        <option value="">Seleccione</option>
                        @foreach ($productOptions as $product)
                            <option value="{{ $product->id }}" @selected((int) old('product_id', $productId) === $product->id)>
                                {{ $product->name }} ({{ $product->stock }} {{ $product->stockUnitLabel() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="field-label">Cantidad vendida</label>
                    <input type="number" step="0.001" min="0.001" name="quantity" value="{{ old('quantity') }}" class="field-input" required>
                </div>

                <div>
                    <label class="field-label">N° operación (opcional)</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" class="field-input" placeholder="Boleta, ticket, pedido...">
                </div>

                <div>
                    <label class="field-label">Observación (opcional)</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="field-input" placeholder="Venta directa en caja">
                </div>

                <button class="btn-primary w-full">Registrar venta y descontar stock</button>
            </form>

            @if (auth()->user()->isAdmin())
                <form method="POST" action="{{ route('inventory.adjustments.store') }}" class="form-card space-y-4">
                    @csrf
                    <div>
                        <p class="section-kicker">Ajuste</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Merma o corrección</h3>
                    </div>

                    <div>
                        <label class="field-label">Producto</label>
                        <select name="product_id" class="field-input" required>
                            <option value="">Seleccione</option>
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

                    <div>
                        <label class="field-label">Cantidad</label>
                        <input type="number" step="0.001" min="0.001" name="quantity" value="{{ old('quantity') }}" class="field-input" required>
                    </div>

                    <div>
                        <label class="field-label">Motivo</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" class="field-input" placeholder="Merma por vencimiento" required>
                    </div>

                    <button class="btn-primary w-full">Aplicar ajuste manual</button>
                </form>
            @endif
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1.2fr)]">
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
                                    <td class="table-cell">{{ $product->category?->name }}</td>
                                    <td class="table-cell">S/ {{ number_format((float) $product->price, 2) }}</td>
                                    <td class="table-cell">
                                        <span class="{{ (float) $product->stock <= 0 ? 'badge-warning' : 'badge-success' }}">
                                            {{ $product->stock }} {{ $product->stockUnitLabel() }}
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
    </div>
</x-app-layout>
