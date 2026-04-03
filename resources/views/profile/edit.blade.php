<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="section-kicker">Cuenta</p>
            <h2 class="page-title mt-2">Perfil</h2>
            <p class="page-subtitle mt-3">Actualiza tu información personal, contraseña y opciones de seguridad desde una vista unificada.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <div class="form-card">
                    <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="form-card">
                    <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <aside class="shell-panel p-6">
                <p class="section-kicker">Seguridad</p>
                <h3 class="display-title mt-3 text-2xl font-bold text-slate-900">Zona sensible</h3>
                <p class="page-subtitle mt-3 text-sm">La eliminación de cuenta es irreversible. Antes de continuar, confirma que no necesitas conservar datos ni acceso al sistema.</p>

                <div class="mt-6 rounded-[24px] border border-rose-100 bg-rose-50/80 p-5">
                    @include('profile.partials.delete-user-form')
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
