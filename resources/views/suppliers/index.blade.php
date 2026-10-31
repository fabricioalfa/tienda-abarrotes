{{-- filepath: /home/fabri/Documentos/tienda-abarrotes/resources/views/suppliers/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Compras</p>
                <h2 class="page-title mt-2">Proveedores</h2>
                <p class="page-subtitle mt-3">Gestiona los proveedores de la tienda y sus números de teléfono.</p>
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn-primary">Nuevo proveedor</a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="notice-success">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="notice-error">{{ session('error') }}</div>
        @endif

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_300px]">
            <form method="GET" action="{{ route('suppliers.index') }}" class="shell-panel-strong p-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o teléfono" class="field-input w-full">
                    <button class="btn-primary sm:min-w-[140px]">Buscar</button>
                </div>
            </form>

            <div class="metric-card">
                <p class="metric-label">Resultados</p>
                <p class="metric-value">{{ $suppliers->total() }}</p>
                <p class="metric-meta">Proveedores que coinciden con la vista actual.</p>
            </div>
        </div>

        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Nombre</th>
                            <th class="table-head-cell">Teléfono</th>
                            <th class="table-head-cell">Correo</th>
                            <th class="table-head-cell">Estado</th>
                            <th class="table-head-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr class="table-row">
                                <td class="table-cell font-semibold text-slate-900">{{ $supplier->name }}</td>
                                <td class="table-cell">{{ $supplier->phone ?: '-' }}</td>
                                <td class="table-cell text-slate-500">{{ $supplier->email ?: '-' }}</td>
                                <td class="table-cell">
                                    <span class="{{ $supplier->is_active ? 'badge-success' : 'badge-warning' }}">
                                        {{ $supplier->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="table-cell">
                                    <div class="flex flex-wrap gap-4">
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="action-link">Editar</a>

                                        @if (auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-link-danger" onclick="return confirm('¿Eliminar proveedor?')">Eliminar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="5" class="empty-state">
                                    No hay proveedores registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
