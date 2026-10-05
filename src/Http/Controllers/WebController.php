<?php
namespace Leazycms\Web\Http\Controllers;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Leazycms\Web\Models\Category;
use Leazycms\Web\Models\PollingResponse;
use Leazycms\Web\Models\PollingTopic;
use Leazycms\Web\Models\Post;
use Leazycms\Web\Models\Tag;
use Leazycms\Web\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WebController extends Controller
{

    public function pollingsubmit(Request $request)
    {
        $request->validate([
            'topic' => 'required|integer',
            'answer' => 'required|integer',
        ]);

        $polling = PollingTopic::select('id', 'keyword', 'duration')->find((int) $request->topic);
        if ($polling && empty($request->cookie('polling_' . $request->keyword))) {
            PollingResponse::create([
                'polling_option_id' => (int) $request->answer,
                'ip' => $request->ip(),
                'reference' => mb_substr(strip_tags((string) $request->headers->get('referer')), 0, 255),
            ]);
            $cookieName = 'polling_' . $polling->keyword;
            $cookieValue = (string) (int) $request->answer;
            $duration = (int) ($polling->duration ?: 1440);

            // Set cookie in request so polling_form can read it
            $request->cookies->set($cookieName, $cookieValue);

            $html = polling_form($polling->keyword);

            return response($html)
                ->cookie($cookieName, $cookieValue, $duration);
        }
    }

    public function formSubmit(Request $request, $module)
    {
        $mod = get_module($module);
        if (!$mod) {
            abort(404, 'Modul tidak ditemukan.');
        }

        // 1. Anti-Spam Honeypot check
        if ($request->filled('_lz_hp')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil dikirim.'
                ]);
            }
            return back()->with('success', 'Data berhasil dikirim.');
        }

        // 1b. Fast submission bot check (human takes at least 1 second)
        if ($request->filled('_lz_ts')) {
            $ts = (int) $request->input('_lz_ts');
            if ($ts > 0 && (time() - $ts) < 1) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data berhasil dikirim.'
                    ]);
                }
                return back()->with('success', 'Data berhasil dikirim.');
            }
        }

        // 2. Dangerous file extensions blacklist & upload safety check
        $dangerousExts = ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'cgi', 'pl', 'exe', 'sh', 'bash', 'bat', 'cmd', 'py', 'svg', 'html', 'htm', 'js', 'jar', 'vbs', 'asp', 'aspx', 'jsp', 'shtml', 'htaccess', 'htpasswd', 'env'];
        $checkFileSafety = function ($file) use ($dangerousExts) {
            if (!$file || !$file->isValid())
                return false;
            $ext = strtolower($file->getClientOriginalExtension());
            $guessedExt = strtolower($file->guessExtension() ?? '');
            if (in_array($ext, $dangerousExts) || in_array($guessedExt, $dangerousExts)) {
                return false;
            }
            if ($file->getSize() > 10 * 1024 * 1024) { // 10MB limit
                return false;
            }
            return true;
        };

        // 3. Dynamic Validation
        $rules = [];
        $messages = [];

        $isTitleAsIdActive = !empty($mod->form->title_as_id) || $request->filled('_title_as_id');

        // Captcha validation if required
        $isCaptchaRequired = !empty($mod->form->captcha) || $request->input('_captcha') === '1' || $request->has('captcha');
        if ($isCaptchaRequired) {
            $rules['captcha'] = [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!function_exists('captcha_check') || !captcha_check($value)) {
                        $fail('Kode captcha tidak valid atau sudah kedaluwarsa.');
                    }
                }
            ];
            $messages['captcha.required'] = 'Kode captcha wajib diisi.';
        }

        // Title validation if present in request and title_as_id is NOT active
        if ($request->has('title') && !$isTitleAsIdActive) {
            $rules['title'] = 'required|string|max:255';
            $messages['title.required'] = ($mod->datatable->data_title ?? 'Judul') . ' wajib diisi.';
        }

        // Module custom_field validation
        $adminOnlyFields = [];
        $richTextFields = [];
        if (!empty($mod->form->custom_field)) {
            foreach (custom_field_without_break($mod->form->custom_field) as $row) {
                $label = $row[0];
                $meta = $row[1] ?? null;
                $k = _us($label);

                $isAdminOnly = (is_array($meta) && isset($meta['public']) && $meta['public'] === false)
                    || (is_object($meta) && isset($meta->public) && $meta->public === false)
                    || (is_array($meta) && (!empty($meta['admin_only']) || !empty($meta['hide_public'])))
                    || (is_object($meta) && (!empty($meta->admin_only) || !empty($meta->hide_public)));

                if ($isAdminOnly) {
                    $adminOnlyFields[] = $k;
                    continue; // Skip validation for admin-only fields in public submission
                }

                $isRequired = is_array($meta) ? (!empty($meta['required'])) : (is_object($meta) ? (!empty($meta->required)) : false);
                $type = is_array($meta) ? ($meta['type'] ?? 'text') : (is_object($meta) ? ($meta->type ?? 'text') : 'text');

                if ($type === 'rich-text' || $type === 'editor') {
                    $richTextFields[] = $k;
                }

                $fieldInputKey = $request->has("field.{$k}") || $request->hasFile("field.{$k}") ? "field.{$k}" : $k;

                if ($isRequired) {
                    if (!$request->hasFile($fieldInputKey) && !$request->hasFile("field.{$k}") && !$request->hasFile($k)) {
                        $rules[$fieldInputKey] = 'required';
                        $messages["{$fieldInputKey}.required"] = $label . ' wajib diisi.';
                    }
                }

                if ($type === 'email' && ($request->filled($fieldInputKey) || $request->filled("field.{$k}"))) {
                    $keyToValidate = $request->filled("field.{$k}") ? "field.{$k}" : $fieldInputKey;
                    $rules[$keyToValidate] = ($rules[$keyToValidate] ?? 'nullable') . '|email|max:255';
                    $messages["{$keyToValidate}.email"] = 'Format ' . $label . ' tidak valid.';
                }

                if ($type === 'file') {
                    $mime = is_array($meta) ? ($meta['mime_type'] ?? null) : (is_object($meta) ? ($meta->mime_type ?? null) : null);
                    $fileRules = ($isRequired ? 'required|' : 'nullable|') . 'file|max:10240';
                    if ($mime) {
                        $fileRules .= '|mimetypes:' . $mime;
                    }
                    if ($request->hasFile("field.{$k}")) {
                        $rules["field.{$k}"] = $fileRules;
                        $messages["field.{$k}.mimetypes"] = 'Format berkas ' . $label . ' tidak didukung.';
                        $messages["field.{$k}.max"] = 'Ukuran berkas ' . $label . ' maksimal 10MB.';
                    } elseif ($request->hasFile($k)) {
                        $rules[$k] = $fileRules;
                        $messages["{$k}.mimetypes"] = 'Format berkas ' . $label . ' tidak didukung.';
                        $messages["{$k}.max"] = 'Ukuran berkas ' . $label . ' maksimal 10MB.';
                    }
                }
            }
        }

        if (!empty($rules)) {
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $validator->errors()->first(),
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }
        }

        // 4. Sanitization closure for XSS prevention
        $sanitizeValue = function ($val, $isRich = false) use (&$sanitizeValue) {
            if (is_array($val)) {
                return array_map(fn($item) => $sanitizeValue($item, $isRich), $val);
            }
            if (!is_string($val)) {
                return $val;
            }
            // Hapus null byte
            $val = str_replace("\0", '', $val);
            if ($isRich) {
                $clean = function_exists('clean_summernote_content') ? clean_summernote_content($val) : $val;
                $allowed = '<div><sub><sup><small><h1><h2><h3><h4><h5><h6><p><s><strike><b><i><u><strong><em><ul><ol><li><br><hr><img><a><iframe><figcaption><figure><blockquote><quote><table><thead><tbody><tfoot><tr><th><td><span>';
                $clean = strip_tags($clean, $allowed);
                $clean = preg_replace('/\s+on[a-z0-9_-]+\s*=\s*(["\'][^"\']*["\']|[^\s>]+)/is', '', $clean);
                $clean = preg_replace('/\s+(href|src)\s*=\s*["\']\s*(?:javascript|vbscript|data\s*:\s*text\/html)[^"\']*["\']/is', ' $1="#"', $clean);
                return $clean;
            }
            // Bersihkan seluruh tag HTML dan karakter kontrol ASCII
            $val = strip_tags($val);
            $val = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $val);
            return trim($val);
        };

        // 5. Status, HMAC Signature & Form Tampering Protection
        $status = 'publish';
        $redirectUrl = null;
        $titleField = null;
        $titleAsId = null;
        $signedExceptKeys = [];

        if ($request->filled('_lz_sig')) {
            $candidateStatus = $request->input('_status', 'publish');
            if (!in_array($candidateStatus, ['publish', 'draft', 'unpublish'])) {
                $candidateStatus = 'publish';
            }
            $candidateRedirect = $request->input('_redirect');
            $candidateTitleField = $request->input('_title_field');
            $candidateExcept = $request->input('_except', '');
            $candidateTitleAsId = $request->input('_title_as_id', '');
            $candidateCaptcha = $request->input('_captcha', '0');

            // Signature checking: dukung signature dengan captcha, dengan _title_as_id, maupun signature legacy
            $sigPayloadWithCaptcha = $module . '|' . $candidateStatus . '|' . ($candidateTitleField ?? '') . '|' . ($candidateRedirect ?? '') . '|' . $candidateExcept . '|' . $candidateTitleAsId . '|' . $candidateCaptcha;
            $sigPayloadWithTitleId = $module . '|' . $candidateStatus . '|' . ($candidateTitleField ?? '') . '|' . ($candidateRedirect ?? '') . '|' . $candidateExcept . '|' . $candidateTitleAsId;
            $sigPayloadLegacy = $module . '|' . $candidateStatus . '|' . ($candidateTitleField ?? '') . '|' . ($candidateRedirect ?? '') . '|' . $candidateExcept;

            $expectedSigWithCaptcha = hash_hmac('sha256', $sigPayloadWithCaptcha, config('app.key'));
            $expectedSigNew = hash_hmac('sha256', $sigPayloadWithTitleId, config('app.key'));
            $expectedSigLegacy = hash_hmac('sha256', $sigPayloadLegacy, config('app.key'));

            $receivedSig = (string) $request->input('_lz_sig');

            if (hash_equals($expectedSigWithCaptcha, $receivedSig) || hash_equals($expectedSigNew, $receivedSig) || hash_equals($expectedSigLegacy, $receivedSig)) {
                $status = $candidateStatus;
                $redirectUrl = $candidateRedirect;
                $titleField = $candidateTitleField;
                if (!empty($candidateTitleAsId)) {
                    $decoded = json_decode($candidateTitleAsId, true);
                    $titleAsId = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $candidateTitleAsId;
                }
                if (!empty($candidateExcept)) {
                    $signedExceptKeys = array_filter(array_map(fn($k) => _us($k), explode(',', $candidateExcept)));
                }
            } else {
                // Signature tidak valid / manipulasi terdeteksi: paksa status draft demi keamanan
                $status = 'draft';
                $redirectUrl = null;
                $titleField = null;
                $titleAsId = null;
            }
        }

        // Module config title_as_id fallback
        if (empty($titleAsId) && !empty($mod->form->title_as_id)) {
            $titleAsId = $mod->form->title_as_id;
        }

        // Open Redirect Prevention: hanya izinkan redirect jika host sama atau relative path
        if ($redirectUrl) {
            $parsed = parse_url($redirectUrl);
            $appHost = parse_url(url('/'), PHP_URL_HOST);
            if (!empty($parsed['host']) && $parsed['host'] !== $appHost) {
                $redirectUrl = null;
            }
        }

        // 6. Determine Title & Slug with strict XSS sanitization
        $title = null;

        if (!empty($titleAsId)) {
            // Generate title otomatis sebagai ID unik (no pendaftaran): [KODE MODUL]-ddmmyyyy-[4 karakter acak]
            $title = function_exists('generate_post_title_id')
                ? generate_post_title_id($module, $titleAsId)
                : (strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $module)) . '-' . now()->format('dmY') . '-' . strtoupper(Str::random(4)));
        } else {
            $title = $request->input('title');

            if (empty($title) && !empty($titleField)) {
                $cleanTitleKey = _us($titleField);
                $title = $request->input("field.{$cleanTitleKey}")
                    ?? $request->input("data_field.{$cleanTitleKey}")
                    ?? $request->input($cleanTitleKey);
            }

            if (empty($title)) {
                $fieldInputs = array_merge($request->input('field', []), $request->input('data_field', []));
                foreach (['nama_lengkap', 'nama', 'name', 'full_name', 'judul', 'title', 'subject', 'instansi', 'lembaga'] as $candidate) {
                    if (!empty($fieldInputs[$candidate])) {
                        $title = $fieldInputs[$candidate];
                        break;
                    }
                }
            }

            $title = strip_tags(trim((string) $title));
            $title = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $title);
            $title = mb_substr($title, 0, 200);

            if (empty($title)) {
                $title = ($mod->title ?? ucfirst($module)) . ' - ' . now()->format('d/m/Y H:i');
            }
        }

        $baseSlug = Str::slug($title);
        if (empty($baseSlug)) {
            $baseSlug = Str::slug($module . '-' . Str::random(5));
        }
        $slug = $baseSlug;
        if (Post::onType($module)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . strtolower(Str::random(5));
        }

        $tenantId = null;
        if (config('modules.multisite_enabled') && function_exists('tenant') && app()->has('tenant')) {
            $t = tenant();
            $tenantId = $t?->id ?? (is_array($t) ? ($t['id'] ?? null) : null);
            if ($tenantId !== null) {
                $tenantId = (int) $tenantId;
            }
        }
        $userId = Auth::id() ?? (User::where('level', 'admin')->value('id') ?? 1);

        $categoryId = $request->filled('category_id') && is_numeric($request->input('category_id')) ? (int) $request->input('category_id') : null;
        $parentId = $request->filled('parent_id') && is_numeric($request->input('parent_id')) ? (int) $request->input('parent_id') : null;

        // 7. Create Post Record
        $post = new Post();
        $post->type = $module;
        $post->title = $title;
        $post->slug = $slug;
        $post->url = $module != 'page' ? $module . '/' . $slug : $slug;
        $post->status = $status;
        $post->user_id = $userId;
        $post->category_id = $categoryId;
        $post->parent_id = $parentId;
        $post->description = $request->input('description') ? mb_substr(strip_tags((string) $request->input('description')), 0, 500) : null;

        // Sanitasi konten
        $rawContent = (string) $request->input('content');
        $post->content = !empty($rawContent) ? $sanitizeValue($rawContent, true) : null;

        $post->shortcut = Str::random(6);
        $post->allow_comment = 'N';
        $post->pinned = 'N';
        if ($tenantId) {
            $post->tenant_id = $tenantId;
        }
        $post->save();

        // 8. Handle Thumbnail / Media with File Security
        if ($request->hasFile('media')) {
            $mediaFile = $request->file('media');
            if ($checkFileSafety($mediaFile)) {
                $post->media = $post->addFile([
                    'file' => $mediaFile,
                    'purpose' => 'thumbnail',
                    'width' => 1200,
                    'mime_type' => ['image/png', 'image/jpeg', 'image/webp', 'image/gif'],
                ]);
                $post->save();
            }
        }

        // 9. Collect and Sanitize Key-Value data_field
        $dataField = [];

        // A. Dari field[...] atau data_field[...]
        $fieldInputs = array_merge(
            $request->input('field', []),
            $request->input('data_field', [])
        );
        foreach ($fieldInputs as $k => $v) {
            $cleanKey = _us($k);
            if (empty($cleanKey))
                continue;
            $isRich = in_array($cleanKey, $richTextFields);
            $dataField[$cleanKey] = $sanitizeValue($v, $isRich);
        }

        // B. Dari upload file di field[...] atau data_field[...]
        $fileFields = array_merge(
            $request->file('field', []),
            $request->file('data_field', [])
        );
        foreach ($fileFields as $k => $file) {
            $cleanKey = _us($k);
            if ($file && $checkFileSafety($file)) {
                $dataField[$cleanKey] = $post->addFile([
                    'file' => $file,
                    'purpose' => $cleanKey,
                    'mime_type' => explode(',', allow_mime()),
                ]);
            }
        }

        // C. Dari flat inputs yang cocok dengan custom_field modul
        if (!empty($mod->form->custom_field)) {
            foreach (custom_field_without_break($mod->form->custom_field) as $row) {
                $cleanKey = _us($row[0]);
                $meta = $row[1] ?? null;
                $type = is_array($meta) ? ($meta['type'] ?? 'text') : (is_object($meta) ? ($meta->type ?? 'text') : 'text');

                if ($type === 'file') {
                    if ($request->hasFile($cleanKey)) {
                        $file = $request->file($cleanKey);
                        if ($file && $checkFileSafety($file)) {
                            $mime = is_array($meta) ? ($meta['mime_type'] ?? null) : (is_object($meta) ? ($meta->mime_type ?? null) : null);
                            $dataField[$cleanKey] = $post->addFile([
                                'file' => $file,
                                'purpose' => $cleanKey,
                                'mime_type' => explode(',', $mime ?? allow_mime()),
                            ]);
                        }
                    }
                } else {
                    if ($request->has($cleanKey) && !isset($dataField[$cleanKey])) {
                        $val = $request->input($cleanKey);
                        $isRich = ($type === 'rich-text' || $type === 'editor');
                        $dataField[$cleanKey] = $sanitizeValue($val, $isRich);
                    }
                }
            }
        }

        // Hapus field yang dikhususkan untuk admin atau dikecualikan di form agar tidak dapat diinjeksi oleh publik
        $allExcludedKeys = array_unique(array_merge($adminOnlyFields, $signedExceptKeys));
        foreach ($allExcludedKeys as $exKey) {
            unset($dataField[$exKey]);
        }
        unset($dataField['captcha'], $dataField['_captcha']);

        // Jika title_as_id aktif dan ada field no_pendaftaran pada modul atau form, sinkronkan
        if (!empty($titleAsId)) {
            foreach (['no_pendaftaran', 'nomor_pendaftaran', 'kode_pendaftaran', 'id_pendaftaran'] as $regCandidate) {
                if (empty($dataField[$regCandidate])) {
                    $dataField[$regCandidate] = $title;
                }
            }
        }

        $post->data_field = $dataField;
        $post->save();

        // 10. Internal Notification for Admin
        try {
            if (class_exists(\Leazycms\Web\Models\Notification::class)) {
                \Leazycms\Web\Models\Notification::create([
                    'title' => 'Entri Form Baru: ' . ($mod->title ?? ucfirst($module)),
                    'message' => 'Data "' . Str::limit($post->title, 40) . '" berhasil dikirim via formulir publik.',
                    'type' => $module,
                    'url' => admin_url($module . '/' . $post->id . '/edit'),
                    'is_read' => false,
                    'user_id' => null,
                ]);
            }
        } catch (\Throwable $e) {
        }

        // 11. Safe Response
        $rawSuccessMsg = (string) $request->input('_success_message', 'Data berhasil disimpan.');
        $rawSuccessMsg = str_replace(
            ['{title}', '{id}', '{no_pendaftaran}', '{nomor_pendaftaran}'],
            $post->title,
            $rawSuccessMsg
        );
        $successMsg = strip_tags(trim($rawSuccessMsg));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $successMsg,
                'redirect' => $redirectUrl,
                'data' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'data_field' => $post->data_field
                ]
            ]);
        }

        if ($redirectUrl) {
            return redirect($redirectUrl)
                ->with('success', $successMsg)
                ->with('post_title', $post->title)
                ->with('post_id', $post->id);
        }

        return back()
            ->with('success', $successMsg)
            ->with('post_title', $post->title)
            ->with('post_id', $post->id);
    }

    public function track(Request $request, $module)
    {
        $mod = get_module($module);
        if (!$mod) {
            abort(404, 'Modul tidak ditemukan.');
        }

        $keyword = trim((string) ($request->input('q') ?? $request->input('keyword') ?? $request->input('nik') ?? $request->input('nip') ?? ''));
        // SQLi & DoS Hardening: filter null bytes dan batasi panjang karakter
        $keyword = str_replace("\0", '', $keyword);
        $keyword = mb_substr(strip_tags($keyword), 0, 100);

        $byParam = $request->input('by') ?? $request->input('field');
        $showParam = $request->input('show');

        if (empty($keyword)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'found' => false,
                    'message' => 'Silakan masukkan kata kunci pencarian.'
                ], 422);
            }
            return back()->with('track_error', 'Silakan masukkan kata kunci pencarian.');
        }

        // Tentukan field-field untuk dicari dengan whitelist sanitasi [a-zA-Z0-9_]
        $searchFields = [];
        if (!empty($byParam)) {
            $searchFields = is_array($byParam) ? $byParam : explode(',', $byParam);
        } else {
            $searchFields = ['nik', 'nip', 'nomor_nip_nuptk', 'nisn', 'nomor_pendaftaran', 'no_pendaftaran', 'kode_pendaftaran', 'no_wa', 'no_kontak_whatsapp', 'email'];
            if (!empty($mod->form->custom_field)) {
                foreach (custom_field_without_break($mod->form->custom_field) as $cf) {
                    $k = _us($cf[0]);
                    if (str_contains($k, 'nik') || str_contains($k, 'nip') || str_contains($k, 'nisn') || str_contains($k, 'pendaftaran') || str_contains($k, 'nomor') || str_contains($k, 'wa') || str_contains($k, 'kode') || str_contains($k, 'identitas') || str_contains($k, 'ktp') || str_contains($k, 'kk')) {
                        $searchFields[] = $k;
                    }
                }
            }
        }

        // SQLi Prevention: Hanya izinkan karakter alfanumerik dan underscore untuk key JSON
        $searchFields = array_unique(array_filter(array_map(function ($f) {
            return preg_replace('/[^a-zA-Z0-9_]/', '', (string) $f);
        }, $searchFields)));

        // Query ke tabel posts menggunakan parameter binding
        $query = Post::onType($module)->published();
        if (config('modules.multisite_enabled') && function_exists('tenant') && tenant()) {
            $t = tenant();
            $tId = $t?->id ?? (is_array($t) ? ($t['id'] ?? null) : null);
            if ($tId) {
                $query->where('tenant_id', (int) $tId);
            }
        }

        $query->where(function ($q) use ($searchFields, $keyword) {
            foreach ($searchFields as $sf) {
                if (!empty($sf)) {
                    $q->orWhere('data_field->' . $sf, $keyword);
                }
            }
            // Sertakan juga pencarian pada judul post
            $q->orWhere('title', $keyword);
        });

        $post = $query->latest()->first();

        if (!$post) {
            $msg = 'Kata kunci "' . $keyword . '" tidak ditemukan. Pastikan data yang dimasukkan sudah sesuai.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'found' => false,
                    'message' => $msg
                ]);
            }
            return back()->with('track_error', $msg)->withInput();
        }

        // Tentukan status text dari data_field atau status post
        $statusText = null;
        if (!empty($post->data_field)) {
            foreach (['status_pendaftaran', 'status_seleksi', 'status_kepegawaian', 'status', 'keterangan_status', 'hasil_seleksi'] as $stCandidate) {
                if (!empty($post->data_field[$stCandidate])) {
                    $statusText = $post->data_field[$stCandidate];
                    break;
                }
            }
        }
        if (empty($statusText)) {
            $statusText = ucfirst($post->status ?? 'Publish');
        }

        // Susun mapping label agar tampilan rapi
        $fieldsData = [];
        $allowedShowKeys = !empty($showParam) ? (is_array($showParam) ? array_map(fn($s) => _us($s), $showParam) : array_map(fn($s) => _us($s), explode(',', $showParam))) : null;

        $labelMap = [];
        if (!empty($mod->form->custom_field)) {
            foreach (custom_field_without_break($mod->form->custom_field) as $cf) {
                $labelMap[_us($cf[0])] = $cf[0];
            }
        }

        if (!empty($post->data_field)) {
            foreach ($post->data_field as $dfKey => $dfVal) {
                if ($dfKey === 'last_editor')
                    continue;
                if ($allowedShowKeys && !in_array($dfKey, $allowedShowKeys))
                    continue;

                $fieldLabel = $labelMap[$dfKey] ?? str($dfKey)->replace('_', ' ')->title()->toString();
                $fieldsData[$fieldLabel] = $dfVal;
            }
        }

        $html = view('cms::layouts.data_tracker_result', [
            'post' => $post,
            'module' => $module,
            'mod' => $mod,
            'status_text' => $statusText,
            'fields_data' => $fieldsData,
        ])->render();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'found' => true,
                'html' => $html,
                'data' => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'status' => $statusText,
                    'created_at' => $post->created_at?->toIso8601String(),
                    'data_field' => $post->data_field,
                ]
            ]);
        }

        return back()->with('track_result', $html)->withInput();
    }

    public function home(Request $request)
    {
        if ($request->isMethod('post') && $request->has('_validate_file')) {
            $referer = $request->headers->get('referer');
            if ($referer && str_starts_with($referer, url('/'))) {
                return app(\Leazycms\Web\Http\Controllers\ExtController::class)->validate_file($request);
            }
        }

        $hp = get_option('home_page');
        if ($hp != 'default' && View::exists('template.' . template() . '.' . str_replace('.blade.php', '', $hp))) {
            return view('template.' . template() . '.' . str_replace('.blade.php', '', $hp));
        }
        return view('cms::layouts.master');
    }

    public function api(Request $req, Post $post, $id = null)
    {
        $allowIp = get_option('allow_ip');
        if (empty($allowIp) || ($allowIp && !in_array(get_client_ip(), explode(",", $allowIp)))) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        if ($id) {
            return response([
                'code' => 200,
                'status' => "success",
                'data' => $post->selectedColumn()->with('user')->whereStatus('publish')->findOrFail($id)
            ], 200);
        }
        return response([
            'code' => 200,
            'status' => "success",
            'data' => $post->index(get_post_type(), true)
        ], 200);
    }
    public function index(Post $post)
    {
        $modul = current_module();
        config(['modules.page_name' => $modul->title]);
        $perPage = $modul->web->post_perpage ?? get_option('post_perpage') ?? 10;
        $data = array(
            'index' => $modul->web->auto_query ? $post->index($modul->name, $perPage) : [],
            'module' => $modul,
        );

        return view('cms::layouts.master', $data);
    }
    public function tags($slug)
    {
        $tag = Tag::select('name', 'visited', 'id', 'slug', 'url')->whereSlug(str($slug)->lower())->first();
        if (empty($tag)) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }

        if ($tag->name != $slug) {
            return redirect($tag->url);
        }
        config(['modules.page_name' => $tag->name]);

        $tag->timestamps = false;
        $tag->increment('visited');

        $post = Post::selectedColumn()->whereHas('tags', function ($query) use ($slug) {
            $query->where('slug', $slug)->whereStatus('publish');
        })->published()->latest('created_at')->paginate(get_option('post_perpage'));

        $data = array(
            'index' => $post,
            'tag' => $tag
        );
        if (View::exists('template.' . template() . '.tags.' . $tag->slug)) {
            return view('template.' . template() . '.tags.' . $tag->slug, $data);
        }
        return view('cms::layouts.master', $data);
    }
    public function author(Request $request, $u = null)
    {
        if ($u) {
            $user = User::select('id', 'name', 'url', 'photo', 'slug')->whereSlug($u)->first();
            if (empty($user)) {
                return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
            }
            config(['modules.page_name' => 'Author: ' . $user->name]);
            $data = [
                'index' => $user->posts()->paginate(10)
            ];
            return view('cms::layouts.master', $data);
        } else {
            $author = User::select('id', 'name', 'url', 'photo', 'slug')
                ->whereHas('posts')
                ->where('status', 'active')
                ->whereNotIn('level', ['admin'])
                ->get();
            config(['modules.page_name' => 'Author']);
            $data = [
                'author' => $author
            ];
            return view('cms::layouts.master', $data);
        }

    }
    public function detail(Request $request, Post $post, $name = null)
    {

        $slug = Str::of($name)
            ->replaceMatches('/[^\p{L}\p{N}-]/u', '')
            ->toString();
        if (strlen($slug) < 5) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        $postType = get_post_type() ?? 'page';
        $modul = get_module($postType);
        $detail = $post->detail($postType, $slug);
        if (empty($detail)) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }

        if ($request->ajax() && $request->isMethod('post')) {
            $request->validate([
                'name' => 'required',
                'captcha' => 'required|min:5|max:5'
            ]);
            if ($request->captcha && !captcha_check($request->captcha)) {
                $request->session()->regenerateToken();
                return response()->json(['error' => 'Captcha'], 200);
            }
            $meta = [];
            if (is_array($request->input('comment_meta'))) {
                foreach ($request->input('comment_meta') as $key => $val) {
                    if (is_string($val)) {
                        $meta[$key] = strip_tags($val);
                    }
                }
            }

            $comment = $detail->addComment([
                'name' => strip_tags(substr($request->name, 0, 20)),
                'email' => strip_tags(substr($request->email, 0, 50) ?? null),
                'ip' => get_client_ip(),
                'content' => nl2br(strip_tags(substr($request->comment_content, 0, 500) ?? null)),
                'link' => strip_tags($request->link ?? null),
                'comment_meta' => $meta,
            ]);

            if (is_array($request->file('comment_meta'))) {
                $hasFiles = false;
                foreach ($request->file('comment_meta') as $key => $file) {
                    if ($file && $file->isValid()) {
                        $savedPath = $comment->addFile([
                            'file' => $file,
                            'purpose' => $key,
                            'mime_type' => ['image/webp', 'application/pdf'],
                            'random_name' => true
                        ]);
                        $meta[$key] = $savedPath;
                        $hasFiles = true;
                    }
                }
                if ($hasFiles) {
                    $comment->update(['comment_meta' => $meta]);
                }
            }
            $request->session()->regenerateToken();
            return response()->json(['error' => 'None'], 200);

        }
        if ($detail->slug != $name) {
            if ($detail->shortcut == $slug) {
                $detail->increment('shortcut_counter');
            }
            return redirect($detail->url);
        }
        if (config('modules.multisite_enabled') && is_main_domain() && $detail->tenant_id) {
            if ($detail->tenant_id != tenant()->id) {
                return redirect('http://' . $detail->tenant->domain . '/' . $detail->url);
            }
        }

        config(['modules.data' => $detail]);
        if ($detail->redirect_to) {
            return redirect($detail->redirect_to);
        }

        $data = array(
            'module' => $modul,
            'category' => $detail->category ?? null,
            'detail' => $detail,
            'history' => $detail->history
        );

        if (!empty($detail->password)) {

            $sessionKey = "post_access_{$detail->id}";

            // Kalau belum submit
            if (!$request->isMethod('post')) {
                if (Session::has($sessionKey)) {
                    $expiredAt = Session::get($sessionKey);
                    if (Carbon::now()->lt($expiredAt)) {

                    } else {

                        Session::forget($sessionKey);
                        return redirect()->to($request->url());
                    }
                } else {
                    return response(protectedContentView($slug));
                }
            } else {


                // Validasi input
                $request->validate([
                    'secret_key' => 'required|digits:4'
                ]);

                // Cek password
                if ($request->secret_key !== dec64($detail->password)) {
                    return response(protectedContentView(
                        $slug,
                        null,
                        'Kode salah, coba lagi.'
                    ));
                }

                session([
                    $sessionKey => Carbon::now()->addMinutes(1)
                ]);
                return redirect()->to($request->url());
            }
        }

        if (View::exists('template.' . template() . '.' . $detail->type . '.' . $detail->slug)) {
            return view('template.' . template() . '.' . $detail->type . '.' . $detail->slug, $data);
        }
        return view('cms::layouts.master', $data);
    }
    public function category($slug = null)
    {
        $modul = get_module(get_post_type());
        $category = Category::where('slug', 'like', $slug . '%')
            ->whereType($modul->name)
            ->whereStatus('publish')
            ->whereHas('posts')
            ->first();

        if (!$category) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        if ($category->slug != $slug) {
            return redirect($category->url);
        }
        config(['modules.page_name' => $modul->title . ' di kategori ' . $category->name]);
        $category->timestamps = false;
        $category->increment('visited');
        $perPage = $modul->web?->post_perpage ?? get_option('post_perpage');
        $data = array(
            'index' => query()->index_by_category($modul->name, $slug, $perPage),
            'category' => $category,
            'module' => $modul
        );
        if (View::exists('template.' . template() . '.' . $category->type . '.category.' . $category->slug)) {
            return view('template.' . template() . '.' . $category->type . '.category.' . $category->slug, $data);
        }
        return view('cms::layouts.master', $data);
    }
    public function search(Request $request, $slug = null)
    {
        if ($request->isMethod('post') && $request->keyword) {
            return redirect('search/' . str($request->keyword)->slug());
        }
        if (empty($slug)) {
            return to_route('home');
        }
        $query = str_replace('-', ' ', str($slug)->slug());
        $modules = get_module();
        $type = collect($modules)
            ->where('public', true)
            ->where('web.detail', true)
            ->where('web.index', true)
            ->pluck('name')->toArray();

        $index = Post::selectedColumn()
            ->whereIn('type', $type)
            ->where('type', '!=', 'page')
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('keyword', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate(get_option('post_perpage'));

        $data = array(
            'keyword' => ucwords($query),
            'index' => $index
        );
        return view('cms::layouts.master', $data);
    }

    public function post_parent(Request $request, $slug = null)
    {
        $modul = get_module(get_post_type());
        if (empty($slug)) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        $post_parent = query()->onType($modul->form->post_parent[1])->where('slug', 'like', $slug . '%')
            ->select('id', 'title', 'slug')->published()->first();
        if (empty($post_parent)) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }
        if ($post_parent->slug != $slug) {
            return redirect($modul->name . '/' . $request->segment(2) . '/' . $post_parent->slug);
        }
        $title = $post_parent->title;
        $post_name = $modul->title;
        config(['modules.page_name' => $modul->title . ' di ' . $modul->form->post_parent[0] . ' ' . $title]);
        $index = query()->index_child($modul->name, $post_parent->id, true);
        $data = array(
            'index' => $index,
            'title' => $post_name . ' ' . $title,
            'icon' => $modul->icon,
            'module' => $modul
        );
        return view('cms::layouts.master', $data);
    }
    public function archive(Request $request, Post $post, $year = null, $month = null, $date = null)
    {
        $type = get_post_type();
        $module = get_module($type);
        if ($year > date('Y')) {
            return app(\Leazycms\Web\Http\Controllers\NotFoundController::class)->error404();
        }

        $perPage = $module->web?->post_perpage ?? get_option('post_perpage');

        if ($year && !$month && !$date) {
            $periode = $year;
            $data = $post->onType($type)->published()->with(['user', 'category'])->whereYear('created_at', $year)->latest('created_at')->paginate($perPage);
        } elseif ($year && $month && !$date) {

            $periode = blnindo($month) . ' ' . $year;
            $data = $post->onType($type)
                ->published()
                ->with(['user', 'category'])
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->latest('created_at')
                ->paginate($perPage);

        } elseif ($year && $month && $date) {


            $periode = ((substr($date, 0, 1) == '0') ? substr($date, 1, 2) : $date) . ' ' . blnindo($month) . ' ' . $year;
            $data = $post->onType($type)
                ->published()
                ->with(['user', 'category'])
                ->whereDate('created_at', $year . '-' . $month . '-' . $date)
                ->latest('created_at')
                ->paginate($perPage);

        }

        $data = array(
            'title' => 'Arsip ' . $module->title . ' ' . $periode,
            'icon' => 'fa-archive',
            'index' => $data
        );
        config(['modules.page_name' => $data['title']]);
        return view('cms::layouts.master', $data);
    }
}
