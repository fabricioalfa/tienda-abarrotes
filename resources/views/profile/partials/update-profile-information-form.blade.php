<section>
    <header>
        <h2 class="display-title text-2xl font-bold text-slate-900">
            Información del perfil
        </h2>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            Actualiza tu nombre y correo electrónico manteniendo tus datos principales al día.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nombre" />
            <x-text-input id="name" name="name" type="text" class="mt-1 w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Correo" />
            <x-text-input id="email" name="email" type="email" class="mt-1 w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-3 text-sm text-slate-700">
                        Tu correo electrónico no está verificado.

                        <button form="send-verification" class="ml-1 font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 transition hover:text-blue-900 focus:outline-none">
                            Haz clic aquí para reenviar el correo de verificación.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="notice-success mt-3">
                            Se envió un nuevo enlace de verificación a tu correo.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Guardar</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-slate-500"
                >Guardado.</p>
            @endif
        </div>
    </form>
</section>
