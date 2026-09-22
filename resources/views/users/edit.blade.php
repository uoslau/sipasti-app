@php
    $palette = ['primary', 'info', 'success', 'warning', 'danger', 'dark'];
    $avatarColor = $palette[abs(crc32($user->username)) % count($palette)];
    $initials = collect(preg_split('/\s+/', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');
@endphp
<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y users-page">
        {{-- Identitas user --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="users-profile">
                <span class="avatar avatar-md" aria-hidden="true">
                    <span class="avatar-initial rounded-circle bg-label-{{ $avatarColor }}">{{ $initials }}</span>
                </span>
                <div>
                    <div class="users-profile-name">{{ $user->name }}</div>
                    <div class="users-profile-handle">{{ '@' . $user->username }}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if ($user->is_admin)
                    <span class="badge bg-label-success"><i class="bx bxs-shield-alt-2 me-1"></i>Admin</span>
                @else
                    <span class="badge bg-label-secondary"><i class="bx bx-user me-1"></i>User</span>
                @endif
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Kembali
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                @include('users.partials.form-edit')
            </div>

            <div class="col-xl-4">
                <div class="card users-reset-card">
                    <div class="card-body">
                        <div class="users-form-section-title"><i class="bx bx-lock-alt" aria-hidden="true"></i>Reset
                            password</div>
                        <p class="text-muted small mb-3">
                            Tetapkan password baru untuk <strong>{{ $user->name }}</strong>. Password ini dipakai pada
                            login berikutnya.
                        </p>
                        <form method="POST" action="{{ route('users.reset_password', $user->id) }}">
                            @csrf
                            <label class="form-label" for="reset_password">Password baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="reset_password" name="password" placeholder="Minimal 6 karakter" required />
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-warning w-100 mt-3">Reset password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
