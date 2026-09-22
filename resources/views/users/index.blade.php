<x-layout>
    <div class="container-xxl flex-grow-1 container-p-y users-page">
        {{-- Judul halaman --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="mb-1">Manajemen user</h4>
                <p class="text-muted mb-0">Kelola akun, tim kerja, dan hak akses pengguna SIPASTI.</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bx bx-plus me-1" aria-hidden="true"></i>Tambah user
            </button>
        </div>

        {{-- Ringkasan --}}
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card users-stat h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="users-stat-icon bg-label-primary" aria-hidden="true"><i class="bx bx-group"></i></span>
                        <div>
                            <div class="users-stat-value">{{ $stats['total'] }}</div>
                            <div class="users-stat-label">Total user</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card users-stat h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="users-stat-icon bg-label-success" aria-hidden="true"><i
                                class="bx bxs-shield-alt-2"></i></span>
                        <div>
                            <div class="users-stat-value">{{ $stats['admin'] }}</div>
                            <div class="users-stat-label">Admin</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card users-stat h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="users-stat-icon bg-label-warning" aria-hidden="true"><i class="bx bx-user-x"></i></span>
                        <div>
                            <div class="users-stat-value">{{ $stats['no_team'] }}</div>
                            <div class="users-stat-label">Belum punya tim kerja</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card users-stat h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="users-stat-icon bg-label-info" aria-hidden="true"><i class="bx bx-sitemap"></i></span>
                        <div>
                            <div class="users-stat-value">{{ $stats['teams_used'] }}<span
                                    class="text-muted fs-6">/{{ $stats['teams_total'] }}</span></div>
                            <div class="users-stat-label">Tim kerja terpakai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar user --}}
        <div class="card">
            <div class="card-header users-toolbar">
                <form action="{{ route('users.index') }}" method="GET" class="users-toolbar-form">
                    <label class="visually-hidden" for="users-search">Cari user</label>
                    <div class="users-search">
                        <i class="bx bx-search" aria-hidden="true"></i>
                        <input type="search" id="users-search" name="search" value="{{ $search }}"
                            placeholder="Cari nama, username, atau email" />
                    </div>

                    <label class="visually-hidden" for="users-role">Filter role</label>
                    <select name="role" id="users-role" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua role</option>
                        <option value="admin" @selected($role === 'admin')>Admin</option>
                        <option value="user" @selected($role === 'user')>User</option>
                    </select>

                    <label class="visually-hidden" for="users-tim-kerja">Filter tim kerja</label>
                    <select name="tim_kerja" id="users-tim-kerja" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua tim kerja</option>
                        @foreach ($tim_kerja as $tk)
                            <option value="{{ $tk->id }}" @selected((string) $tim_kerja_id === (string) $tk->id)>
                                {{ $tk->alias_tim_kerja }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if ($is_filtered)
                        <a href="{{ route('users.index') }}" class="btn btn-text-secondary text-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            @if ($users->isEmpty())
                <div class="users-empty">
                    @if ($is_filtered)
                        <i class="bx bx-search-alt" aria-hidden="true"></i>
                        <h6 class="users-empty-title">Tidak ada user yang cocok</h6>
                        <p class="users-empty-text">Ubah kata kunci atau reset filter untuk melihat semua user.</p>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-primary">Reset filter</a>
                    @else
                        <i class="bx bx-group" aria-hidden="true"></i>
                        <h6 class="users-empty-title">Belum ada user</h6>
                        <p class="users-empty-text">Tambahkan akun pengguna untuk mulai mengelola akses SIPASTI.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">Tambah user</button>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover users-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 30%;">User</th>
                                <th scope="col" style="width: 24%;">Email</th>
                                <th scope="col" style="width: 24%;">Tim kerja</th>
                                <th scope="col" class="text-center" style="width: 12%;">Role</th>
                                <th scope="col" class="text-end" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $u)
                                @php
                                    $palette = ['primary', 'info', 'success', 'warning', 'danger', 'dark'];
                                    $avatarColor = $palette[abs(crc32($u->username)) % count($palette)];
                                    $initials = collect(preg_split('/\s+/', trim($u->name)))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                                        ->implode('');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="users-identity">
                                            <span class="avatar avatar-sm" aria-hidden="true">
                                                <span
                                                    class="avatar-initial rounded-circle bg-label-{{ $avatarColor }}">{{ $initials }}</span>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="users-identity-name text-truncate">{{ $u->name }}</div>
                                                <div class="users-identity-handle text-truncate">
                                                    {{ '@' . $u->username }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($u->email)
                                            <a href="mailto:{{ $u->email }}" class="text-body">{{ $u->email }}</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($u->timKerja->isEmpty())
                                            <span class="users-chip users-chip-empty">Belum ada tim</span>
                                        @else
                                            <div class="users-chips">
                                                @foreach ($u->timKerja->take(2) as $tk)
                                                    <span class="users-chip"
                                                        title="{{ $tk->nama_tim_kerja }}">{{ $tk->alias_tim_kerja }}</span>
                                                @endforeach
                                                @if ($u->timKerja->count() > 2)
                                                    <span class="users-chip users-chip-more"
                                                        title="{{ $u->timKerja->pluck('nama_tim_kerja')->implode(', ') }}">
                                                        +{{ $u->timKerja->count() - 2 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($u->is_admin)
                                            <span class="badge bg-label-success"><i
                                                    class="bx bxs-shield-alt-2 me-1"></i>Admin</span>
                                        @else
                                            <span class="badge bg-label-secondary"><i class="bx bx-user me-1"></i>User</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="users-action-btn dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown" aria-expanded="false"
                                                aria-label="Aksi untuk {{ $u->name }}">
                                                <i class="bx bx-dots-vertical-rounded" aria-hidden="true"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item d-flex align-items-center gap-2"
                                                    href="{{ route('users.edit', $u->id) }}">
                                                    <i class="bx bx-edit-alt" aria-hidden="true"></i>Edit
                                                </a>
                                                <hr class="dropdown-divider" />
                                                <form action="{{ route('users.destroy', $u->id) }}" method="POST"
                                                    class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        class="dropdown-item btn-delete-user d-flex align-items-center gap-2">
                                                        <i class="bx bx-trash" aria-hidden="true"></i>Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3 border-top">
                    <small class="text-muted">
                        Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
                    </small>
                    <div class="pagination pagination-sm mb-0">
                        {{ $users->onEachSide(0)->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal tambah user --}}
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah user</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        @include('users.create')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>

<script src="{{ asset('js/confirm-delete-user.js') }}"></script>
