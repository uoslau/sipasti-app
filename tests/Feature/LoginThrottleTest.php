<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    RateLimiter::clear('login|admin|127.0.0.1');
    RateLimiter::clear('login-ip|127.0.0.1');
});

it('menampilkan pesan biasa pada percobaan gagal pertama', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    $this->post('/login', ['username' => 'admin', 'password' => 'salah'])
        ->assertSessionHasErrors('loginError');

    expect(session('errors')->first('loginError'))->toBe('Username atau password salah.');
});

it('mengunci setelah lima percobaan gagal', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['username' => 'admin', 'password' => 'salah'])
            ->assertSessionHasErrors('loginError');
    }

    $this->post('/login', ['username' => 'admin', 'password' => 'salah']);

    expect(session('errors')->first('loginError'))->toContain('Terlalu banyak percobaan login');
});

it('tetap menolak kredensial benar selama masih terkunci', function () {
    User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', ['username' => 'admin', 'password' => 'salah']);
    }

    $this->post('/login', ['username' => 'admin', 'password' => 'rahasia'])
        ->assertSessionHasErrors('loginError');

    $this->assertGuest();
});

it('mereset hitungan setelah login berhasil', function () {
    $user = User::factory()->create(['username' => 'admin', 'password' => 'rahasia']);

    $this->post('/login', ['username' => 'admin', 'password' => 'salah']);

    $this->post('/login', ['username' => 'admin', 'password' => 'rahasia'])
        ->assertRedirect(route('dashboard.index'));

    $this->assertAuthenticatedAs($user);
    expect(RateLimiter::attempts('login|admin|127.0.0.1'))->toBe(0);
});

it('mengunci per alamat IP walau username berganti-ganti', function () {
    foreach (range(1, 20) as $i) {
        $this->post('/login', ['username' => 'user' . $i, 'password' => 'salah']);
    }

    $this->post('/login', ['username' => 'user-baru', 'password' => 'salah']);

    expect(session('errors')->first('loginError'))->toContain('Terlalu banyak percobaan login');
});

it('menolak input yang bukan teks tanpa error server', function () {
    $this->post('/login', ['username' => ['admin'], 'password' => 'rahasia'])
        ->assertSessionHasErrors('username');
});
