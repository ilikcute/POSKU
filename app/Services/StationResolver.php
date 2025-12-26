<?php

namespace App\Services;

use App\Helpers\DeviceHelper;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class StationResolver
{
    public static function resolve(): Station
    {
        $user = Auth::user();

        if (! $user) {
            throw new RuntimeException('Pengguna belum login.');
        }

        $deviceIdentifier = DeviceHelper::getDeviceId();

        $station = Station::where('device_identifier', $deviceIdentifier)
            ->first();

        if (! $station) {
            if (! config('posku.station_auto_create')) {
                throw new RuntimeException('Station belum terdaftar. Hubungi admin untuk mendaftarkan device ini.');
            }

            $station = Station::create([
                'device_identifier' => $deviceIdentifier,
                'name' => $user->name . ' Station',
                'is_active' => true,
            ]);
        }

        $station->forceFill(['last_seen_at' => now()])->save();

        return $station;
    }
}
