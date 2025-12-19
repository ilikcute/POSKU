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

        if (! $user || ! $user->store_id) {
            throw new RuntimeException('Pengguna tidak terhubung ke toko.');
        }

        $deviceIdentifier = DeviceHelper::getDeviceId();

        $station = Station::firstOrCreate(
            [
                'store_id' => $user->store_id,
                'device_identifier' => $deviceIdentifier,
            ],
            [
                'name' => $user->name . ' Station',
                'is_active' => true,
            ]
        );

        $station->forceFill(['last_seen_at' => now()])->save();

        return $station;
    }
}
