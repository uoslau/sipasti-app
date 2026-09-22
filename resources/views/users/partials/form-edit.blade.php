<form method="POST" action="{{ route('users.update', $user->id) }}">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Data akun</h5>
        </div>
        <div class="card-body">
            <div class="users-form-section">
                <div class="users-form-section-title"><i class="bx bx-id-card" aria-hidden="true"></i>Identitas akun
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Nama</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name', $user->name) }}" placeholder="Nama lengkap" required
                            autofocus />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username"
                            name="username" value="{{ old('username', $user->username) }}" required />
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Dipakai untuk login.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $user->email) }}" placeholder="nama@bps.go.id" />
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Opsional.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="Biarkan kosong bila tidak diubah" />
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Isi hanya bila ingin mengganti password.</div>
                    </div>
                </div>
            </div>

            <div class="users-form-section">
                <div class="users-form-section-title"><i class="bx bx-shield-quarter" aria-hidden="true"></i>Hak akses
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_admin" id="is_admin"
                        value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} />
                    <label class="form-check-label" for="is_admin">Admin, akses penuh ke semua tim kerja</label>
                </div>

                <label class="form-label d-block">Tim kerja</label>
                @include('users.partials.tim-kerja-options', [
                    'selected' => old('tim_kerja_ids', $user->timKerja->pluck('id')->all()),
                ])
                <div class="form-text">User hanya dapat mengelola kegiatan dari tim kerja yang dipilih.</div>
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted">Terakhir diubah {{ $user->updated_at->format('d M Y, H:i') }}</small>
            <div class="d-flex gap-2">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            </div>
        </div>
    </div>
</form>
