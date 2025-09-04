<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'is_admin' => 1,
        ]);

        $emails = [
            'alfrince.hulu@bps.go.id',
            'anugrah.marbun@bps.go.id',
            'brillian.jeremia@bps.go.id',
            'desman@bps.go.id',
            'eliaman.zebua@bps.go.id',
            'eliyudin@bps.go.id',
            'elviranaftalias@bps.go.id',
            'faberlius.hulu@bps.go.id',
            'geni.harefa@bps.go.id',
            'gregorius.gulo@bps.go.id',
            'gusnaini@bps.go.id',
            'hamdanas-pppk@bps.go.id',
            'hiskia.harefa@bps.go.id',
            'kartika.halawa@bps.go.id',
            'kurniaman.harefa@bps.go.id',
            'nelson.mordehai@bps.go.id',
            'puguh@bps.go.id',
            'restuwz@bps.go.id',
            'richard.putra@bps.go.id',
            'sonazaro@bps.go.id',
            'trisno.harefa@bps.go.id',
            'wisni@bps.go.id',
            'yuris.tz@bps.go.id',
            'yusnidar.zebua@bps.go.id',
        ];

        $userTimKerjaMap = [
            'yuris.tz'       => 1,
            'gregorius.gulo' => 2,
            'faberlius.hulu' => 3,
            'geni.harefa'    => 5,
            'anugrah.marbun' => 6,
            'alfrince.hulu'  => 7,
            'hiskia.harefa'  => 8,
            'kurniaman.harefa' => 9,
            'trisno.harefa'  => 10,
        ];

        // Loop untuk membuat setiap user
        foreach ($emails as $email) {
            // 1. Ekstrak username dari email
            $username = explode('@', $email)[0];

            // 2. Buat nama dari username (ganti '.' dan '-' dengan spasi)
            $name = ucwords(str_replace(['.', '-'], ' ', $username));

            // 3. Buat user menggunakan factory
            $user = User::factory()->create([
                'name'              => $name,
                'username'          => $username,
                'email'             => $email,
                'email_verified_at' => now(),
                'password'          => Hash::make($username),
                'is_admin'          => 0,
            ]);

            if (isset($userTimKerjaMap[$username])) {
                // Jika ada, hubungkan user dengan tim kerja yang sesuai
                $timKerjaId = $userTimKerjaMap[$username];
                $user->timKerja()->attach($timKerjaId);
            }
        }
    }
}
