{{-- filepath: /home/fabri/Documentos/tienda-abarrotes/resources/views/inventory/movements.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Inventario</p>
                <h2 class="page-title mt-2">Historial de movimientos</h2>
                <p class="page-subtitle mt-3">Entradas, salidas por venta y ajustes de stock.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('inventory.index') }}" class="btn-secondary">← Volver al resumen</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
@if (session('status'))
    <div class="notice-success">{{ session('status') }}</div>
@endif

@include('components.inventory-tabs')

        <form method="GET" class="form-card flex flex-col gap-3 sm:flex-row sm:items-end">
            <select name="product_id" class="field-input sm:flex-1 sm:min-w-[200px]">
                <option value="">Todos los productos</option>
                @foreach ($productOptions as $product)
                    <option value="{{ $product->id }}" @selected((string) $productId === (string) $product->id)>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
            <select name="movement_type" class="field-input sm:flex-1 sm:min-w-[180px]">
                <option value="">Todos los movimientos</option>
                <option value="entry" @selected($movementType === 'entry')>Entradas</option>
                <option value="sale" @selected($movementType === 'sale')>Salidas por venta</option>
                <option value="adjustment" @selected($movementType === 'adjustment')>Ajustes</option>
            </select>
            <button class="btn-secondary">Filtrar</button>
            <a href="{{ route('inventory.movements') }}" class="btn-secondary">Limpiar</a>
        </form>

        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Producto</th>
                            <th class="table-head-cell">Fecha</th>
                            <th class="table-head-cell">Movimiento</th>
                            <th class="table-head-cell">Proveedor</th>
                            <th class="table-head-cell">Dirección</th>
                            <th class="table-head-cell">Cantidad</th>
                            <th class="table-head-cell">Stock final</th>
                            <th class="table-head-cell">Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr class="table-row">
                                <td class="table-cell font-semibold text-slate-900">{{ $movement->product?->name }}</td>
                                <td class="table-cell text-slate-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                <td class="table-cell">{{ $movement->typeLabel() }}</td>
                                <td class="table-cell text-slate-500">{{ $movement->supplier?->name ?? '-' }}</td>
                                <td class="table-cell text-xs">{{ $movement->directionLabel() }}</td>
                                <td class="table-cell">
                                    <span class="{{ $movement->direction === 'in' ? 'badge-success' : 'badge-warning' }}">
                                        {{ $movement->direction === 'in' ? '+' : '-' }}{{ $movement->quantity }}
                                    </span>
                                </td>
                                <td class="table-cell font-semibold text-slate-900">{{ $movement->stock_after }}</td>
                                <td class="table-cell text-slate-500">{{ $movement->reference ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="8" class="empty-state">No hay movimientos registrados con los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
