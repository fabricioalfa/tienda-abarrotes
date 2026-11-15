<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Reportes</p>
                <h2 class="page-title mt-2">Productos más vendidos</h2>
                <p class="page-subtitle mt-3">Ranking de productos con mayor volumen de ventas en el periodo seleccionado.</p>
            </div>
        </div>
    </x-slot>

    <x-report-tabs />

    <div class="space-y-6">
        <div class="shell-panel-strong p-4">
            <form method="GET" class="grid gap-3 md:grid-cols-[1fr_1fr_auto_auto]">
                <input type="date" name="from" value="{{ $from }}" class="field-input">
                <input type="date" name="to" value="{{ $to }}" class="field-input">
                <select name="category_id" class="field-input">
                    <option value="">Todas las categorías</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ $q }}" class="field-input" placeholder="Producto">
                <button class="btn-secondary">Actualizar reporte</button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Ranking</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900">Productos más vendidos</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-shell">
                        <thead class="table-head">
                            <tr>
                                <th class="table-head-cell">Producto</th>
                                <th class="table-head-cell">Cantidad</th>
                                <th class="table-head-cell">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $product)
                                <tr class="table-row">
                                    <td class="table-cell font-semibold text-slate-900">{{ $product->product_name }}</td>
                                    <td class="table-cell">{{ rtrim(rtrim(number_format((float) $product->sold_quantity, 3, '.', ''), '0'), '.') }}</td>
                                    <td class="table-cell">Bs. {{ number_format((float) $product->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="3" class="empty-state">Aun no hay productos vendidos en el rango elegido.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>