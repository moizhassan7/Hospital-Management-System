<?php

use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

if (!function_exists('get_setting')) {
    function get_setting($key, $default = null) {
        $settings = Cache::rememberForever('hospital_settings', function () {
            try {
                return Setting::first();
            } catch (\Exception $e) {
                return null;
            }
        });
        
        return $settings ? $settings->$key : $default;
    }
}
