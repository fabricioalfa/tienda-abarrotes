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
                trackExpiration: {{ old('track_expiration', $product->track_expiration) ? 'true' : 'false' }},
                presentations: @js(collect(old('presentations', $product->presentations->map(fn ($p) => [
                    'name' => $p->name,
                    'units' => $p->units_per_presentation,
                    'price' => $p->price,
                ])))->map(fn ($p) => [
                    'name' => $p['name'] ?? '',
                    'units' => $p['units'] ?? 1,
                    'price' => $p['price'] ?? '',
                ])->values()),
                addPresentation() {
                    this.presentations.push({ name: '', units: 1, price: '' });
                },
                removePresentation(i) {
                    this.presentations.splice(i, 1);
                },
                addPreset(name, units) {
                    this.presentations.push({ name: name, units: units, price: '' });
                }
              }">
            @csrf
            @method('PUT')

            {{-- Datos básicos --}}
            <div class="form-grid">
                <div>
                    <label class="field-label">Código del producto</label>
                    <input type="text" value="{{ $product->code }}" class="field-input" readonly>
                    <p class="field-help">El código se genera automáticamente y no se puede editar.</p>
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
                    <label class="field-label">Costo de compra (Bs.)</label>
                    <input type="number" step="0.01" min="0" name="purchase_price"
                           value="{{ old('purchase_price', $product->purchase_price) }}" class="field-input" placeholder="0.00">
                    <p class="field-help">No puede ser mayor al precio de venta.</p>
                    @error('purchase_price') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
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

            {{-- Presentaciones múltiples (solo unidades) --}}
            <div x-show="typeKey === 'unit'" x-cloak>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="field-label" style="margin:0">Presentaciones de venta</p>
                            <p class="field-help">Opciones como medio maple, maple, caja, docena, etc. Se muestran en el POS al lado de "Unidad".</p>
                        </div>
                        <div class="flex gap-2 sm:flex-wrap">
                            <button type="button" class="btn-secondary flex-1 sm:auto" @click="addPreset('Paquete', 1)">Paquete</button>
                            <button type="button" class="btn-secondary flex-1 sm:auto" @click="addPreset('Medio maple', 1)">Medio maple</button>
                            <button type="button" class="btn-secondary flex-1 sm:auto" @click="addPreset('Maple', 1)">Maple</button>
                            <button type="button" class="btn-secondary" @click="addPresentation()">
                                + Agregar presentación
                            </button>
                        </div>
                    </div>

                @error('presentations') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror

                <div class="mt-4 space-y-3">
                    <template x-for="(p, i) in presentations" :key="i">
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <div class="form-grid" style="align-items:end">
                                <div>
                                    <label class="field-label">Nombre</label>
                                    <input type="text" :name="`presentations[${i}][name]`" x-model="p.name"
                                           class="field-input" placeholder="Ej. Medio maple, Maple, Caja">
                                </div>
                                <div>
                                    <label class="field-label">Unidades por presentación</label>
                                    <input type="number" min="1" step="1" :name="`presentations[${i}][units]`"
                                           x-model="p.units" class="field-input" placeholder="Ej. 15">
                                </div>
                                <div>
                                    <label class="field-label">Precio (Bs.)</label>
                                    <input type="number" min="0" step="0.01" :name="`presentations[${i}][price]`"
                                           x-model="p.price" class="field-input" placeholder="Ej. 15.00">
                                </div>
                                <div>
                                    <button type="button" class="btn-secondary" @click="removePresentation(i)">
                                        Quitar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <p x-show="presentations.length === 0" class="text-sm text-slate-400">
                        Sin presentaciones. El producto se venderá solo por unidad.
                    </p>
                </div>
            </div>

            {{-- Producto a granel (solo peso) --}}
            <div x-show="typeKey !== 'unit'" x-cloak>
                <div class="form-check-card">
                    <input type="checkbox" name="is_bulk" value="1" id="is_bulk"
                           class="field-checkbox" @checked(old('is_bulk', $product->is_bulk))>
                    <label for="is_bulk" class="text-sm font-semibold text-blue-800">
                        Producto a granel (permite escribir en Kg o en Bs. en el POS)
                    </label>
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
