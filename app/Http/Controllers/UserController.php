<?php

namespace App\Http\Controllers;

use App\Models\TimKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $timKerjaFilter = $request->query('tim_kerja');

        $users = User::with('timKerja')
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role === 'admin', fn ($q) => $q->where('is_admin', true))
            ->when($role === 'user', fn ($q) => $q->where('is_admin', false))
            ->when($timKerjaFilter, fn ($q) => $q->whereHas(
                'timKerja',
                fn ($t) => $t->where('tim_kerjas.id', $timKerjaFilter)
            ))
            ->orderBy('is_admin', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'       => User::count(),
            'admin'       => User::where('is_admin', true)->count(),
            'no_team'     => User::doesntHave('timKerja')->count(),
            'teams_used'  => TimKerja::has('user')->count(),
            'teams_total' => TimKerja::count(),
        ];

        return view('users.index', [
            'users'         => $users,
            'tim_kerja'     => TimKerja::select('id', 'nama_tim_kerja', 'alias_tim_kerja')->get(),
            'search'        => $search,
            'role'          => $role,
            'tim_kerja_id'  => $timKerjaFilter,
            'stats'         => $stats,
            'is_filtered'   => (bool) ($search || $role || $timKerjaFilter),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'name'            => 'required|string|max:255',
            'username'        => 'required|string|max:255|unique:users,username',
            'email'           => 'nullable|email|max:255|unique:users,email',
            'password'        => 'required|string|min:6',
            'is_admin'        => 'nullable|boolean',
            'tim_kerja_ids'   => 'nullable|array',
            'tim_kerja_ids.*' => 'exists:tim_kerjas,id',
        ]);

        $user = User::create([
            'name'     => $validated_data['name'],
            'username' => $validated_data['username'],
            'email'    => $validated_data['email'] ?? null,
            'password' => $validated_data['password'],
            'is_admin' => $request->boolean('is_admin'),
        ]);

        $user->timKerja()->sync($validated_data['tim_kerja_ids'] ?? []);

        return to_route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', [
            'user'      => $user,
            'tim_kerja' => TimKerja::select('id', 'nama_tim_kerja', 'alias_tim_kerja')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated_data = $request->validate([
            'name'            => 'required|string|max:255',
            'username'        => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'           => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'        => 'nullable|string|min:6',
            'is_admin'        => 'nullable|boolean',
            'tim_kerja_ids'   => 'nullable|array',
            'tim_kerja_ids.*' => 'exists:tim_kerjas,id',
        ]);

        $is_admin = $request->boolean('is_admin');

        // admin tidak boleh menurunkan status admin akunnya sendiri
        if ($user->id === Auth::id() && ! $is_admin) {
            return back()
                ->withInput()
                ->with('error', 'Anda tidak dapat menurunkan status admin akun Anda sendiri.');
        }

        $user->name     = $validated_data['name'];
        $user->username = $validated_data['username'];
        $user->email    = $validated_data['email'] ?? null;
        $user->is_admin = $is_admin;

        if (! empty($validated_data['password'])) {
            $user->password = $validated_data['password'];
        }

        $user->save();

        $user->timKerja()->sync($validated_data['tim_kerja_ids'] ?? []);

        return to_route('users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return to_route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->isAdmin() && User::where('is_admin', true)->count() <= 1) {
            return to_route('users.index')
                ->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();

        return to_route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }

    /**
     * Reset the password of the specified resource.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated_data = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->password = $validated_data['password'];
        $user->save();

        return to_route('users.edit', $user->id)
            ->with('success', 'Password berhasil direset!');
    }
}
