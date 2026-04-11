{{-- filepath: /home/fabri/Documentos/tienda/resources/views/products/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Inventario</p>
            <h2 class="page-title mt-2">Editar producto</h2>
            <p class="page-subtitle mt-3">Actualiza los datos del producto. El stock se administra desde inventario.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl">
        @php
            $typeKey = old('type_key', $product->sale_type === 'unit' ? 'unit' : ($product->weight_unit ?? 'kg'));
        @endphp
        <form method="POST" action="{{ route('products.update', $product) }}" class="form-card space-y-6"
              x-data="{
                typeKey: '{{ $typeKey }}',
                allowsPackage: {{ old('allows_package_sale', $product->allows_package_sale) ? 'true' : 'false' }},
                trackExpiration: {{ old('track_expiration', $product->track_expiration) ? 'true' : 'false' }}
              }">
            @csrf
            @method('PUT')

            {{-- Datos básicos --}}
            <div class="form-grid">
                <div>
                    <label class="field-label">Código del producto</label>
                    <input type="text" name="code" value="{{ old('code', $product->code) }}" class="field-input" placeholder="Ej. 10001">
                    <p class="field-help">Debe ser numérico y único. Puede usarse para lectura con código de barras.</p>
                    @error('code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="field-input" required maxlength="255">
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Código de barras (opcional)</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="field-input" placeholder="Si lo dejas vacío, se usa el código del producto">
                    @error('barcode') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Categoría</label>
                    <select name="category_id" class="field-input">
                        <option value="">Seleccione una categoría</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Tipo</label>
                    <select name="type_key" class="field-input" x-model="typeKey">
                        <option value="unit" @selected($typeKey === 'unit')>Unidad</option>
                        <option value="kg" @selected($typeKey === 'kg')>Kg</option>
                        <option value="lb" @selected($typeKey === 'lb')>Libra</option>
                        <option value="quarter" @selected($typeKey === 'quarter')>Cuartilla</option>
                    </select>
                    @error('type_key') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Precio de venta (Bs.) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="field-input" required>
                    <p class="field-help">Para kg, libra o cuartilla el precio se aplica a esa unidad.</p>
                    @error('price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Stock mínimo</label>
                    <input type="number" step="0.001" min="0" name="minimum_stock"
                           value="{{ old('minimum_stock', $product->minimum_stock ?? 5) }}" class="field-input">
                    <p class="field-help">Alerta cuando el stock baje de este valor.</p>
                    @error('minimum_stock') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Stock actual</label>
                    <input type="text" value="{{ $product->stockBreakdownLabel() }}" class="field-input" readonly>
                    <p class="field-help">Para modificar stock usa el módulo de inventario.</p>
                    <a href="{{ route('inventory.index', ['product_id' => $product->id]) }}" class="action-link mt-2 inline-flex">Ir a inventario</a>
                </div>
            </div>

            {{-- Venta por paquete (solo unidades) --}}
            <div x-show="typeKey === 'unit'" x-cloak>
                <div class="form-check-card">
                    <input type="checkbox" name="allows_package_sale" value="1" id="allows_package_sale"
                           class="field-checkbox" x-model="allowsPackage"
                           @checked(old('allows_package_sale', $product->allows_package_sale))>
                    <label for="allows_package_sale" class="text-sm font-semibold text-blue-800">
                        Permitir venta por paquete (caja, bolsa, docena, etc.)
                    </label>
                </div>

                <div x-show="allowsPackage" x-cloak class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-blue-600">Configuración de paquete</p>
                    <div class="form-grid">
                        <div>
                            <label class="field-label">Nombre del paquete</label>
                            <input type="text" name="package_name"
                                   value="{{ old('package_name', $product->package_name) }}"
                                   class="field-input" placeholder="Ej. Caja, Bolsa, Docena">
                            @error('package_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="field-label">Unidades por paquete</label>
                            <input type="number" min="1" step="1" name="units_per_package"
                                   value="{{ old('units_per_package', $product->units_per_package) }}"
                                   class="field-input" placeholder="Ej. 12">
                            @error('units_per_package') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="field-label">Precio del paquete (Bs.)</label>
                            <input type="number" min="0" step="0.01" name="package_price"
                                   value="{{ old('package_price', $product->package_price) }}"
                                   class="field-input" placeholder="Ej. 60.00">
                            @error('package_price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Opciones adicionales --}}
            <div class="space-y-3">
                <div class="form-check-card">
                    <input type="checkbox" name="track_expiration" value="1" id="track_expiration"
                           class="field-checkbox"
                           @checked(old('track_expiration', $product->track_expiration))>
                    <label for="track_expiration" class="text-sm font-semibold text-blue-800">
                        Registrar fechas de vencimiento en cada entrada de inventario
                    </label>
                </div>

                <div class="form-check-card">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           class="field-checkbox" @checked(old('is_active', $product->is_active))>
                    <label for="is_active" class="text-sm font-semibold text-blue-800">Producto activo</label>
                </div>
            </div>

            <div class="module-actions">
                <button class="btn-primary">Actualizar</button>
                <a href="{{ route('products.index') }}" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
