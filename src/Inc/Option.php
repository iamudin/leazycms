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

        // 1. Ambil dari database / runtime config
        if (!config('modules.multisite_enabled')) {
            $val = config('modules.option.' . $key);
            if ($val !== null && $val !== '') {
                return $val;
            }
        } else {
            if ($options === null && app()->bound('tenant.options') && app()->bound('default.options')) {
                $options = array_merge(app('default.options'), app('tenant.options'));
            }
            if ($options !== null && isset($options[$key]) && $options[$key] !== '') {
                return $options[$key];
            }
        }

        if ($default !== null) {
            return $default;
        }

        // 2. Fallback otomatis ke default yang didefinisikan di add_option()
        $allGroups = config('modules.config.option', []);
        $targetSlug = function_exists('_us') ? _us($key) : strtolower(trim(preg_replace('/[^A-Za-z0-9_]+/', '_', $key), '_'));
        foreach ($allGroups as $group) {
            if (is_array($group)) {
                foreach ($group as $item) {
                    if (is_array($item) && isset($item[0])) {
                        $itemSlug = function_exists('_us') ? _us($item[0]) : strtolower(trim(preg_replace('/[^A-Za-z0-9_]+/', '_', $item[0]), '_'));
                        if ($itemSlug === $targetSlug && isset($item[1])) {
                            if (is_array($item[1]) && array_key_exists('default', $item[1])) {
                                return $item[1]['default'];
                            } elseif (is_object($item[1]) && isset($item[1]->default)) {
                                return $item[1]->default;
                            } elseif (isset($item[2]) && is_string($item[2])) {
                                return $item[2];
                            }
                        }
                    }
                }
            }
        }

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
            'app_debug',
            'allow_manage_user',
            'allow_park_domain',
            'max_image_width'
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
