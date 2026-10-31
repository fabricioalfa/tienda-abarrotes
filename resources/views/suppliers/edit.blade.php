{{-- filepath: /home/fabri/Documentos/tienda-abarrotes/resources/views/suppliers/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Compras</p>
            <h2 class="page-title mt-2">Editar proveedor</h2>
            <p class="page-subtitle mt-3">Actualiza los datos del proveedor.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="form-card space-y-6">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div>
                    <label class="field-label">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="field-input" required maxlength="255">
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="field-input" placeholder="Ej. 71234567">
                    @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="field-input" placeholder="Ej. proveedor@ejemplo.com">
                    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Dirección</label>
                    <input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="field-input" placeholder="Ej. Av. Principal 123">
                    @error('address') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-3">
                <div class="form-check-card">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="field-checkbox" @checked(old('is_active', $supplier->is_active))>
                    <label for="is_active" class="text-sm font-semibold text-blue-800">Proveedor activo</label>
                </div>
            </div>

            <div class="module-actions">
                <button class="btn-primary">Actualizar</button>
                <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
