<?php
// filepath: /home/fabri/Documentos/tienda/app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = User::roles();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(User::roles()))],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        User::create($data);

        return redirect()->route('users.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = User::roles();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::roles()))],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'No se puede eliminar el usuario autenticado.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Usuario eliminado correctamente.');
    }
}