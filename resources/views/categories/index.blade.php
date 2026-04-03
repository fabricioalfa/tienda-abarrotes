{{-- filepath: /home/fabri/Documentos/tienda/resources/views/categories/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Administración</p>
                <h2 class="page-title mt-2">Categorías</h2>
                <p class="page-subtitle mt-3">Mantén la estructura del catálogo con nombres consistentes y descripciones claras para el equipo.</p>
            </div>

            <a href="{{ route('categories.create') }}" class="btn-primary">Nueva categoría</a>
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
                <article class="metric-card md:col-span-1">
                    <p class="metric-label">Total</p>
                    <p class="metric-value">{{ $categories->total() }}</p>
                    <p class="metric-meta">Categorías registradas en el sistema.</p>
                </article>
                <article class="metric-card md:col-span-2">
                    <p class="metric-label">Uso recomendado</p>
                    <p class="metric-meta mt-4 text-sm leading-6 text-slate-600">Define categorías comerciales simples y evita duplicados para que el catálogo conserve orden y facilite búsquedas futuras.</p>
                </article>
            </div>

            <div class="table-card">
                <div class="table-toolbar">
                    <div>
                        <p class="section-kicker">Listado</p>
                        <h3 class="display-title mt-2 text-2xl font-bold text-slate-900">Catálogo de categorías</h3>
                    </div>
                </div>

                <div class="overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Nombre</th>
                            <th class="table-head-cell">Descripción</th>
                            <th class="table-head-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr class="table-row">
                                <td class="table-cell font-semibold text-slate-900">{{ $category->name }}</td>
                                <td class="table-cell text-slate-500">{{ $category->description ?: 'Sin descripción registrada.' }}</td>
                                <td class="table-cell">
                                    <div class="flex flex-wrap gap-4">
                                    <a href="{{ route('categories.edit', $category) }}" class="action-link">Editar</a>

                                    <form method="POST" action="{{ route('categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-link-danger" onclick="return confirm('¿Eliminar categoría?')">Eliminar</button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="3" class="empty-state">No hay categorías registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                <div class="table-footer">
                    {{ $categories->links() }}
                </div>
            </div>
    </div>
</x-app-layout>