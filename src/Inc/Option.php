<?php
if (!function_exists('tenant')) {
    function tenant($default = null)
    {
        return app()->bound('tenant') ? app('tenant') : $default;
    }
}
if (!function_exists('get_option')) {
    function get_option($key, $default = null)
    {
        if (!config('modules.multisite_enabled')) {
            return config('modules.option.' . $key, $default);
        }

        if (!app()->bound('tenant.options')) {
            return $default;
        }
        $defaultOption = cache()->rememberForever('default_options', function () {
            return \Leazycms\Web\Models\Option::withoutGlobalScope('tenant')->pluck('value', 'name')->toArray();
        });
        return array_merge($defaultOption, app('tenant.options'))[$key] ?? $default;
    }
}