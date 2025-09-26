<?php

namespace App\Helpers;

class DeviceHelper
{
    public static function getDeviceId()
    {
        // Simple device fingerprint using user agent and IP
        $userAgent = request()->userAgent();
        $ip = request()->ip();
        return md5($userAgent . $ip);
    }
}
