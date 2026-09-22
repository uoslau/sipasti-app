<form method="POST" action="{{ route('users.store') }}">
    @csrf

    <div class="users-form-section">
        <div class="users-form-section-title"><i class="bx bx-id-card" aria-hidden="true"></i>Identitas akun</div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Nama</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                    value="{{ old('name') }}" placeholder="Nama lengkap" required autofocus />
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username"
                    name="username" value="{{ old('username') }}" placeholder="cth: nelson.mordehai" required />
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Dipakai untuk login.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                    value="{{ old('email') }}" placeholder="nama@bps.go.id" />
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Opsional.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                    name="password" placeholder="Minimal 6 karakter" required />
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="users-form-section">
        <div class="users-form-section-title"><i class="bx bx-shield-quarter" aria-hidden="true"></i>Hak akses</div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" name="is_admin" id="is_admin" value="1"
                {{ old('is_admin') ? 'checked' : '' }} />
            <label class="form-check-label" for="is_admin">Admin, akses penuh ke semua tim kerja</label>
        </div>

        <label class="form-label d-block">Tim kerja</label>
        @include('users.partials.tim-kerja-options', ['selected' => old('tim_kerja_ids', [])])
        <div class="form-text">User hanya dapat mengelola kegiatan dari tim kerja yang dipilih.</div>
    </div>

    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Tambah user</button>
    </div>
</form>
