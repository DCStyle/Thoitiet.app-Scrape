<?php

use App\Models\FooterSetting;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                \App\Models\Setting::updateOrCreate(
                    ['key' => $k],
                    ['value' => $v]
                );
            }
            return true;
        }

        $setting = \App\Models\Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}

if (!function_exists('footer_setting')) {
    function footer_setting($key, $default = null)
    {
        return FooterSetting::where('key', $key)->value('value') ?? $default;
    }
}