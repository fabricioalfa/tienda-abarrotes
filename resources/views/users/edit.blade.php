{{-- filepath: /home/fabri/Documentos/tienda/resources/views/users/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Seguridad y acceso</p>
            <h2 class="page-title mt-2">Editar usuario</h2>
            <p class="page-subtitle mt-3">Actualiza datos, rol y credenciales conservando una estructura formal y legible.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl">
            <form method="POST" action="{{ route('users.update', $user) }}" class="form-card space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="field-label">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="field-input">
                    @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Correo</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="field-input">
                    @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Rol</label>
                    <select name="role" class="field-input">
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Nueva contraseña</label>
                    <input type="password" name="password" class="field-input">
                    @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="field-label">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmation" class="field-input">
                </div>

                <div class="module-actions">
                    <button class="btn-primary">Actualizar</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
    </div>
</x-app-layout>