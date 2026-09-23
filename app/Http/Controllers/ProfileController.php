<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil user yang sedang login.
     */
    public function index()
    {
        $user = Auth::user()->load('timKerja');

        return view('profil.index', [
            'user' => $user,
        ]);
    }

    /**
     * Perbarui data akun (nama, username, email) user yang sedang login.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated_data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->name     = $validated_data['name'];
        $user->username = $validated_data['username'];
        $user->email    = $validated_data['email'] ?? null;
        $user->save();

        return to_route('profil.index')
            ->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Ganti password user yang sedang login.
     */
    public function updatePassword(Request $request)
    {
        $validated_data = $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai!',
        ]);

        $user = Auth::user();
        $user->password = $validated_data['password'];
        $user->save();

        return to_route('profil.index')
            ->with('success', 'Password berhasil diubah!');
    }
}
