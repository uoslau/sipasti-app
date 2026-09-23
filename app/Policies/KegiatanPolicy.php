<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\User;

class KegiatanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Kegiatan $kegiatan): bool
    {
        // semua user boleh melihat detail kegiatan, termasuk milik tim kerja lain
        return true;
    }

    public function create(User $user): bool
    {
        return ! empty($user->timKerjaIds());
    }

    public function update(User $user, Kegiatan $kegiatan): bool
    {
        return $this->owns($user, $kegiatan);
    }

    public function delete(User $user, Kegiatan $kegiatan): bool
    {
        return $this->owns($user, $kegiatan);
    }

    private function owns(User $user, Kegiatan $kegiatan): bool
    {
        return in_array($kegiatan->tim_kerja_id, $user->timKerjaIds());
    }
}
