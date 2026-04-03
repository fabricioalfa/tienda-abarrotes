{{-- filepath: /home/fabri/Documentos/tienda/resources/views/categories/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Administración</p>
            <h2 class="page-title mt-2">Nueva categoría</h2>
            <p class="page-subtitle mt-3">Crea una categoría clara para ordenar productos y facilitar la gestión comercial.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl">
            <form method="POST" action="{{ route('categories.store') }}" class="form-card space-y-5">
                @csrf

                <div>
                    <label class="field-label">Nombre</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="field-input">
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Descripción</label>
                    <textarea name="description" rows="4" class="field-textarea">{{ old('description') }}</textarea>
                    @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="module-actions">
                    <button class="btn-primary">Guardar</button>
                    <a href="{{ route('categories.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
    </div>
</x-app-layout>