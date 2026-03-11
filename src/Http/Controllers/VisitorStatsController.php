<?php

namespace Leazycms\Web\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class VisitorStatsController extends Controller
{

    public function headerImage()
    {
        $host = request()->getHost();
        $now = Carbon::now();

        $referer = request()->headers->get('referer');

        if (!$referer || strpos($referer, $host) === false) {
            return redirect('/');
        }

        $ip = get_client_ip();
        $agent = request()->header('User-Agent');

        /* =========================
           ONLINE USER
        ==========================*/

        $onlineVisitors = DB::table('analytics_visitors')
            ->where('domain', $host)
            ->where('last_seen_at', '>=', $now->copy()->subMinutes(5))
            ->count();

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

        $todayViews = DB::table('analytics_daily')
            ->where('domain', $host)
            ->where('type', 'page_view')
            ->where('date', $today)
            ->sum('count');

        $yesterdayViews = DB::table('analytics_daily')
            ->where('domain', $host)
            ->where('type', 'page_view')
            ->where('date', $yesterday)
            ->sum('count');

        $weekViews = DB::table('analytics_daily')
            ->where('domain', $host)
            ->where('type', 'page_view')
            ->where('date', '>=', $weekStart)
            ->sum('count');

        $monthViews = DB::table('analytics_daily')
            ->where('domain', $host)
            ->where('type', 'page_view')
            ->where('date', '>=', $monthStart)
            ->sum('count');

        /* =========================
           UNIQUE VISITOR
        ==========================*/

        $uniqueVisitorsToday = DB::table('analytics_daily')
            ->where('domain', $host)
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