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
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                {{-- Data profil --}}
                <form method="POST" action="{{ route('profil.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Data profil</h5>
                        </div>
                        <div class="card-body">
                            <div class="users-form-section">
                                <div class="users-form-section-title"><i class="bx bx-id-card" aria-hidden="true"></i>Identitas akun
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="name">Nama</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $user->name) }}"
                                            placeholder="Nama lengkap" required autofocus />
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="username">Username</label>
                                        <input type="text" class="form-control @error('username') is-invalid @enderror"
                                            id="username" name="username" value="{{ old('username', $user->username) }}"
                                            required />
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Dipakai untuk login.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $user->email) }}"
                                            placeholder="nama@bps.go.id" />
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Opsional.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <small class="text-muted">Terakhir diubah
                                {{ $user->updated_at?->format('d M Y, H:i') ?? '-' }}</small>
                            <button type="submit" class="btn btn-primary ms-auto">Simpan perubahan</button>
                        </div>
                    </div>
                </form>

                {{-- Tim kerja (hanya lihat) --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Tim kerja</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Keanggotaan tim kerja dikelola oleh admin.</p>
                        @if ($user->timKerja->isEmpty())
                            <span class="users-chip users-chip-empty">Belum ada tim</span>
                        @else
                            <div class="users-chips">
                                @foreach ($user->timKerja as $tk)
                                    <span class="users-chip"
                                        title="{{ $tk->nama_tim_kerja }}">{{ $tk->alias_tim_kerja }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                {{-- Ganti password --}}
                <div class="card users-reset-card">
                    <div class="card-body">
                        <div class="users-form-section-title"><i class="bx bx-lock-alt" aria-hidden="true"></i>Ganti
                            password</div>
                        <p class="text-muted small mb-3">
                            Masukkan password saat ini untuk mengonfirmasi perubahan.
                        </p>
                        <form method="POST" action="{{ route('profil.password') }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label" for="current_password">Password saat ini</label>
                                <input type="password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" required />
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password baru</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Minimal 6 karakter" required />
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password_confirmation">Konfirmasi password baru</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required />
                            </div>
                            <button type="submit" class="btn btn-warning w-100">Simpan password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
