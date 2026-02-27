<?php
namespace Leazycms\Web\Http\Controllers;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class VisitorStatsController extends Controller
{
// ... method index() milikmu tetap ada di sini


    public function headerImage()
    {
        $host = request()->getHost();
        $now = Carbon::now();

        if (strpos(request()->headers->get('referer'), $host) === false) {
            return to_route('home');
        }

        $ip = request()->get_client_ip();
        $agent = request()->header('User-Agent');

        /* =========================
           ONLINE USER (table baru)
        ==========================*/
        $onlineVisitors = DB::table('online_users')
            ->where('domain', $host)
            ->where('last_activity', '>=', $now->copy()->subMinutes(5))
            ->count();

        /* =========================
           RANGE WAKTU
        ==========================*/
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $weekStart = $now->copy()->startOfWeek()->toDateString();

        /* =========================
           VISITOR STATS (table baru)
        ==========================*/
        $rangeStats = DB::table('visitor_stats')
            ->where('domain', $host)
            ->selectRaw("
        SUM(CASE WHEN date >= ? THEN total ELSE 0 END) as this_month,
        SUM(CASE WHEN date >= ? THEN total ELSE 0 END) as this_week
    ", [
                $monthStart,
                $weekStart
            ])
            ->first();
        $todayStats = DB::table('visitor_stats')
            ->where('domain', $host)
            ->where('date', $today)
            ->first();

        $yesterdayStats = DB::table('visitor_stats')
            ->where('domain', $host)
            ->where('date', $yesterday)
            ->first();

        $thisMonth = $rangeStats->this_month ?? 0;
        $thisWeek = $rangeStats->this_week ?? 0;

        $uniqueVisitorsToday = $todayStats->unique ?? 0;

        /* =========================
           DEVICE INFO
        ==========================*/
        $browser = $this->getBrowser($agent);
        $device = $this->getDevice($agent);
        $os = $this->getOS($agent);

        /* =========================
           GENERATE IMAGE
        ==========================*/

        $img = Image::canvas(800, 870, 'rgba(0, 0, 0, 0.65)');
        $fontPath = public_path('backend/fonts/captcha.ttf');

        $y = 70;

        $img->text('Statistik Pengunjung :', 30, $y, function ($font) use ($fontPath) {
            $font->file($fontPath);
            $font->size(40);
            $font->color('#ffffff');
            $font->align('left');
        });

        $y += 60;

        $data = [
            'Sedang Online' => $onlineVisitors,
            'Unik Hari ini' => $uniqueVisitorsToday,
            '---------------------------------------' => null,
            'Pageview Hari ini' => number_format($todayStats->total ?? 0),
            'Kemarin' => number_format($yesterdayStats->total ?? 0),
            'Minggu ini' => number_format($thisWeek),
            'Bulan ini' => number_format($thisMonth),
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
                $font->align('left');
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
        $response->header('Cache-Control', 'public, max-age=300');

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
        if (preg_match('/MSIE/i', $agent))
            return 'Internet Explorer';
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