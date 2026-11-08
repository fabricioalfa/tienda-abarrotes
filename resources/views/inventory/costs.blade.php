<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Inventario</p>
                <h2 class="page-title mt-2">Historial de costos</h2>
                <p class="page-subtitle mt-3">Historial de costos unitarios de productos con filtros.</p>
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

        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-4">
            <div>
                <label class="field-label">Producto</label>
                <select name="product_id" class="field-input">
                    <option value="">Todos los productos</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) $productId === (string) $product->id)>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">Categoría</label>
                <select name="category_id" class="field-input">
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">Proveedor</label>
                <select name="supplier_id" class="field-input">
                    <option value="">Todos los proveedores</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) $supplierId === (string) $supplier->id)>
                            {{ $supplier->name }}{{ $supplier->phone ? ' (' . $supplier->phone . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label">Desde</label>
                <input type="date" name="from" value="{{ $from }}" class="field-input">
            </div>
            <div>
                <label class="field-label">Hasta</label>
                <input type="date" name="to" value="{{ $to }}" class="field-input">
            </div>
            <button class="btn-secondary">Aplicar filtros</button>
        </form>

        <div class="overflow-x-auto">
            <table class="table-shell">
                <thead class="table-head">
                    <tr>
                        <th class="table-head-cell">Producto</th>
                        <th class="table-head-cell">Fecha</th>
                        <th class="table-head-cell">Categoría</th>
                        <th class="table-head-cell">Proveedor</th>
                        <th class="table-head-cell">Costo unitario</th>
                        <th class="table-head-cell">Cantidad</th>
                        <th class="table-head-cell">Total</th>
                        <th class="table-head-cell">Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($costs as $cost)
                        <tr class="table-row">
                            <td class="table-cell font-semibold text-slate-900">{{ $cost->product?->name }}</td>
                            <td class="table-cell">{{ $cost->created_at->format('d/m/Y') }}</td>
                            <td class="table-cell">{{ $cost->product?->getRelation('category')?->name ?? $cost->product->category ?? '-' }}</td>
                            <td class="table-cell">{{ $cost->supplier?->name ?: '-' }}</td>
                            <td class="table-cell">Bs. {{ number_format((float) $cost->unit_cost, 2) }}</td>
                            <td class="table-cell">{{ $cost->quantity }}</td>
                            <td class="table-cell">Bs. {{ number_format((float) $cost->unit_cost * $cost->quantity, 2) }}</td>
                            <td class="table-cell">{{ $cost->reference ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="8" class="empty-state">No hay historial de costos con los filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            {{ $costs->links() }}
        </div>
    </div>
</x-app-layout>