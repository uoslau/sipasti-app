<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Batas percobaan gagal untuk satu kombinasi username + IP.
     */
    private const MAX_ATTEMPTS_PER_ACCOUNT = 5;

    /**
     * Batas percobaan gagal untuk satu alamat IP, menahan percobaan yang mengganti-ganti username.
     */
    private const MAX_ATTEMPTS_PER_IP = 20;

    /**
     * Lama hitungan percobaan gagal sebelum direset.
     */
    private const ACCOUNT_DECAY_SECONDS = 60;

    private const IP_DECAY_SECONDS = 300;

    public function index()
    {
        return view('login.index');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $accountKey = $this->accountThrottleKey($credentials['username'], $request->ip());
        $ipKey = $this->ipThrottleKey($request->ip());

        if (RateLimiter::tooManyAttempts($accountKey, self::MAX_ATTEMPTS_PER_ACCOUNT)) {
            return $this->lockoutResponse($accountKey);
        }

        if (RateLimiter::tooManyAttempts($ipKey, self::MAX_ATTEMPTS_PER_IP)) {
            return $this->lockoutResponse($ipKey);
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($accountKey);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard.index'));
        }

        RateLimiter::hit($accountKey, self::ACCOUNT_DECAY_SECONDS);
        RateLimiter::hit($ipKey, self::IP_DECAY_SECONDS);

        return back()->withErrors([
            'loginError' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function accountThrottleKey(string $username, ?string $ip): string
    {
        return 'login|' . Str::transliterate(Str::lower($username)) . '|' . $ip;
    }

    private function ipThrottleKey(?string $ip): string
    {
        return 'login-ip|' . $ip;
    }

    private function lockoutResponse(string $key)
    {
        return back()->withErrors([
            'loginError' => 'Terlalu banyak percobaan login. Coba lagi dalam '
                . $this->humanizeSeconds(RateLimiter::availableIn($key)) . '.',
        ])->onlyInput('username');
    }

    private function humanizeSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' detik';
        }

        return (int) ceil($seconds / 60) . ' menit';
    }
}
