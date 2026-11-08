@php
$tabs = [
  ['label' => 'Ventas', 'route' => route('reports.sales'), 'active' => request()->routeIs('reports.sales')],
  ['label' => 'Productos', 'route' => route('reports.products'), 'active' => request()->routeIs('reports.products')],
  ['label' => 'Vencimientos', 'route' => route('reports.expiring'), 'active' => request()->routeIs('reports.expiring')],
]
@endphp

<div class="flex flex-col sm:flex-wrap gap-2">
  @foreach($tabs as $tab)
    <a href="{{ $tab['route'] }}" class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-semibold transition {{ $tab['active'] ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-blue-700' }}">
      {{ $tab['label'] }}
    </a>
  @endforeach
</div>