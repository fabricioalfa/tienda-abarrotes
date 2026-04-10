<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|merriweather:400,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $user = auth()->user();
        $routeName = request()->route()?->getName() ?? 'dashboard';
        $todayLabel = \Carbon\Carbon::now()->locale('es')->translatedFormat('d \\d\\e F, Y');
        $initials = collect(explode(' ', (string) $user?->name))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        $breadcrumbs = [
            ['label' => 'Inicio', 'route' => route('dashboard')],
        ];

        if (request()->routeIs('products.*')) {
            $breadcrumbs[] = ['label' => 'Productos', 'route' => route('products.index')];

            if (request()->routeIs('products.create')) {
                $breadcrumbs[] = ['label' => 'Nuevo producto', 'route' => null];
            } elseif (request()->routeIs('products.edit')) {
                $breadcrumbs[] = ['label' => 'Editar producto', 'route' => null];
            }
        } elseif (request()->routeIs('inventory.*')) {
            $breadcrumbs[] = ['label' => 'Inventario', 'route' => route('inventory.index')];
        } elseif (request()->routeIs('sales.*')) {
            $breadcrumbs[] = ['label' => 'Ventas', 'route' => route('sales.index')];

            if (request()->routeIs('sales.create')) {
                $breadcrumbs[] = ['label' => 'Caja', 'route' => null];
            } elseif (request()->routeIs('sales.show')) {
                $breadcrumbs[] = ['label' => 'Recibo', 'route' => null];
            }
        } elseif (request()->routeIs('reports.*')) {
            $breadcrumbs[] = ['label' => 'Reportes', 'route' => route('reports.sales')];

            if (request()->routeIs('reports.sales')) {
                $breadcrumbs[] = ['label' => 'Ventas', 'route' => null];
            }
        } elseif (request()->routeIs('categories.*')) {
            $breadcrumbs[] = ['label' => 'Categorías', 'route' => route('categories.index')];

            if (request()->routeIs('categories.create')) {
                $breadcrumbs[] = ['label' => 'Nueva categoría', 'route' => null];
            } elseif (request()->routeIs('categories.edit')) {
                $breadcrumbs[] = ['label' => 'Editar categoría', 'route' => null];
            }
        } elseif (request()->routeIs('users.*')) {
            $breadcrumbs[] = ['label' => 'Usuarios', 'route' => route('users.index')];

            if (request()->routeIs('users.create')) {
                $breadcrumbs[] = ['label' => 'Nuevo usuario', 'route' => null];
            } elseif (request()->routeIs('users.edit')) {
                $breadcrumbs[] = ['label' => 'Editar usuario', 'route' => null];
            }
        } elseif (request()->routeIs('profile.*')) {
            $breadcrumbs[] = ['label' => 'Perfil', 'route' => route('profile.edit')];
        }
    @endphp
    <body class="font-sans antialiased text-slate-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <div class="min-w-0 lg:pl-[280px]">
                <header class="px-4 pt-4 sm:px-6 lg:px-8 lg:pt-5">
                    <div class="dashboard-header-shell dashboard-header-shell-compact px-4 py-4 sm:px-6">
                        <div class="dashboard-header-top">
                            <div class="min-w-0">
                                <nav class="dashboard-breadcrumbs" aria-label="Breadcrumb">
                                    @foreach ($breadcrumbs as $index => $breadcrumb)
                                        @if ($index > 0)
                                            <span class="dashboard-breadcrumb-separator">&gt;</span>
                                        @endif

                                        @if ($breadcrumb['route'])
                                            <a href="{{ $breadcrumb['route'] }}" class="dashboard-breadcrumb-link">{{ $breadcrumb['label'] }}</a>
                                        @else
                                            <span class="dashboard-breadcrumb-current">{{ $breadcrumb['label'] }}</span>
                                        @endif
                                    @endforeach
                                </nav>
                            </div>

                            <div class="dashboard-header-meta">
                                <div class="dashboard-date-pill">{{ $todayLabel }}</div>
                            </div>
                        </div>

                        @isset($header)
                            <div class="mt-5 rounded-[16px] border border-blue-100/90 bg-[#cddbe8] px-4 py-4 shadow-[0_18px_38px_-34px_rgba(37,99,235,0.45)] sm:px-6">
                            {{ $header }}
                            </div>
                        @endisset
                    </div>
                </header>

                <main class="min-w-0 px-4 py-4 sm:px-6 lg:px-8 lg:py-5">
                {{ $slot }}
                </main>
            </div>
        </div>
        <script>
            // Prevent bfcache from restoring stale authenticated pages when navigating back
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        </script>
    </body>
</html>
