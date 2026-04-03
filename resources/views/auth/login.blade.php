<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-extrabold text-slate-900">Iniciar sesión</h2>
            <p class="mt-2 text-sm text-slate-500">Ingresa con tu cuenta para acceder al panel.</p>
        </div>

        <div class="rounded-[18px] border border-blue-100 bg-blue-50/80 p-4 text-sm text-blue-900">
            <p class="font-semibold">Usuarios de prueba</p>
            <div class="mt-3 space-y-1 text-blue-800">
                <p>Administrador: admin@tienda.local / password</p>
                <p>Caja: caja@tienda.local / password</p>
            </div>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

            <div>
                <x-input-label for="email" value="Correo electrónico" />
                <x-text-input id="email" class="mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Contraseña" />

                <x-text-input id="password" class="mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label for="remember_me" class="inline-flex items-center gap-3 text-sm text-slate-600">
                    <input id="remember_me" type="checkbox" class="field-checkbox" name="remember">
                    <span>Recordar sesión</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold text-slate-500 transition hover:text-slate-900" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <div class="pt-1">
                <x-primary-button class="w-full justify-center">
                    Ingresar
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
