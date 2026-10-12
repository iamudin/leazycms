<?php

namespace Leazycms\Web\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Leazycms\FLC\Models\File;
use Leazycms\Web\Models\Option;
use Leazycms\Web\Models\Post;

class MediaManagerController extends Controller
{
    /**
     * Dapatkan daftar host yang diizinkan untuk multisite/tenant.
     */
    protected function getAuthorizedHosts(Request $request): ?array
    {
        if (config('modules.multisite_enabled') && !is_main_domain()) {
            if (app()->has('tenant') && function_exists('tenant') && tenant()) {
                $tenant = tenant();
                $parkedDomain = Cache::rememberForever("tenant:{$tenant->id}:parked_domain", function () use ($tenant) {
                    return Option::withoutGlobalScope('tenant')
                        ->where('tenant_id', $tenant->id)
                        ->where('name', 'parked_domain')
                        ->value('value') ?: '';
                });
                if ($parkedDomain === '') {
                    $parkedDomain = null;
                }
                return array_values(array_filter([
                    $tenant->domain,
                    $parkedDomain,
                    $tenant->getAttribute('matched_parked_domain'),
                    $request->getHost()
                ]));
            }
            return [$request->getHost()];
        }
        return null;
    }

    /**
     * Format ukuran bytes ke teks yang mudah dibaca.
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $bytes = (float) $bytes;
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        return round($bytes / pow(1024, $power), $precision) . ' ' . $units[$power];
    }

    /**
     * Query dasar file sesuai izin host / tenant.
     */
    protected function baseFileQuery(Request $request)
    {
        $query = File::query();
        if (config('modules.multisite_enabled') && !is_main_domain()) {
            $allowedHosts = $this->getAuthorizedHosts($request);
            if ($allowedHosts !== null) {
                $query->whereIn('host', $allowedHosts);
            } else {
                $query->where('host', $request->getHost());
            }
        } else {
            if ($request->filled('host') && $request->host !== 'all') {
                $query->where('host', $request->host);
            }
        }
        return $query;
    }

    /**
     * Halaman Utama Media Manager (Grid View).
     */
    public function index(Request $request)
    {
        abort_if(!Auth::check(), 403);

        $baseQuery = $this->baseFileQuery($request);
        $totalFiles = (clone $baseQuery)->count();
        $totalSizeBytes = (clone $baseQuery)->sum('file_size');
        $totalSizeFormatted = $this->formatBytes($totalSizeBytes);

        // Ekstensi yang ada dan jumlahnya
        $extensionsList = (clone $baseQuery)->pluck('file_name')->map(function ($name) {
            return strtolower(pathinfo($name, PATHINFO_EXTENSION));
        })->filter()->countBy()->sortDesc();

        return view('cms::backend.media-manager.index', [
            'totalFiles' => $totalFiles,
            'totalSizeFormatted' => $totalSizeFormatted,
            'extensionsList' => $extensionsList,
        ]);
    }

    /**
     * API Endpoint untuk memuat daftar media (filter ekstensi, search, sort waktu/ukuran, pagination).
     */
    public function list(Request $request)
    {
        abort_if(!Auth::check(), 403);

        $query = $this->baseFileQuery($request)->with('user');

        // 1. Filter Pencarian Nama File
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('file_name', 'LIKE', '%' . $search . '%');
        }

        // 2. Filter Kategori Ekstensi / Ekstensi Spesifik
        $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'];
        $docExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf', 'csv'];
        $videoExts = ['mp4', 'mkv', 'avi', 'mov', 'webm', 'mp3', 'wav', 'ogg'];
        $archiveExts = ['zip', 'rar', '7z', 'tar', 'gz'];

        if ($request->filled('category') && $request->category !== 'all') {
            $category = strtolower($request->category);
            $targetExts = match ($category) {
                'image' => $imageExts,
                'document' => $docExts,
                'video' => $videoExts,
                'archive' => $archiveExts,
                default => [],
            };
            if (!empty($targetExts)) {
                $query->where(function ($q) use ($targetExts) {
                    foreach ($targetExts as $ext) {
                        $q->orWhere('file_name', 'LIKE', '%.' . $ext);
                    }
                });
            }
        }

        if ($request->filled('extension') && $request->extension !== 'all') {
            $ext = strtolower(ltrim($request->extension, '.'));
            $query->where('file_name', 'LIKE', '%.' . $ext);
        }

        // 3. Sorting (Waktu & Ukuran)
        $sort = $request->get('sort', 'time_desc');
        switch ($sort) {
            case 'time_asc':
                $query->oldest();
                break;
            case 'size_desc':
                $query->orderBy('file_size', 'desc');
                break;
            case 'size_asc':
                $query->orderBy('file_size', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('file_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('file_name', 'desc');
                break;
            case 'time_desc':
            default:
                $query->latest();
                break;
        }

        $perPage = (int) $request->get('per_page', 24);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 24;
        }

        $paginator = $query->paginate($perPage);

        // Format items untuk grid view
        $items = collect($paginator->items())->map(function ($item) use ($imageExts, $docExts, $videoExts, $archiveExts) {
            $ext = strtolower(pathinfo($item->file_name, PATHINFO_EXTENSION));
            $isImage = in_array($ext, $imageExts);
            $isVideo = in_array($ext, ['mp4', 'mkv', 'avi', 'mov', 'webm']);
            $isAudio = in_array($ext, ['mp3', 'wav', 'ogg']);
            $isPdf = ($ext === 'pdf');
            $isDoc = in_array($ext, $docExts);
            $isArchive = in_array($ext, $archiveExts);

            $fileUrl = url('media/' . $item->file_name);
            $thumbUrl = $fileUrl . ($isImage && !in_array($ext, ['svg', 'ico']) ? '?size=small&time=' . time() : '');

            return [
                'id' => $item->id,
                'file_name' => $item->file_name,
                'file_path' => $item->file_path,
                'file_type' => $item->file_type,
                'file_size' => (int) $item->file_size,
                'file_size_formatted' => $this->formatBytes($item->file_size),
                'extension' => strtoupper($ext),
                'is_image' => $isImage,
                'is_video' => $isVideo,
                'is_audio' => $isAudio,
                'is_pdf' => $isPdf,
                'is_doc' => $isDoc,
                'is_archive' => $isArchive,
                'url' => $fileUrl,
                'thumb_url' => $thumbUrl,
                'uploader' => $item->user?->name ?? 'System',
                'created_at' => $item->created_at ? $item->created_at->translatedFormat('d M Y H:i') : '-',
                'created_raw' => $item->created_at ? $item->created_at->toISOString() : null,
            ];
        });

        // Hitung statistik ekstensi dinamis untuk query saat ini
        $allExtensions = $this->baseFileQuery($request)->pluck('file_name')->map(function ($name) {
            return strtolower(pathinfo($name, PATHINFO_EXTENSION));
        })->filter()->countBy()->sortDesc();

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'extensions' => $allExtensions,
        ]);
    }

    /**
     * Upload File Baru langsung dari Media Manager.
     */
    public function upload(Request $request)
    {
        abort_if(!Auth::check(), 403);

        $maxSizeBytes = \Illuminate\Http\UploadedFile::getMaxFilesize();
        $maxSizeKB = floor($maxSizeBytes / 1024);

        $request->validate([
            'media' => 'required',
        ]);

        $files = $request->file('media');
        if (!is_array($files)) {
            $files = [$files];
        }

        $uploadedResults = [];
        $errors = [];

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                $errors[] = 'File tidak valid atau rusak.';
                continue;
            }

            $fileHash = md5_file($file->getRealPath());
            $tenantHosts = [$request->getHost()];
            if (app()->has('tenant') && function_exists('tenant') && tenant()) {
                $tenant = tenant();
                $tenantHosts = array_values(array_filter([
                    $tenant->domain,
                    $tenant->getAttribute('matched_parked_domain'),
                    $request->getHost()
                ]));
            }

            // Cek duplikasi
            $existing = File::where('child_id', $fileHash)
                ->where(function ($q) use ($tenantHosts) {
                    $q->whereIn('host', $tenantHosts)->orWhereNull('host');
                })->first();

            if ($existing) {
                $errors[] = 'File "' . $file->getClientOriginalName() . '" sudah ada sebelumnya: ' . $existing->file_name;
                continue;
            }

            $addResult = (new File)->addFile([
                'file' => $file,
                'purpose' => 'Upload Media',
                'child_id' => $fileHash,
                'mime_type' => explode(',', allow_mime()),
                'self_upload' => true,
            ]);

            if ($addResult) {
                $rawFileName = str_replace('/media/', '', $addResult);
                $finalFile = File::where('file_name', $rawFileName)->first();
                if ($finalFile) {
                    $uploadedResults[] = [
                        'id' => $finalFile->id,
                        'file_name' => $finalFile->file_name,
                        'file_size' => $this->formatBytes($finalFile->file_size),
                        'url' => url('media/' . $finalFile->file_name),
                    ];
                }
            } else {
                $errors[] = 'Gagal mengunggah file "' . $file->getClientOriginalName() . '".';
            }
        }

        if (count($uploadedResults) > 0) {
            return response()->json([
                'status' => 'success',
                'message' => count($uploadedResults) . ' file berhasil diunggah.',
                'uploaded' => $uploadedResults,
                'errors' => $errors,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => count($errors) > 0 ? implode(' ', $errors) : 'Gagal mengunggah file.',
            'errors' => $errors,
        ], 400);
    }

    /**
     * Memeriksa penggunaan file di tabel options dan posts secara cepat dan teroptimasi.
     */
    private function getFileUsageInfo($fileName, $limit = 25)
    {
        $postUsages = [];
        $optionUsages = [];

        // 1. Cek tabel options (cepat, hanya beberapa ratus baris)
        $options = Option::withoutGlobalScopes()
            ->where('value', 'LIKE', '%' . $fileName . '%')
            ->take($limit)
            ->get(['id', 'name', 'value']);

        foreach ($options as $o) {
            $optionUsages[] = [
                'id' => $o->id,
                'name' => $o->name,
            ];
        }

        // 2. Cek kolom media di tabel posts (cepat menggunakan index posts_media_index)
        $mediaPatterns = ['/media/' . $fileName, 'media/' . $fileName, $fileName];
        $postsMedia = Post::withoutGlobalScopes()
            ->whereIn('media', $mediaPatterns)
            ->take($limit)
            ->get(['id', 'title', 'type', 'media']);

        foreach ($postsMedia as $p) {
            $postUsages[$p->id] = [
                'id' => $p->id,
                'title' => $p->title ?: 'Tanpa Judul (ID: ' . $p->id . ')',
                'type' => ucfirst($p->type ?: 'Post'),
                'used_in' => 'Thumbnail / Media Utama',
            ];
        }

        // 3. Cek kolom content di tabel posts (menggunakan Fulltext Index dengan fallback)
        try {
            $postsContent = Post::withoutGlobalScopes()
                ->whereRaw('MATCH(content) AGAINST(? IN BOOLEAN MODE)', ['"' . $fileName . '"'])
                ->take($limit)
                ->get(['id', 'title', 'type']);
        } catch (\Exception $e) {
            $postsContent = Post::withoutGlobalScopes()
                ->where('content', 'LIKE', '%' . $fileName . '%')
                ->take($limit)
                ->get(['id', 'title', 'type']);
        }

        foreach ($postsContent as $p) {
            if (isset($postUsages[$p->id])) {
                if (!str_contains($postUsages[$p->id]['used_in'], 'Konten Artikel')) {
                    $postUsages[$p->id]['used_in'] .= ', Konten Artikel';
                }
            } else {
                $postUsages[$p->id] = [
                    'id' => $p->id,
                    'title' => $p->title ?: 'Tanpa Judul (ID: ' . $p->id . ')',
                    'type' => ucfirst($p->type ?: 'Post'),
                    'used_in' => 'Konten Artikel',
                ];
            }
        }

        // 4. Cek kolom data_field di tabel posts (hanya memindai ID yang teridentifikasi memiliki media)
        $cachedDfIds = Cache::remember('mm_posts_with_media_data_field', 1800, function () {
            return Post::withoutGlobalScopes()
                ->where('data_field', 'LIKE', '%media/%')
                ->pluck('id')
                ->toArray();
        });

        // Sertakan juga post yang baru dibuat dalam 15 menit terakhir agar data real-time
        $recentDfIds = Post::withoutGlobalScopes()
            ->where('created_at', '>=', now()->subMinutes(15))
            ->whereNotNull('data_field')
            ->pluck('id')
            ->toArray();

        $allDfIds = array_values(array_unique(array_merge($cachedDfIds ?: [], $recentDfIds ?: [])));

        if (!empty($allDfIds)) {
            $postsDf = Post::withoutGlobalScopes()
                ->whereIn('id', $allDfIds)
                ->where('data_field', 'LIKE', '%' . $fileName . '%')
                ->take($limit)
                ->get(['id', 'title', 'type']);

            foreach ($postsDf as $p) {
                if (isset($postUsages[$p->id])) {
                    if (!str_contains($postUsages[$p->id]['used_in'], 'Data Field')) {
                        $postUsages[$p->id]['used_in'] .= ', Data Field';
                    }
                } else {
                    $postUsages[$p->id] = [
                        'id' => $p->id,
                        'title' => $p->title ?: 'Tanpa Judul (ID: ' . $p->id . ')',
                        'type' => ucfirst($p->type ?: 'Post'),
                        'used_in' => 'Data Field',
                    ];
                }
            }
        }

        // 5. Cek kolom data_loop di tabel posts (hanya memindai ID yang teridentifikasi memiliki media)
        $cachedDlIds = Cache::remember('mm_posts_with_media_data_loop', 1800, function () {
            return Post::withoutGlobalScopes()
                ->where('data_loop', 'LIKE', '%media/%')
                ->pluck('id')
                ->toArray();
        });

        $recentDlIds = Post::withoutGlobalScopes()
            ->where('created_at', '>=', now()->subMinutes(15))
            ->whereNotNull('data_loop')
            ->pluck('id')
            ->toArray();

        $allDlIds = array_values(array_unique(array_merge($cachedDlIds ?: [], $recentDlIds ?: [])));

        if (!empty($allDlIds)) {
            $postsDl = Post::withoutGlobalScopes()
                ->whereIn('id', $allDlIds)
                ->where('data_loop', 'LIKE', '%' . $fileName . '%')
                ->take($limit)
                ->get(['id', 'title', 'type']);

            foreach ($postsDl as $p) {
                if (isset($postUsages[$p->id])) {
                    if (!str_contains($postUsages[$p->id]['used_in'], 'Data Loop')) {
                        $postUsages[$p->id]['used_in'] .= ', Data Loop';
                    }
                } else {
                    $postUsages[$p->id] = [
                        'id' => $p->id,
                        'title' => $p->title ?: 'Tanpa Judul (ID: ' . $p->id . ')',
                        'type' => ucfirst($p->type ?: 'Post'),
                        'used_in' => 'Data Loop',
                    ];
                }
            }
        }

        return [
            'is_used' => (count($postUsages) > 0 || count($optionUsages) > 0),
            'post_usages' => array_values($postUsages),
            'option_usages' => $optionUsages,
        ];
    }

    /**
     * Validasi sebelum menghapus: Cek apakah file terpakai di table `posts` atau `options`.
     */
    public function checkUsage(Request $request, $id)
    {
        abort_if(!Auth::check(), 403);

        $file = $this->baseFileQuery($request)->findOrFail($id);
        $fileName = $file->file_name;

        $usage = $this->getFileUsageInfo($fileName, 25);
        $isUsed = $usage['is_used'];

        return response()->json([
            'status' => 'success',
            'file_id' => $file->id,
            'file_name' => $file->file_name,
            'file_size' => $this->formatBytes($file->file_size),
            'is_used' => $isUsed,
            'can_delete' => !$isUsed,
            'total_post_references' => count($usage['post_usages']),
            'total_option_references' => count($usage['option_usages']),
            'post_usages' => $usage['post_usages'],
            'option_usages' => $usage['option_usages'],
            'message' => $isUsed
                ? 'File tidak dapat dihapus karena masih digunakan pada konten atau pengaturan website!'
                : 'File aman untuk dihapus. Tidak ada referensi di tabel posts maupun options.',
        ]);
    }

    /**
     * Hapus file setelah divalidasi tidak terpakai di `posts` dan `options`.
     */
    public function destroy(Request $request, $id)
    {
        abort_if(!Auth::check(), 403);

        $file = $this->baseFileQuery($request)->findOrFail($id);
        $fileName = $file->file_name;

        // Validasi cepat di backend sebelum menghapus
        $usage = $this->getFileUsageInfo($fileName, 1);
        if ($usage['is_used']) {
            $whereList = [];
            if (!empty($usage['post_usages'])) {
                $whereList[] = 'tabel posts';
            }
            if (!empty($usage['option_usages'])) {
                $whereList[] = 'tabel options';
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Penghapusan dibatalkan! File "' . $fileName . '" masih digunakan pada ' . implode(' dan ', $whereList) . '.',
            ], 422);
        }

        try {
            // Hapus file fisik dari disk storage
            if ($file->disk && Storage::disk($file->disk)->exists($file->file_path)) {
                Storage::disk($file->disk)->delete($file->file_path);
            }

            // Hapus cache media
            Cache::forget($file->host . ':media:' . $fileName);

            // Log aktivitas
            Log::channel('daily')->warning('Media Manager: File deleted ' . $fileName, [
                'user' => Auth::user()->email ?? Auth::id(),
                'ip' => get_client_ip(),
                'path' => $file->file_path,
            ]);

            // Hapus record dari database
            $file->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'File "' . $fileName . '" berhasil dihapus secara permanen.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Edit Gambar (Crop, Rotate, Flip, Resize) jika file gambar.
     */
    public function editImage(Request $request, $id)
    {
        abort_if(!Auth::check(), 403);

        $file = $this->baseFileQuery($request)->findOrFail($id);

        // Validasi apakah file adalah gambar
        $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
        $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $imageExts) || !str_starts_with((string) $file->file_type, 'image/')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya file gambar (WebP, PNG, JPG) yang dapat diedit.',
            ], 422);
        }

        $request->validate([
            'image_data' => 'required|string|max:15000000',
            'action' => 'required|in:overwrite,new',
            'new_name' => 'nullable|string|max:100',
        ]);

        try {
            $base64Data = $request->image_data;

            // Decode image via Intervention Image
            $image = Image::decode($base64Data);

            // Tentukan format encoding sesuai ekstensi asli
            $targetExt = in_array($ext, ['webp', 'png', 'jpg', 'jpeg']) ? $ext : 'webp';
            if ($targetExt === 'jpg') {
                $targetExt = 'jpeg';
            }

            $encodedBytes = $image->encodeUsingFileExtension($targetExt, quality: 92)->toString();

            if ($request->action === 'overwrite') {
                // TIMPA GAMBAR ASLI
                Storage::disk($file->disk)->put($file->file_path, $encodedBytes);

                $file->file_size = strlen($encodedBytes);
                $file->updated_at = now();
                $file->save();

                // Bersihkan cache media
                Cache::forget($file->host . ':media:' . $file->file_name);

                return response()->json([
                    'status' => 'success',
                    'mode' => 'overwrite',
                    'message' => 'Gambar "' . $file->file_name . '" berhasil diperbarui!',
                    'file' => [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_path' => $file->file_path,
                        'file_type' => $file->file_type,
                        'file_size' => $file->file_size,
                        'file_size_formatted' => $this->formatBytes($file->file_size),
                        'extension' => strtoupper($targetExt === 'jpeg' ? 'jpg' : $targetExt),
                        'is_image' => true,
                        'is_video' => false,
                        'is_audio' => false,
                        'is_pdf' => false,
                        'is_doc' => false,
                        'is_archive' => false,
                        'url' => url('media/' . $file->file_name) . '?time=' . time(),
                        'thumb_url' => url('media/' . $file->file_name) . '?size=small&time=' . time(),
                        'uploader' => $file->user?->name ?? 'System',
                        'created_at' => $file->created_at ? $file->created_at->translatedFormat('d M Y H:i') : '-',
                    ],
                ]);
            } else {
                // SIMPAN SEBAGAI FILE BARU
                $customName = $request->filled('new_name') ? Str::slug($request->new_name) : pathinfo($file->file_name, PATHINFO_FILENAME) . '-edited';
                $newFileName = $customName . '-' . Str::lower(Str::random(4)) . '.' . ($targetExt === 'jpeg' ? 'jpg' : $targetExt);

                $host = $file->host ?: $request->getHost();
                $directory = (config('flc.disk_directory') ? config('flc.disk_directory') . '/' : $host . '/') . Carbon::now()->format('Y/m/d');
                $newPath = $directory . '/' . $newFileName;

                Storage::disk($file->disk)->put($newPath, $encodedBytes);

                $newFile = File::create([
                    'user_id' => Auth::id(),
                    'file_path' => $newPath,
                    'file_type' => 'image/' . ($targetExt === 'jpg' ? 'jpeg' : $targetExt),
                    'file_name' => $newFileName,
                    'file_size' => strlen($encodedBytes),
                    'disk' => $file->disk,
                    'host' => $host,
                    'purpose' => 'edited-media',
                    'child_id' => md5($encodedBytes),
                ]);

                Cache::rememberForever($newFile->host . ":media:{$newFile->file_name}", function () use ($newFile) {
                    return [
                        'file_path' => $newFile->file_path,
                        'file_type' => $newFile->file_type,
                        'file_host' => $newFile->host,
                        'file_auth' => $newFile->file_auth,
                        'file_size' => $newFile->file_size,
                        'file_hits' => 0,
                        'file_disk' => $newFile->disk,
                        'encrypt_key' => null,
                    ];
                });

                return response()->json([
                    'status' => 'success',
                    'mode' => 'new',
                    'message' => 'Gambar baru "' . $newFileName . '" berhasil disimpan ke Media Library!',
                    'file' => [
                        'id' => $newFile->id,
                        'file_name' => $newFile->file_name,
                        'file_path' => $newFile->file_path,
                        'file_type' => $newFile->file_type,
                        'file_size' => $newFile->file_size,
                        'file_size_formatted' => $this->formatBytes($newFile->file_size),
                        'extension' => strtoupper($targetExt === 'jpeg' ? 'jpg' : $targetExt),
                        'is_image' => true,
                        'is_video' => false,
                        'is_audio' => false,
                        'is_pdf' => false,
                        'is_doc' => false,
                        'is_archive' => false,
                        'url' => url('media/' . $newFile->file_name),
                        'thumb_url' => url('media/' . $newFile->file_name) . '?size=small&time=' . time(),
                        'uploader' => Auth::user()?->name ?? 'System',
                        'created_at' => $newFile->created_at ? $newFile->created_at->translatedFormat('d M Y H:i') : Carbon::now()->translatedFormat('d M Y H:i'),
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::channel('daily')->error('Media Manager: Edit image failed - ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses gambar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
