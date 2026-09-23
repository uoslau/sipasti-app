<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('menolak tamu dan mengarahkan ke login', function () {
    $this->get('/profil')->assertRedirect('/login');
});

it('menampilkan halaman profil user yang login', function () {
    $user = User::factory()->create(['name' => 'Nelson Mordehai', 'username' => 'nelson.mordehai']);

    $this->actingAs($user)
        ->get('/profil')
        ->assertOk()
        ->assertSee('Data profil')
        ->assertSee('nelson.mordehai');
});

it('menampilkan menu Profil di sidebar', function () {
    $user = User::factory()->create(['username' => 'sidebar.user']);

    $this->actingAs($user)->get('/')->assertSee('Profil');
});

it('memperbarui data profil', function () {
    $user = User::factory()->create(['name' => 'Nama Lama', 'username' => 'lama']);

    $this->actingAs($user)->put('/profil', [
        'name'     => 'Nama Baru',
        'username' => 'baru',
        'email'    => 'baru@bps.go.id',
    ])->assertRedirect(route('profil.index'));

    $user->refresh();
    expect($user->name)->toBe('Nama Baru')
        ->and($user->username)->toBe('baru')
        ->and($user->email)->toBe('baru@bps.go.id');
});

it('menolak username yang sudah dipakai user lain', function () {
    User::factory()->create(['username' => 'dipakai']);
    $user = User::factory()->create(['username' => 'saya']);

    $this->actingAs($user)->put('/profil', [
        'name'     => $user->name,
        'username' => 'dipakai',
    ])->assertSessionHasErrors('username');
});

it('mengizinkan menyimpan username sendiri tanpa error unik', function () {
    $user = User::factory()->create(['username' => 'tetap']);

    $this->actingAs($user)->put('/profil', [
        'name'     => $user->name,
        'username' => 'tetap',
    ])->assertSessionHasNoErrors();
});

it('mengubah password dengan password saat ini yang benar', function () {
    $user = User::factory()->create(['username' => 'ganti.password', 'password' => 'rahasia']);

    $this->actingAs($user)->put('/profil/password', [
        'current_password'      => 'rahasia',
        'password'              => 'baru12345',
        'password_confirmation' => 'baru12345',
    ])->assertRedirect(route('profil.index'));

    expect(Hash::check('baru12345', $user->fresh()->password))->toBeTrue();
});

it('menolak ganti password bila password saat ini salah', function () {
    $user = User::factory()->create(['username' => 'ganti.password', 'password' => 'rahasia']);

    $this->actingAs($user)->put('/profil/password', [
        'current_password'      => 'salah',
        'password'              => 'baru12345',
        'password_confirmation' => 'baru12345',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('rahasia', $user->fresh()->password))->toBeTrue();
});

it('menolak bila konfirmasi password tidak cocok', function () {
    $user = User::factory()->create(['username' => 'ganti.password', 'password' => 'rahasia']);

    $this->actingAs($user)->put('/profil/password', [
        'current_password'      => 'rahasia',
        'password'              => 'baru12345',
        'password_confirmation' => 'beda12345',
    ])->assertSessionHasErrors('password');
});
