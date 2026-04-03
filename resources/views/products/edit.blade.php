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
                        <label class="field-label">Stock actual</label>
                        <input type="text" value="{{ $product->stock }} {{ $product->stockUnitLabel() }}" class="field-input" readonly>
                        <p class="field-help">Para modificar stock usa el modulo de inventario (entradas, ventas o ajustes).</p>
                        <a href="{{ route('inventory.index', ['product_id' => $product->id]) }}" class="action-link mt-2 inline-flex">Ir a inventario</a>
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