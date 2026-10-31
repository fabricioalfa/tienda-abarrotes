{{-- filepath: /home/fabri/Documentos/tienda-abarrotes/resources/views/inventory/entries.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Inventario</p>
                <h2 class="page-title mt-2">Entrada de stock</h2>
                <p class="page-subtitle mt-3">Registra el ingreso de productos con su costo y fecha de vencimiento.</p>
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
                        <option value="{{ $product->id }}" @selected(old('product_id') === $product->id)>
                            {{ $product->name }}
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
                    <select name="supplier_id" class="field-input">
                        <option value="">Seleccionar proveedor (opcional)</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                {{ $supplier->name }}{{ $supplier->phone ? ' (' . $supplier->phone . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if ($suppliers->isEmpty())
                        <p class="field-help">Aún no hay proveedores registrados. <a href="{{ route('suppliers.create') }}" class="action-link">Crear proveedor</a></p>
                    @endif
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
    </div>
</x-app-layout>
