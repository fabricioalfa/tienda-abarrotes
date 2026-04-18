@php
    $user = Auth::user();
    $navInitials = collect(explode(' ', (string) $user?->name))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $links = array_values(array_filter([
        ['label' => 'Resumen', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard', 'dashboard.admin', 'dashboard.caja'), 'icon' => 'M3.75 3h16.5A1.5 1.5 0 0 1 21.75 4.5v4.125A1.5 1.5 0 0 1 20.25 10.125H3.75a1.5 1.5 0 0 1-1.5-1.5V4.5A1.5 1.5 0 0 1 3.75 3Zm0 10.875h4.5a1.5 1.5 0 0 1 1.5 1.5v4.125a1.5 1.5 0 0 1-1.5 1.5h-4.5a1.5 1.5 0 0 1-1.5-1.5V15.375a1.5 1.5 0 0 1 1.5-1.5Zm10.5 0h6a1.5 1.5 0 0 1 1.5 1.5v4.125a1.5 1.5 0 0 1-1.5 1.5h-6a1.5 1.5 0 0 1-1.5-1.5V15.375a1.5 1.5 0 0 1 1.5-1.5Z'],
        $user->isAdmin() ? ['label' => 'Productos', 'route' => route('products.index'), 'active' => request()->routeIs('products.*'), 'icon' => 'M21 7.5v9a2.25 2.25 0 0 1-1.166 1.977l-7.5 4.143a2.25 2.25 0 0 1-2.168 0l-7.5-4.143A2.25 2.25 0 0 1 1.5 16.5v-9a2.25 2.25 0 0 1 1.166-1.977l7.5-4.143a2.25 2.25 0 0 1 2.168 0l7.5 4.143A2.25 2.25 0 0 1 21 7.5Zm-9 13.125V12M3.45 6.75 12 11.25l8.55-4.5'] : null,
        ['label' => 'Inventario', 'route' => route('inventory.index'), 'active' => request()->routeIs('inventory.*'), 'icon' => 'M20.25 7.5H3.75A1.5 1.5 0 0 0 2.25 9v9.75a1.5 1.5 0 0 0 1.5 1.5h16.5a1.5 1.5 0 0 0 1.5-1.5V9a1.5 1.5 0 0 0-1.5-1.5ZM6 7.5V6a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3v1.5M9.75 12h4.5'],
        ['label' => 'Ventas', 'route' => route('sales.index'), 'active' => request()->routeIs('sales.*'), 'icon' => 'M2.25 3h1.386a1.5 1.5 0 0 1 1.455 1.136L5.61 6H20.25a.75.75 0 0 1 .73.92l-1.5 6A.75.75 0 0 1 18.75 13.5H7.5a.75.75 0 0 1-.73-.57L4.152 4.5H2.25M7.5 18.75A1.125 1.125 0 1 1 5.25 18.75a1.125 1.125 0 0 1 2.25 0Zm12 0a1.125 1.125 0 1 1-2.25 0 1.125 1.125 0 0 1 2.25 0Z'],
        ['label' => 'Caja', 'route' => route('cash-registers.index'), 'active' => request()->routeIs('cash-registers.*'), 'icon' => 'M21 8.25v7.5A2.25 2.25 0 0 1 18.75 18H5.25A2.25 2.25 0 0 1 3 15.75v-7.5A2.25 2.25 0 0 1 5.25 6h13.5A2.25 2.25 0 0 1 21 8.25ZM7.5 12h9'],
        ['label' => 'Reportes', 'route' => route('reports.sales'), 'active' => request()->routeIs('reports.*'), 'icon' => 'M3 13.5h3.75v7.5H3v-7.5Zm7.125-6h3.75V21h-3.75V7.5Zm7.125-4.5H21V21h-3.75V3Z'],
        $user->isAdmin() ? ['label' => 'Categorias', 'route' => route('categories.index'), 'active' => request()->routeIs('categories.*'), 'icon' => 'M3.75 4.5h6.75v6.75H3.75V4.5Zm9.75 0h6.75v6.75H13.5V4.5Zm-9.75 9.75h6.75V21H3.75v-6.75Zm9.75 0h6.75V21H13.5v-6.75Z'] : null,
        $user->isAdmin() ? ['label' => 'Usuarios', 'route' => route('users.index'), 'active' => request()->routeIs('users.*'), 'icon' => 'M15 19.128a9.38 9.38 0 0 0-3-.878 9.38 9.38 0 0 0-3 .878m6 0a3 3 0 1 0-6 0m6 0H18a2.25 2.25 0 0 1 2.25 2.25V21m-8.25-11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.5 8.25a2.25 2.25 0 0 0-2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V21m-13.5 0v-.75A2.25 2.25 0 0 1 9 18h.75'] : null,
    ]));
@endphp

<nav x-data="{ open: false }">
    <div class="px-4 pt-4 sm:px-6 lg:hidden">
        <div class="executive-topbar flex items-center justify-between px-4 py-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="executive-brand-mark">{{ $navInitials }}</span>
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-semibold tracking-[0.08em] text-blue-100/80">{{ \App\Models\User::roles()[$user->role] ?? $user->role }}</p>
                    <p class="truncate text-base font-bold text-white">{{ $user->name }}</p>
                </div>
            </a>

            <button @click="open = !open" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-white shadow-sm transition hover:bg-white/15">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:block lg:w-[280px]">
        <div class="executive-sidebar-shell flex h-full flex-col px-5 py-6" x-data="{ profileOpenDesktop: false }">
            <div class="executive-sidebar-top-card">
                <div class="relative">
                    <button type="button" class="executive-sidebar-account-trigger" @click="profileOpenDesktop = !profileOpenDesktop" :aria-expanded="profileOpenDesktop.toString()">
                        <span class="executive-brand-mark executive-brand-mark-lg">{{ $navInitials }}</span>
                        <span class="min-w-0 flex-1 text-left">
                            <span class="block text-[11px] font-semibold tracking-[0.08em] text-blue-100/80">{{ \App\Models\User::roles()[$user->role] ?? $user->role }}</span>
                            <span class="mt-1 block text-[1.05rem] font-bold leading-5 text-white">{{ $user->name }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-blue-100/70 transition" :class="profileOpenDesktop ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="profileOpenDesktop"
                        x-transition.origin.top.left
                        @click.outside="profileOpenDesktop = false"
                        class="executive-user-dropdown"
                    >
                        <a href="{{ route('profile.edit') }}" class="executive-user-dropdown-link" @click="profileOpenDesktop = false">Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="executive-user-dropdown-link executive-user-dropdown-link-danger">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>

            <p class="mt-5 text-[10px] font-semibold uppercase tracking-[0.24em] text-blue-100/70">Navegación</p>

            <div class="mt-3 space-y-1.5">
                @foreach ($links as $link)
                    <a href="{{ $link['route'] }}"
                        class="executive-nav-link {{ $link['active'] ? 'executive-nav-link-active' : '' }}">
                        <span class="flex items-center gap-3">
                            <span class="executive-nav-icon">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $link['icon'] }}" />
                                </svg>
                            </span>
                            <span>{{ $link['label'] }}</span>
                        </span>
                        <span class="h-2 w-2 rounded-full {{ $link['active'] ? 'bg-blue-700' : 'bg-white/40' }}"></span>
                    </a>
                @endforeach
            </div>
        </div>
    </aside>

    <div x-cloak x-show="open" class="fixed inset-0 z-40 bg-slate-950/30 backdrop-blur-sm lg:hidden" @click="open = false"></div>

    <aside x-cloak x-show="open" x-transition class="fixed inset-y-0 left-0 z-50 w-[88%] max-w-[320px] lg:hidden">
        <div class="executive-sidebar-mobile flex h-full flex-col px-5 py-6" x-data="{ profileOpenMobile: false }">
            <div class="executive-sidebar-top-card">
                <div class="flex justify-end">
                    <button @click="open = false" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                </button>
                </div>

                <div class="relative mt-3">
                    <button type="button" class="executive-sidebar-account-trigger" @click="profileOpenMobile = !profileOpenMobile" :aria-expanded="profileOpenMobile.toString()">
                        <span class="executive-brand-mark executive-brand-mark-lg">{{ $navInitials }}</span>
                        <span class="min-w-0 flex-1 text-left">
                            <span class="block text-[11px] font-semibold tracking-[0.08em] text-blue-100/80">{{ \App\Models\User::roles()[$user->role] ?? $user->role }}</span>
                            <span class="mt-1 block text-[1.05rem] font-bold leading-5 text-white">{{ $user->name }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-blue-100/70 transition" :class="profileOpenMobile ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="profileOpenMobile"
                        x-transition.origin.top.left
                        @click.outside="profileOpenMobile = false"
                        class="executive-user-dropdown"
                    >
                        <a href="{{ route('profile.edit') }}" class="executive-user-dropdown-link" @click="profileOpenMobile = false; open = false">Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="executive-user-dropdown-link executive-user-dropdown-link-danger">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>

            <p class="mt-5 text-[10px] font-semibold uppercase tracking-[0.24em] text-blue-100/70">Navegación</p>

            <div class="mt-3 space-y-1.5">
                @foreach ($links as $link)
                    <a href="{{ $link['route'] }}"
                        class="executive-nav-link {{ $link['active'] ? 'executive-nav-link-active' : '' }}" @click="open = false">
                        <span class="flex items-center gap-3">
                            <span class="executive-nav-icon">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $link['icon'] }}" />
                                </svg>
                            </span>
                            <span>{{ $link['label'] }}</span>
                        </span>
                        <span class="h-2 w-2 rounded-full {{ $link['active'] ? 'bg-blue-700' : 'bg-white/40' }}"></span>
                    </a>
                @endforeach
            </div>
        </div>
    </aside>
</nav>
