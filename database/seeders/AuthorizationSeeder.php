<?php

namespace Database\Seeders;

use App\Models\Authorization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        $authorizations = [
            ['name' => 'Buka Shift', 'password' => Hash::make('123456')],
            ['name' => 'Tutup Shift', 'password' => Hash::make('123456')],
            ['name' => 'Tutup Harian', 'password' => Hash::make('123456')],
        ];

        foreach ($authorizations as $auth) {
            $exists = Authorization::where('name', $auth['name'])->exists();
            if (! $exists) {
                Authorization::create($auth);
            }
        }
    }
}
