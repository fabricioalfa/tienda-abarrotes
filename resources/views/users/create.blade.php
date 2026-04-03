{{-- filepath: /home/fabri/Documentos/tienda/resources/views/users/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Seguridad y acceso</p>
            <h2 class="page-title mt-2">Nuevo usuario</h2>
            <p class="page-subtitle mt-3">Crea cuentas con el rol mínimo necesario para una operación segura y ordenada.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl">
            <form method="POST" action="{{ route('users.store') }}" class="form-card space-y-5">
                @csrf

                <div>
                    <label class="field-label">Nombre</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="field-input">
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Correo</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="field-input">
                    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Rol</label>
                    <select name="role" class="field-input">
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Contraseña</label>
                    <input type="password" name="password" class="field-input">
                    @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="field-input">
                </div>

                <div class="module-actions">
                    <button class="btn-primary">Guardar</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
    </div>
</x-app-layout>