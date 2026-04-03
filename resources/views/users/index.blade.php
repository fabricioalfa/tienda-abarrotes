{{-- filepath: /home/fabri/Documentos/tienda/resources/views/users/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Seguridad y acceso</p>
                <h2 class="page-title mt-2">Usuarios</h2>
                <p class="page-subtitle mt-3">Administra cuentas y roles con una vista orientada a control, trazabilidad y lectura rápida.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn-primary">Nuevo usuario</a>
        </div>
    </x-slot>

    <div class="space-y-6">
            @if (session('status'))
                <div class="notice-success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="notice-error">{{ session('error') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <article class="metric-card">
                    <p class="metric-label">Cuentas</p>
                    <p class="metric-value">{{ $users->total() }}</p>
                    <p class="metric-meta">Usuarios visibles en la gestión actual.</p>
                </article>
                <article class="metric-card md:col-span-2">
                    <p class="metric-label">Criterio</p>
                    <p class="metric-meta mt-4 text-sm leading-6 text-slate-600">Mantén roles mínimos por usuario para evitar accesos innecesarios y preservar una operación controlada.</p>
                </article>
            </div>

            <div class="table-card">
                <div class="overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Nombre</th>
                            <th class="table-head-cell">Correo</th>
                            <th class="table-head-cell">Rol</th>
                            <th class="table-head-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="table-row">
                                <td class="table-cell font-semibold text-slate-900">{{ $user->name }}</td>
                                <td class="table-cell text-slate-500">{{ $user->email }}</td>
                                <td class="table-cell"><span class="badge-neutral">{{ \App\Models\User::roles()[$user->role] ?? $user->role }}</span></td>
                                <td class="table-cell">
                                    <div class="flex flex-wrap gap-4">
                                    <a href="{{ route('users.edit', $user) }}" class="action-link">Editar</a>

                                    <form method="POST" action="{{ route('users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-link-danger" onclick="return confirm('¿Eliminar usuario?')">Eliminar</button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="empty-state">No hay usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                <div class="table-footer">
                    {{ $users->links() }}
                </div>
            </div>
    </div>
</x-app-layout>