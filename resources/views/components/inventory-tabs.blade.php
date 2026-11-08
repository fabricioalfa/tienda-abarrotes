@php
$tabs = [
  ['label' => 'Stock', 'route' => route('inventory.index'), 'active' => request()->routeIs('inventory.index')],
  ['label' => 'Movimientos', 'route' => route('inventory.movements'), 'active' => request()->routeIs('inventory.movements')],
  ['label' => 'Entradas', 'route' => route('inventory.entries'), 'active' => request()->routeIs('inventory.entries')],
  ['label' => 'Ajustes', 'route' => route('inventory.adjustments'), 'active' => request()->routeIs('inventory.adjustments')],
  ['label' => 'Vencimientos', 'route' => route('inventory.expiring'), 'active' => request()->routeIs('inventory.expiring')],
  ['label' => 'Costos', 'route' => route('inventory.costs'), 'active' => request()->routeIs('inventory.costs')],
]
@endphp

<div class="flex flex-col sm:flex-wrap gap-2">
  @foreach($tabs as $tab)
    <a href="{{ $tab['route'] }}" class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-semibold transition {{ $tab['active'] ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300 hover:text-blue-700' }}">
      {{ $tab['label'] }}
    </a>
  @endforeach
</div>