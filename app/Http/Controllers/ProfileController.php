<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Un administrador puede cambiar su contrasena desde aqui, pero no
        // borrarse a si mismo si es el unico: dejaria el sistema sin acceso.
        // El error va bajo la clave 'password' porque es la unica que el modal
        // de borrado muestra (resources/views/profile/partials/delete-user-form.blade.php).
        if ($user->isLastAdmin()) {
            return Redirect::route('profile.edit')->withErrors([
                'password' => 'Eres el unico administrador. Crea otro usuario administrador antes de eliminar tu cuenta.',
            ], 'userDeletion');
        }

        // `sales.user_id` y `cash_registers.opened_by` son ON DELETE RESTRICT:
        // borrar un usuario con historial falla a nivel de base de datos.
        $blockers = $user->deletionBlockers();

        if ($blockers !== []) {
            return Redirect::route('profile.edit')->withErrors([
                'password' => 'No se puede eliminar tu cuenta porque tiene '.implode(' y ', $blockers)
                    .'. Tu usuario debe conservarse para que el historial de ventas sea trazable.',
            ], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
