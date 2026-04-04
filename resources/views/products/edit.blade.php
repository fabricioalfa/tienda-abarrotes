{{-- filepath: /home/fabri/Documentos/tienda/resources/views/products/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Inventario</p>
            <h2 class="page-title mt-2">Editar producto</h2>
            <p class="page-subtitle mt-3">Ajusta datos comerciales y operativos manteniendo una ficha clara y uniforme.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl">
            <form method="POST" action="{{ route('products.update', $product) }}" class="form-card space-y-6">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div>
                        <label class="field-label">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="field-input">
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Marca</label>
                        <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="field-input">
                    </div>

                    <div>
                        <label class="field-label">Proveedor</label>
                        <input type="text" name="supplier_name" value="{{ old('supplier_name', $product->supplier_name) }}" class="field-input">
                    </div>

                    <div>
                        <label class="field-label">Código de barras</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="field-input">
                        @error('barcode') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Categoría</label>
                        <select name="category_id" class="field-input">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Tipo de venta</label>
                        <select name="sale_type" class="field-input">
                            <option value="unit" @selected(old('sale_type', $product->sale_type) === 'unit')>Por unidad</option>
                            <option value="weight" @selected(old('sale_type', $product->sale_type) === 'weight')>Por peso</option>
                        </select>
                        @error('sale_type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Unidad de peso</label>
                        <select name="weight_unit" class="field-input">
                            <option value="">No aplica</option>
                            <option value="kg" @selected(old('weight_unit', $product->weight_unit) === 'kg')>Kg</option>
                            <option value="lb" @selected(old('weight_unit', $product->weight_unit) === 'lb')>Libra</option>
                            <option value="g" @selected(old('weight_unit', $product->weight_unit) === 'g')>Gramos</option>
                        </select>
                        @error('weight_unit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Precio</label>
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="field-input">
                        @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Stock mínimo</label>
                        <input type="number" step="0.001" min="0" name="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock) }}" class="field-input">
                    </div>

                    <div>
                        <label class="field-label">Stock actual</label>
                        <input type="text" value="{{ $product->stockBreakdownLabel() }}" class="field-input" readonly>
                        <p class="field-help">Para modificar stock usa el modulo de inventario (entradas, ventas o ajustes).</p>
                        <a href="{{ route('inventory.index', ['product_id' => $product->id]) }}" class="action-link mt-2 inline-flex">Ir a inventario</a>
                    </div>
                </div>

                <div class="grid gap-4 rounded-3xl border border-blue-100 bg-blue-50/40 p-5 md:grid-cols-2">
                    <div class="form-check-card !border-0 !bg-transparent !p-0">
                        <input type="checkbox" name="allows_package_sale" value="1" id="allows_package_sale" class="field-checkbox" @checked(old('allows_package_sale', $product->allows_package_sale))>
                        <label for="allows_package_sale" class="text-sm font-semibold text-blue-800">Vender por paquete o maple</label>
                    </div>

                    <div class="form-check-card !border-0 !bg-transparent !p-0">
                        <input type="checkbox" name="track_expiration" value="1" id="track_expiration" class="field-checkbox" @checked(old('track_expiration', $product->track_expiration))>
                        <label for="track_expiration" class="text-sm font-semibold text-blue-800">Controlar vencimiento</label>
                    </div>

                    <div>
                        <label class="field-label">Nombre del paquete</label>
                        <input type="text" name="package_name" value="{{ old('package_name', $product->package_name) }}" class="field-input" placeholder="Ej. maple">
                    </div>

                    <div>
                        <label class="field-label">Unidades por paquete</label>
                        <input type="number" min="2" name="units_per_package" value="{{ old('units_per_package', $product->units_per_package) }}" class="field-input" placeholder="Ej. 30">
                    </div>

                    <div>
                        <label class="field-label">Precio por paquete</label>
                        <input type="number" step="0.01" min="0" name="package_price" value="{{ old('package_price', $product->package_price) }}" class="field-input">
                    </div>
                </div>

                <div class="form-check-card">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="field-checkbox" @checked(old('is_active', $product->is_active))>
                    <label for="is_active" class="text-sm font-semibold text-blue-800">Producto activo</label>
                </div>

                <div class="module-actions">
                    <button class="btn-primary">Actualizar</button>
                    <a href="{{ route('products.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
    </div>
</x-app-layout>