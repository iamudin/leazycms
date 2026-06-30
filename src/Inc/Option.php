<?php
if (!function_exists('tenant')) {
    function tenant($default = null)
    {
        return app()->bound('tenant') ? app('tenant') : $default;
    }
}
if (!function_exists('default_menu')) {
    function default_menu()
    {
        return ['berita','menu','banner','page'];
    }
}
if (!function_exists('get_option')) {
    function get_option($key, $default = null)
    {
        static $options = null;

        // Jika multisite mati, ambil dari config langsung (config() sudah sangat cepat karena hanya akses array)
        if (!config('modules.multisite_enabled')) {
            return config('modules.option.' . $key, $default);
        }

        // Jika multisite aktif, gunakan static cache untuk menghindari hit app('tenant.options') berulang
        if ($options === null && app()->bound('tenant.options') && app()->bound('default.options')) {
            $options = array_merge(app('tenant.options'), app('default.options'));
        }

        if ($options !== null) {
            return $options[$key] ?? $default;
        }

        // Fallback jika belum terikat (singleton belum dipanggil atau masih proses)
        return $default;
    }
}
