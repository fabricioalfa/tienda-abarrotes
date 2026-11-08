<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Inventario</p>
                <h2 class="page-title mt-2">Productos proximos a vencer</h2>
                <p class="page-subtitle mt-3">Productos con stock disponible que vencen en el rango de fechas seleccionado.</p>
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

        @if (session('warn'))
            <div class="notice-warning">{{ session('warn') }}</div>
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
                            <td class="table-cell">
                                @php
                                $days = (int) floor(
                                    ($batch->expires_at->startOfDay()->timestamp - now()->startOfDay()->timestamp) / 86400
                                );
                                echo $days >= 0 ? $days : -$days;
                                @endphp>
                            </td>
                            <td class="table-cell">{{ $batch->remaining_quantity }}</td>
                            <td class="table-cell">{{ $batch->supplier_name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="5" class="empty-state">No hay productos por vencer en el rango seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            {{ $expiringBatches->links() }}
        </div>
    </div>
</x-app-layout>