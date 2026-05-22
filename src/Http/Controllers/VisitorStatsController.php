<?php

namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class VisitorStatsController extends Controller
{

        public function logoGenerator()
    {
        /*
        |--------------------------------------------------------------------------
        | GET OPTION
        |--------------------------------------------------------------------------
        */

        $title = strtoupper(
            get_option('singkatan_organisasi') ?? 'JUDULWEB'
        );

        $slogan = get_option('keterangan_organisasi')
            ?? 'DESKRIPSIWEB';

        $logoPath = get_option('logo_organisasi')
            ? media(get_option('logo_organisasi'))->path()
            : public_path('noimage.png');

        /*
        |--------------------------------------------------------------------------
        | CACHE KEY
        |--------------------------------------------------------------------------
        */

        // Membuat hash unik untuk membedakan cache apabila opsi berubah
        $cacheKey = md5(
            $title .
            $slogan .
            $logoPath .
            (app()->has('tenant') ? tenant()->id : '')
        );

        /*
        |--------------------------------------------------------------------------
        | BROWSER CACHE (ETAG)
        |--------------------------------------------------------------------------
        */

        $etag = '"' . $cacheKey . '"';

        // Jika browser mengirimkan ETag yang sama, kembalikan 304 Not Modified
        if (request()->header('If-None-Match') === $etag) {
            return response('', 304);
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE TRANSPARENT CANVAS (Intervention Image v2)
        |--------------------------------------------------------------------------
        */

        $img = Image::canvas(650, 110); // Default is transparent

        /*
        |--------------------------------------------------------------------------
        | PLACE LOGO
        |--------------------------------------------------------------------------
        */

        if (File::exists($logoPath)) {
            $logoImage = Image::make($logoPath)->resize(100, 100, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Menyisipkan logo di koordinat x: 30, y: 35
            $img->insert($logoImage, 'top-left', 10, 5);
        }

        /*
        |--------------------------------------------------------------------------
        | GARIS ORNAMEN MELAYU
        |--------------------------------------------------------------------------
        */

        // Membuat garis menggunakan rectangle dengan ketebalan 4px (v2 syntax)




        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        $fontBold = public_path('fonts/Poppins-Bold.ttf');
        $img->text($title, 100, 60, function ($font) use ($fontBold) {
            if (File::exists($fontBold)) {
                $font->file($fontBold);
            }
            $font->size(65);
            $font->color('#00843D');
        });

        /*
        |--------------------------------------------------------------------------
        | SLOGAN
        |--------------------------------------------------------------------------
        */

        $fontRegular = public_path('fonts/Poppins-Regular.ttf');
        $img->text($slogan, 101, 88, function ($font) use ($fontRegular) {
            if (File::exists($fontRegular)) {
                $font->file($fontRegular);
            }
            $font->size(28);
            $font->color('#B8860B');
        });

        /*
        |--------------------------------------------------------------------------
        | EXPORT WEBP & RETURN (Browser Cache Only)
        |--------------------------------------------------------------------------
        */

        // Menggunakan format webp untuk performa lebih baik sesuai ekstensi route
        $output = $img->encode('webp', 90);

        return response($output)
            ->header('Content-Type', 'image/webp')
            ->header('Cache-Control', 'public, max-age=31536000, immutable')
            ->header('ETag', $etag);
    }
    public function headerImage()
    {
        $host = request()->getHost();
        $now = Carbon::now();
        $tenantId = app()->has('tenant') ? tenant()->id : null;

        $referer = request()->headers->get('referer');

        if (!$referer || strpos($referer, $host) === false) {
            return redirect('/');
        }

        $ip = get_client_ip();
        $agent = request()->header('User-Agent');

        /* =========================
           ONLINE USER
        ==========================*/

        $onlineQuery = DB::table('analytics_visitors')
            ->where('domain', $host)
            ->where('last_seen_at', '>=', $now->copy()->subMinutes(5));

        if ($tenantId) {
            $onlineQuery->where('tenant_id', $tenantId);
        }

        $onlineVisitors = $onlineQuery->count();

        /* =========================
           RANGE
        ==========================*/

        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();
        $weekStart = $now->copy()->startOfWeek()->toDateString();
        $monthStart = $now->copy()->startOfMonth()->toDateString();

        /* =========================
           PAGEVIEW
        ==========================*/

        $baseQuery = DB::table('analytics_daily')
            ->where('domain', $host);

        if ($tenantId) {
            $baseQuery->where('tenant_id', $tenantId);
        }

        $todayViews = (clone $baseQuery)
            ->where('type', 'page_view')
            ->where('date', $today)
            ->sum('count');

        $yesterdayViews = (clone $baseQuery)
            ->where('type', 'page_view')
            ->where('date', $yesterday)
            ->sum('count');

        $weekViews = (clone $baseQuery)
            ->where('type', 'page_view')
            ->where('date', '>=', $weekStart)
            ->sum('count');

        $monthViews = (clone $baseQuery)
            ->where('type', 'page_view')
            ->where('date', '>=', $monthStart)
            ->sum('count');

        /* =========================
           UNIQUE VISITOR
        ==========================*/

        $uniqueVisitorsToday = (clone $baseQuery)
            ->where('type', 'unique_total')
            ->where('key', 'site')
            ->where('date', $today)
            ->value('count') ?? 0;

        /* =========================
           DEVICE INFO
        ==========================*/

        $browser = $this->getBrowser($agent);
        $device = $this->getDevice($agent);
        $os = $this->getOS($agent);

        /* =========================
           GENERATE IMAGE
        ==========================*/

        $img = Image::canvas(800, 730, 'rgba(0,0,0,0.65)');
        $fontPath = public_path('backend/fonts/captcha.ttf');

        $y = 70;

        $img->text('Statistik Pengunjung :', 30, $y, function ($font) use ($fontPath) {
            $font->file($fontPath);
            $font->size(40);
            $font->color('#ffffff');
        });

        $y += 60;

        $data = [

            'Sedang Online' => $onlineVisitors,
            'Unik Hari ini' => $uniqueVisitorsToday,
            '---------------------------------------' => null,

            'Pageview Hari ini' => number_format($todayViews),
            'Kemarin' => number_format($yesterdayViews),
            'Minggu ini' => number_format($weekViews),
            'Bulan ini' => number_format($monthViews),

            '---------------------------------------' => null,

            'IP Address' => $ip,
            'Browser' => $browser,
            'Perangkat' => $device,
            'Sistem Operasi' => $os,

        ];

        foreach ($data as $label => $value) {

            $img->text($label, 30, $y, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(32);
                $font->color('#ffffff');
            });

            if (!is_null($value)) {

                $img->text($value, 770, $y, function ($font) use ($fontPath) {
                    $font->file($fontPath);
                    $font->size(32);
                    $font->color('#ffdd00');
                    $font->align('right');
                });

            }

            $y += 55;
        }

        $response = $img->response('png');
        return $response;
    }


    private function getBrowser($agent)
    {
        if (preg_match('/Firefox/i', $agent))
            return 'Firefox';
        if (preg_match('/Chrome/i', $agent))
            return 'Chrome';
        if (preg_match('/Safari/i', $agent))
            return 'Safari';
        if (preg_match('/Edge/i', $agent))
            return 'Edge';
        return 'Unknown';
    }

    private function getDevice($agent)
    {
        if (preg_match('/Mobile/i', $agent))
            return 'Mobile';
        if (preg_match('/Tablet/i', $agent))
            return 'Tablet';
        return 'Desktop';
    }

    private function getOS($agent)
    {
        if (preg_match('/Windows/i', $agent))
            return 'Windows';
        if (preg_match('/Mac/i', $agent))
            return 'MacOS';
        if (preg_match('/Linux/i', $agent))
            return 'Linux';
        if (preg_match('/Android/i', $agent))
            return 'Android';
        if (preg_match('/iPhone|iPad/i', $agent))
            return 'iOS';
        return 'Unknown';
    }

}
