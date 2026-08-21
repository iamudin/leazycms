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

if (!function_exists('disallow_option_key')) {
    /**
     * Daftarkan atau cek key opsi sensitif yang dilarang didaftarkan, dimodifikasi, atau diekspos oleh tenant jika multisite aktif.
     *
     * Cara Penggunaan di template/modules.blade.php:
     * disallow_option_key([
     *     'selected_package',
     *     'payment_status',
     *     'limit_publish',
     *     'disk_space',
     *     ...
     * ]);
     *
     * @param array|string|null $keys Array key untuk mendaftarkan, string key untuk mengecek, atau null untuk mengambil semua.
     * @return array|bool
     */
    function disallow_option_key($keys = null)
    {
        static $cachedKeys = null;

        // Default internal core disallowed keys
        $coreDisallowed = [
            'tenant_id',
            'database_version',
            'admin_path',
            'site_maintenance',
            'app_env',
            'app_debug'
        ];

        // 1. Jika parameter adalah ARRAY: Daftarkan/tambahkan key secara dinamis
        if (is_array($keys)) {
            $current = config('modules.disallow_option_keys', []);
            $formatted = array_map(function ($k) {
                return function_exists('_us') ? _us($k) : strtolower(trim(preg_replace('/[^A-Za-z0-9_]+/', '_', $k), '_'));
            }, $keys);

            $merged = array_values(array_unique(array_merge($current, $formatted)));
            config(['modules.disallow_option_keys' => $merged]);
            $cachedKeys = array_values(array_unique(array_merge($coreDisallowed, $merged)));
            return $cachedKeys;
        }

        // Ambil list lengkap key yang terdaftar
        if ($cachedKeys === null) {
            $dynamic = config('modules.disallow_option_keys', []);
            $cachedKeys = array_values(array_unique(array_merge($coreDisallowed, $dynamic)));
        }

        // 2. Jika parameter NULL: Kembalikan seluruh list array key terlarang
        if ($keys === null) {
            return $cachedKeys;
        }

        // 3. Jika parameter adalah STRING: Cek apakah key tersebut termasuk yang dilarang
        $slugKey = function_exists('_us') ? _us($keys) : strtolower(trim(preg_replace('/[^A-Za-z0-9_]+/', '_', $keys), '_'));
        return in_array($slugKey, $cachedKeys, true);
    }
}
