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

        {{-- Componentes de tabs --}}
        @include('components.inventory-tabs')

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
        </div>

        {{-- Tabla de Stock disponible --}}
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
        </section>
    </div>
</x-app-layout>