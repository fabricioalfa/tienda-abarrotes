{{-- filepath: /home/fabri/Documentos/tienda/resources/views/products/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Inventario</p>
                <h2 class="page-title mt-2">Productos</h2>
                <p class="page-subtitle mt-3">Consulta el catálogo operativo, filtra por nombre o código y gestiona disponibilidad con una lectura más ordenada.</p>
            </div>

            @if (auth()->user()->isAdmin())
                <a href="{{ route('products.create') }}" class="btn-primary">Nuevo producto</a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
            @if (session('status'))
                <div class="notice-success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="notice-error">{{ session('error') }}</div>
            @endif

            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_300px]">
                <form method="GET" action="{{ route('products.index') }}" class="shell-panel-strong p-4">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o código de barras" class="field-input w-full">
                        <button class="btn-primary sm:min-w-[140px]">Buscar</button>
                    </div>
                </form>

                <div class="metric-card">
                    <p class="metric-label">Resultados</p>
                    <p class="metric-value">{{ $products->total() }}</p>
                    <p class="metric-meta">Productos que coinciden con la vista actual.</p>
                </div>
            </div>

            <div class="table-card">
                <div class="overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Producto</th>
                            <th class="table-head-cell">Categoría</th>
                            <th class="table-head-cell">Tipo</th>
                            <th class="table-head-cell">Marca / proveedor</th>
                            <th class="table-head-cell">Código de barras</th>
                            <th class="table-head-cell">Precio</th>
                            <th class="table-head-cell">Stock</th>
                            <th class="table-head-cell">Estado</th>
                            @if (auth()->user()->isAdmin())
                                <th class="table-head-cell">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="table-row">
                                <td class="table-cell font-semibold text-slate-900">{{ $product->name }}</td>
                                <td class="table-cell">{{ $product->category?->name }}</td>
                                <td class="table-cell">
                                    {{ $product->saleTypeLabel() }}
                                    @if ($product->weight_unit)
                                        ({{ $product->weightUnitLabel() }})
                                    @endif
                                    @if ($product->supportsPackageSale())
                                        <p class="mt-1 text-xs text-slate-500">{{ $product->package_name }}: {{ $product->units_per_package }} unid | S/ {{ number_format((float) $product->package_price, 2) }}</p>
                                    @endif
                                </td>
                                <td class="table-cell text-slate-500">
                                    <p>{{ $product->brand ?: '-' }}</p>
                                    <p class="mt-1 text-xs">{{ $product->supplier_name ?: 'Sin proveedor' }}</p>
                                </td>
                                <td class="table-cell text-slate-500">{{ $product->barcode ?: '-' }}</td>
                                <td class="table-cell font-semibold text-slate-900">S/ {{ number_format((float) $product->price, 2) }}</td>
                                <td class="table-cell">
                                    <p>{{ $product->stockBreakdownLabel() }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Minimo: {{ rtrim(rtrim(number_format((float) $product->minimum_stock, 3, '.', ''), '0'), '.') ?: '0' }}</p>
                                </td>
                                <td class="table-cell">
                                    <span class="{{ $product->is_active ? ($product->isLowStock() ? 'badge-warning' : 'badge-success') : 'badge-warning' }}">
                                        {{ $product->is_active ? ($product->isLowStock() ? 'Stock bajo' : 'Activo') : 'Inactivo' }}
                                    </span>
                                    @if ($product->track_expiration)
                                        <p class="mt-1 text-xs text-slate-500">Controla vencimiento</p>
                                    @endif
                                </td>

                                @if (auth()->user()->isAdmin())
                                    <td class="table-cell">
                                        <div class="flex flex-wrap gap-4">
                                        <a href="{{ route('products.edit', $product) }}" class="action-link">Editar</a>
                                        <a href="{{ route('inventory.index', ['product_id' => $product->id]) }}" class="action-link">Movimientos</a>

                                        <form method="POST" action="{{ route('products.destroy', $product) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-link-danger" onclick="return confirm('¿Eliminar producto?')">Eliminar</button>
                                        </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="empty-state">
                                    No hay productos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                <div class="table-footer">
                    {{ $products->links() }}
                </div>
            </div>
    </div>
</x-app-layout>