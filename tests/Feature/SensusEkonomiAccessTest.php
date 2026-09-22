<?php

use App\Models\TimKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// SEMENTARA — menguji aturan akses fitur Sensus Ekonomi (admin atau tim kerja PEMEJA).
uses(RefreshDatabase::class);

function userInTeam(string $username, string $alias, bool $isAdmin = false): User
{
    $team = TimKerja::forceCreate(['nama_tim_kerja' => 'Tim ' . $alias, 'alias_tim_kerja' => $alias]);
    $user = User::factory()->create(['username' => $username, 'is_admin' => $isAdmin]);
    $user->timKerja()->attach($team->id);

    return $user;
}

it('mengizinkan admin tanpa tim kerja', function () {
    $admin = User::factory()->create(['username' => 'adminx', 'is_admin' => true]);

    $this->actingAs($admin)->get('/sensus-ekonomi')->assertOk();
});

it('mengizinkan anggota PEMEJA yang bukan admin', function () {
    $user = userInTeam('pemeja', 'PEMEJA');

    $this->actingAs($user)->get('/sensus-ekonomi')->assertOk();
});

it('mengizinkan admin yang kebetulan juga di PEMEJA', function () {
    $user = userInTeam('adminpemeja', 'PEMEJA', true);

    $this->actingAs($user)->get('/sensus-ekonomi')->assertOk();
});

it('menolak user dari tim kerja lain', function () {
    $user = userInTeam('ipek', 'IPEK');

    $this->actingAs($user)->get('/sensus-ekonomi')->assertForbidden();
});

it('menolak user tanpa tim kerja', function () {
    $user = User::factory()->create(['username' => 'lajang', 'is_admin' => false]);

    $this->actingAs($user)->get('/sensus-ekonomi')->assertForbidden();
});

it('menolak tamu dan mengarahkan ke login', function () {
    $this->get('/sensus-ekonomi')->assertRedirect('/login');
});

it('menyembunyikan menu sidebar dari user tanpa hak', function () {
    $pemeja = userInTeam('pemeja2', 'PEMEJA');
    $lain = userInTeam('ipek2', 'IPEK');

    $this->actingAs($pemeja)->get('/')->assertSee('Sensus Ekonomi');
    $this->actingAs($lain)->get('/')->assertDontSee('Sensus Ekonomi');
});
