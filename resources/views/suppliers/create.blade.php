{{-- filepath: /home/fabri/Documentos/tienda-abarrotes/resources/views/suppliers/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Compras</p>
            <h2 class="page-title mt-2">Nuevo proveedor</h2>
            <p class="page-subtitle mt-3">Registra los datos del proveedor.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('suppliers.store') }}" class="form-card space-y-6">
            @csrf

            <div class="form-grid">
                <div>
                    <label class="field-label">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="field-input" required maxlength="255">
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="field-input" placeholder="Ej. 71234567">
                    @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="field-input" placeholder="Ej. proveedor@ejemplo.com">
                    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Dirección</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="field-input" placeholder="Ej. Av. Principal 123">
                    @error('address') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-3">
                <div class="form-check-card">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="field-checkbox" @checked(old('is_active', true))>
                    <label for="is_active" class="text-sm font-semibold text-blue-800">Proveedor activo</label>
                </div>
            </div>

            <div class="module-actions">
                <button class="btn-primary">Guardar</button>
                <a href="{{ route('suppliers.index') }}" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
