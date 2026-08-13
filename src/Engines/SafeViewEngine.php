<?php

namespace Leazycms\Web\Engines;

use Illuminate\Contracts\View\Engine;
use Illuminate\Support\Facades\Log;

class SafeViewEngine implements Engine
{
    /**
     * @var Engine
     */
    protected $originalEngine;

    /**
     * @var Engine
     */
    protected $engine;

    /**
     * Cache memori statis untuk jalur file yang sudah diperiksa dalam 1 request lifecycle.
     *
     * @var array<string, bool>
     */
    protected static $checkedPaths = [];

    /**
     * Cache pola regex gabungan keyword terlarang/backdoor per hash opsi keyword.
     *
     * @var array<string, string>
     */
    protected static $compiledRegexes = [];

    /**
     * Create a new safe view engine instance.
     *
     * @param Engine $originalEngine
     */
    public function __construct(Engine $originalEngine)
    {
        $this->originalEngine = $originalEngine;
        $this->engine = $originalEngine;
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
        // Hanya lakukan pemindaian jika berkas berada dalam direktori template yang sedang aktif (single site & multisite)
        if ($path && $this->isPathInActiveTemplate($path) && file_exists($path)) {
            // Cek dari cache statis request jika jalur file ini sudah pernah diperiksa sebelumnya
            if (isset(self::$checkedPaths[$path])) {
                if (self::$checkedPaths[$path] === true) {
                    return '';
                }
            } else {
                $content = @file_get_contents($path);
                if ($content !== false && $this->containsForbiddenKeyword($content, $matched)) {
                    self::$checkedPaths[$path] = true;
                    Log::warning("SafeViewEngine: Terdeteksi file blade terlarang/berbahaya pada path [{$path}]" . ($matched ? " (keyword: '{$matched}')" : ""));
                    return '';
                }
                self::$checkedPaths[$path] = false;
            }
        }

        return $this->originalEngine->get($path, $data);
    }

    /**
     * Periksa apakah jalur file berada di dalam folder template yang sedang aktif saat ini.
     * Berlaku baik untuk Single Site maupun Multisite.
     *
     * @param  string  $path
     * @return bool
     */
    protected function isPathInActiveTemplate($path)
    {
        // Ambil nama template yang aktif saat ini
        $activeTemplate = function_exists('template') ? template() : null;

        if (empty($activeTemplate) && function_exists('get_option')) {
            $activeTemplate = get_option('template');
        }

        if (empty($activeTemplate)) {
            $activeTemplate = 'default';
        }

        // Normalisasi separator path untuk Windows (\) dan Linux (/)
        $normalizedPath = str_replace('\\', '/', $path);
        $targetSegment = '/template/' . trim($activeTemplate, '/') . '/';

        return stripos($normalizedPath, $targetSegment) !== false;
    }

    /**
     * Periksa apakah konten file blade mengandung keyword terlarang atau signature backdoor.
     *
     * @param  string  $content
     * @param  string|null  $matched
     * @return bool
     */
    protected function containsForbiddenKeyword($content, &$matched = null)
    {
        // Bersihkan komentar Blade {{-- ... --}} agar tidak memicu false positive
        $content = preg_replace('/\{\{--[\s\S]*?--\}\}/', '', $content);

        $extraKeywords = (function_exists('get_option') && get_option('forbidden_keyword'))
            ? (string) get_option('forbidden_keyword')
            : '';

        $cacheKey = md5($extraKeywords);

        if (!isset(self::$compiledRegexes[$cacheKey])) {
            // Daftar fungsi berbahaya yang wajib diikuti kurung buka (
            $dangerousFunctions = [
                'hex2bin',
                'exit',
                'die',
                'eval',
                'phpinfo',
                'exec',
                'system',
                'passthru',
                'shell_exec',
                'proc_open',
                'popen',
                'pcntl_exec',
                'assert',
                'base64_decode',
                'file_put_contents',
                'fopen',
                'unlink',
                'mkdir',
                'rmdir',
                'chmod',
                'chown',
                'curl_exec',
                'create_function',
                'file_get_contents',
                'gzinflate',
                'gzuncompress',
                'str_rot13',
                'readfile',
                'show_source',
                'highlight_file',
                'symlink',
                'copy',
                'move_uploaded_file',
                'ini_set',
                'putenv',
                'call_user_func',
                'call_user_func_array',
            ];

            $patterns = [
                // Signature Webshell & Backdoor Populer
                '\bc99shell\b',
                '\br57shell\b',
                '\bb374k\b',
                '\bwso_version\b',
                '\bFilesMan\b',
                '\bIndoXploit\b',
                '\bAlfa\s+Team\b',
                '\balfa_version\b',
                '\bmarijuana\b',
                '\bsec4ever\b',

                // Sensitive System Path & File Reads
                '\/etc\/passwd',
                '\/etc\/shadow',
                'proc\/self\/environ',

                // Direct Database / Schema / Tenant Isolation Bypass in Blade
                'DB::delete\s*\(',
                'DB::update\s*\(',
                'DB::statement\s*\(',
                'DB::truncate\s*\(',
                'DB::drop\s*\(',
                'DB::raw\s*\(',
                'Schema::drop\s*\(',
                'Schema::dropIfExists\s*\(',
                'Artisan::call\s*\(',
                'withoutGlobalScope\s*\(',
                'withoutGlobalScopes\s*\(',
                'mysqli_query\s*\(',
                'pdo->exec\s*\(',
                'pdo->query\s*\(',

                // SQL Injection & File Dump Signatures
                'UNION\s+SELECT',
                'INTO\s+OUTFILE',
                'LOAD_FILE\s*\(',

                // Dynamic Payload Executions (misal $_POST['cmd']($_GET['arg']))
                '\$_(POST|GET|REQUEST|COOKIE|SERVER)\s*\[.+?\]\s*\(',
            ];

            // Wajib diikuti kurung buka \s*\( agar class/variabel tidak dianggap berbahaya
            foreach ($dangerousFunctions as $fn) {
                $patterns[] = '\b' . preg_quote($fn, '/') . '\s*\(';
            }

            // Tambahkan opsi kustom pengembang jika ada
            if ($extraKeywords !== '') {
                $extra = array_map('trim', explode(',', $extraKeywords));
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

            self::$compiledRegexes[$cacheKey] = '/' . implode('|', array_unique($patterns)) . '/i';
        }

        $compiledRegex = self::$compiledRegexes[$cacheKey];

        if (preg_match($compiledRegex, $content, $matches)) {
            $matched = $matches[0] ?? null;
            return true;
        }

        return false;
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
