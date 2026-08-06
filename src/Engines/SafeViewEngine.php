<?php

namespace Leazycms\Web\Engines;

use Illuminate\Contracts\View\Engine;

class SafeViewEngine implements Engine
{
    /**
     * @var Engine
     */
    protected $originalEngine;

    /**
     * Cache memori statis untuk jalur file yang sudah diperiksa dalam 1 request lifecycle.
     *
     * @var array<string, bool>
     */
    protected static $checkedPaths = [];

    /**
     * Cache pola regex gabungan keyword terlarang/backdoor.
     *
     * @var string|null
     */
    protected static $compiledRegex = null;

    /**
     * Create a new safe view engine instance.
     *
     * @param Engine $originalEngine
     */
    public function __construct(Engine $originalEngine)
    {
        $this->originalEngine = $originalEngine;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        if ($path && file_exists($path)) {
            // Cek dari cache statis request jika jalur file ini sudah pernah diperiksa sebelumnya
            if (isset(self::$checkedPaths[$path])) {
                if (self::$checkedPaths[$path] === true) {
                    return '';
                }
            } else {
                $content = @file_get_contents($path);
                if ($content !== false && $this->containsForbiddenKeyword($content)) {
                    self::$checkedPaths[$path] = true;
                    return '';
                }
                self::$checkedPaths[$path] = false;
            }
        }

        return $this->originalEngine->get($path, $data);
    }

    /**
     * Periksa apakah konten file blade mengandung keyword terlarang atau signature backdoor.
     *
     * @param  string  $content
     * @return bool
     */
    protected function containsForbiddenKeyword($content)
    {
        if (self::$compiledRegex === null) {
            // Daftar fungsi berbahaya yang wajib diikuti kurung buka (
            $dangerousFunctions = [
                'hex2bin',
                'exit',
                'eval',
                'phpinfo',
                'exec',
                'system',
                'passthru',
                'shell_exec',
                'proc_open',
                'popen',
                'assert',
                'base64_decode',
                'file_put_contents',
                'fopen',
                'unlink',
                'mkdir',
                'curl_exec',
                'create_function',
                'file_get_contents',
                'delete',
                'update',
                'gzinflate',
            ];

            $patterns = [
                '\bc99shell\b',
                '\br57shell\b',
                '\bb374k\b',
                '\bwso_version\b',
                '\/etc\/passwd',
                '\/etc\/shadow',
                'proc\/self\/environ',
            ];

            // Wajib diikuti kurung buka \s*\( agar class="update" atau class="delete" tidak dianggap berbahaya
            foreach ($dangerousFunctions as $fn) {
                $cleanFn = rtrim($fn, '(');
                $patterns[] = '\b' . preg_quote($cleanFn, '/') . '\s*\(';
            }

            // Tambahkan opsi kustom pengembang jika ada
            if (function_exists('get_option') && get_option('forbidden_keyword')) {
                $extra = array_map('trim', explode(',', get_option('forbidden_keyword')));
                $htmlElements = ['<script', 'javascript:', 'onerror=', 'cmd', 'system', 'exec', '.php', '.env', '.git', '.svn'];
                foreach ($extra as $kw) {
                    if ($kw !== '' && !in_array(strtolower($kw), $htmlElements)) {
                        if (str_ends_with($kw, '(')) {
                            $cleanKw = rtrim($kw, '(');
                            $patterns[] = '\b' . preg_quote($cleanKw, '/') . '\s*\(';
                        } else {
                            $patterns[] = preg_quote($kw, '/');
                        }
                    }
                }
            }

            self::$compiledRegex = '/' . implode('|', array_unique($patterns)) . '/i';
        }

        if (!self::$compiledRegex) {
            return false;
        }

        return (bool) preg_match(self::$compiledRegex, $content);
    }

    /**
     * Pass dynamic method calls to the original engine instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->originalEngine, $method], $parameters);
    }

    /**
     * Dynamically access original engine properties.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->originalEngine->{$key};
    }

    /**
     * Dynamically set original engine properties.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->originalEngine->{$key} = $value;
    }
}
