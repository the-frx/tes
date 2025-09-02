<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingsHelper
{
    public static function getAllSettings()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        return $settings;
    }
}
