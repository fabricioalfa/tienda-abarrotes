<section class="space-y-6">
    <header>
        <h2 class="display-title text-2xl font-bold text-slate-900">
            Eliminar cuenta
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-600">
            Esta acción elimina definitivamente la cuenta y sus recursos relacionados. Continúa solo si realmente ya no necesitas acceso.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar cuenta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="display-title text-2xl font-bold text-slate-900">
                ¿Seguro que deseas eliminar tu cuenta?
            </h2>

            <p class="mt-3 text-sm leading-6 text-slate-600">
                Esta operación es irreversible. Ingresa tu contraseña para confirmar la eliminación definitiva de tu cuenta.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Contraseña" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 w-full"
                    placeholder="Contraseña"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
