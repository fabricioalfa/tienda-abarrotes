{{-- filepath: /home/fabri/Documentos/tienda/resources/views/products/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Inventario</p>
            <h2 class="page-title mt-2">Nuevo producto</h2>
            <p class="page-subtitle mt-3">Registra artículos con categoría, tipo de venta, precio y stock en una ficha limpia y consistente.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl">
            <form method="POST" action="{{ route('products.store') }}" class="form-card space-y-6">
                @csrf

                <div class="form-grid">
                    <div>
                        <label class="field-label">Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="field-input">
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Código de barras</label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}" class="field-input">
                        @error('barcode') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Categoría</label>
                        <select name="category_id" class="field-input">
                            <option value="">Seleccione</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Tipo de venta</label>
                        <select name="sale_type" class="field-input">
                            <option value="unit" @selected(old('sale_type') === 'unit')>Por unidad</option>
                            <option value="weight" @selected(old('sale_type') === 'weight')>Por peso</option>
                        </select>
                        @error('sale_type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Unidad de peso</label>
                        <select name="weight_unit" class="field-input">
                            <option value="">No aplica</option>
                            <option value="kg" @selected(old('weight_unit') === 'kg')>Kg</option>
                            <option value="g" @selected(old('weight_unit') === 'g')>Gramos</option>
                        </select>
                        @error('weight_unit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Precio</label>
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" class="field-input">
                        @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="field-label">Stock inicial</label>
                        <input type="number" step="0.001" min="0" name="initial_stock" value="{{ old('initial_stock', 0) }}" class="field-input">
                        <p class="field-help">Este valor genera una entrada automática en inventario.</p>
                        @error('initial_stock') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="form-check-card">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="field-checkbox" @checked(old('is_active', true))>
                    <label for="is_active" class="text-sm font-semibold text-blue-800">Producto activo</label>
                </div>

                <div class="module-actions">
                    <button class="btn-primary">Guardar</button>
                    <a href="{{ route('products.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
    </div>
</x-app-layout>