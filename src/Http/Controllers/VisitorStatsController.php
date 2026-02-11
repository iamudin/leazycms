<?php
namespace Leazycms\Web\Http\Controllers;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use  Leazycms\Web\Models\Visitor;
use Leazycms\Web\Models\VisitorLog;
use Intervention\Image\Facades\Image;
use Request;

class VisitorStatsController extends Controller
{
// ... method index() milikmu tetap ada di sini


    public function headerImage()
    {
        $host = request()->getHost();
        if (strpos(request()->headers->get('referer'),$host)===false){
            return to_route('home');
        }
        $ip = get_client_ip();
        $agent = request()->header('User-Agent');
        $ipinfo = get_ip_info($ip);
        $now = Carbon::now();

        // ======================
        // ONLINE VISITOR
        // ======================
        $onlineVisitors = Visitor::where('domain', $host)
            ->where('last_activity', '>=', $now->copy()->subMinutes(5))
            ->count();

        // ======================
        // RANGE WAKTU
        // ======================
        $todayStart = Carbon::today();
        $yesterdayStart = Carbon::yesterday();
        $yesterdayEnd = $todayStart;
        $monthStart = $now->copy()->startOfMonth();
        $weekStart = $now->copy()->startOfWeek();

        // ======================
        // PAGE VIEW
        // ======================
        $pageViews = VisitorLog::where(['domain' => $host, 'status_code' => 200])
            ->selectRaw("
        COUNT(CASE WHEN created_at >= ? THEN 1 END) AS today,
        COUNT(CASE WHEN created_at >= ? AND created_at < ? THEN 1 END) AS yesterday,
        COUNT(CASE WHEN created_at >= ? THEN 1 END) AS this_month,
        COUNT(CASE WHEN created_at >= ? THEN 1 END) AS this_week
    ", [
                $todayStart,
                $yesterdayStart,
                $yesterdayEnd,
                $monthStart,
                $weekStart
            ])
            ->first();

        $uniqueVisitorsToday = Visitor::where('domain', $host)
            ->whereBetween('created_at', [$todayStart, now()])
            ->count();

        // ======================
        // INFO DEVICE (simplified)
        // ======================
        $browser = $this->getBrowser($agent);
        $device = $this->getDevice($agent);
        $os = $this->getOS($agent);

        // ======================
        // GENERATE IMAGE
        // ======================
        $img = Image::canvas(800, 870,'rgba(0, 0, 0, 0.65)' ); // warna biru tua

        $fontPath = public_path('backend/fonts/captcha.ttf'); // pastikan font ini ada

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
            '.......................................' => null,
            'Pageview' => '',
            'Hari ini' => number_format($pageViews->today ?? 0),
            'Kemarin' => number_format($pageViews->yesterday ?? 0),
            'Minggu ini' => number_format($pageViews->this_week ?? 0),
            'Bulan ini' => number_format($pageViews->this_month ?? 0),
            '---------------------------------------'=>null,
            'IP Address' => $ip,
            'Location' => (isset($ipinfo['region'])? $ipinfo['region'].',' : null).''. (isset($ipinfo['country']) ? $ipinfo['country'] . ',' : null), 
            'Browser' => $browser,
            'Perangkat' => $device,
            'Sistem Operasi' => $os,
        ];

        foreach ($data as $label => $value) {
            if ($label === '') {
                $y += 25;
                continue;
            }

            $img->text($label, 30, $y, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(32);
                $font->color('#ffffff');
                $font->align('left');
            });

            if ($value !== '') {
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