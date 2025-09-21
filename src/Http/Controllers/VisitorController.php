<?php
namespace Leazycms\Web\Http\Controllers;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class VisitorController
{

    public function visitor_counter()
    {
      
        if(config('modules.installed') && strpos(request()->headers->get('referer')??'no',admin_path())==false && !is_local() && request()->segment(1) != md5(enc64(parse_url(config('app.url'), PHP_URL_HOST))) ) {
            $data = config('modules.data');

            if($data){
                if (!$this->isDuplicateVisitor(Session::getId(), url()->full()) && strpos(request()->headers->get('referer'),admin_path()) ===false) {
                $data->timestamps = false;
                $data->increment('visited');
                }
            }
            $visitorData = [
                'ip' => get_client_ip(),
                'user_id' => request()->user()?->id,
                'post_id' => $data?->id,
                'ip_location' => get_ip_info(),
                'browser' => self::browser(),
                'session' => Session::getId(),
                'device' => self::device(),
                'os' => self::os(),
                'page' => url()->full(),
                'reference' => request()->headers->get('referer') ?? '',
                'created_at' => now(),
            ];

            // Cache key for visitor list and sorted set
            $sortedKey = 'visitor_sorted';

            // Retrieve existing data
            $sortedSet = \Illuminate\Support\Facades\Cache::get($sortedKey, []);

            // Add new visitor data
            $sortedSet[] = $visitorData;

            // Sort the sorted set by time
            usort($sortedSet, fn($a, $b) => strtotime($a['created_at']) -  strtotime($b['created_at']));

            // Store updated data back to cache
            \Illuminate\Support\Facades\Cache::put($sortedKey, $sortedSet, now()->addMinutes(6)); // Store sorted set in cache for 10 minutes

            return true;
        }

        return false;

    }

    public function isDuplicateVisitor($session, $visitedPage, $timestamp = null)
    {

        $timestamp = $timestamp ?? now()->timestamp;
        $timeLimit = Carbon::createFromTimestamp($timestamp)->subMinutes(5)->timestamp;

        // Cache key for the sorted set
        $sortedKey = 'visitor_sorted';

        // Retrieve visitor data within the time range from cache
        $sortedSet = \Illuminate\Support\Facades\Cache::get($sortedKey, []);

        // Filter data within the time limit
        $filteredData = array_filter($sortedSet, fn($visitor) => strtotime($visitor['created_at']) >= $timeLimit && strtotime($visitor['created_at']) <= $timestamp);

        // Check for duplicate visitors
        foreach ($filteredData as $visitor) {
            if ($visitor['session'] === $session && $visitor['page'] === $visitedPage) {
                return true; // Duplicate visitor found
            }
        }

        return false; // No duplicate visitor found
    }


    public function browser()
    {
        $userAgent = request()->header('User-Agent');

        if (strpos($userAgent, 'MSIE') !== false) {
            $browser = 'Internet Explorer';
        } elseif (strpos($userAgent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Mozilla Firefox';
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Google Chrome';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Apple Safari';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            $browser = 'Opera';
        } else {
            $browser = 'Unknown';
        }

        return $browser;
    }
    public function os()
    {
        $userAgent = request()->header('User-Agent');

        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Macintosh') !== false) {
            $os = 'Mac OS';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            $os = 'iOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } else {
            $os = 'Unknown OS';
        }
        return $os;
    }
    public function device()
    {
        $userAgent = request()->header('User-Agent');

        if (strpos($userAgent, 'Mobi') !== false) {
            $deviceType = 'Mobile';
        } else {
            $deviceType = 'Desktop';
        }

        return $deviceType;
    }
    public function lastvisit()
    {
        $startOfMonth = Carbon::now()->subMonth()->startOfMonth()->timestamp;
        $endOfMonth = Carbon::now()->timestamp;

        // Cache key for storing visitor data
        $cacheKey = 'visitor_data';

        // Retrieve cached data
        $visitorDataList = \Illuminate\Support\Facades\Cache::get($cacheKey, []);

        // Filter data within the start and end timestamps
        $filteredData = array_filter($visitorDataList, function ($visitor) use ($startOfMonth, $endOfMonth) {
            return $visitor->created_at >= $startOfMonth && $visitor->created_at <= $endOfMonth;
        });

        // Sort based on created_at in descending order
        usort($filteredData, function ($a, $b) {
            return $b->created_at - $a->created_at;
        });

        // Extract desired attributes
        return array_map(function ($visitor) {
            return [
                'created_at' => Carbon::createFromTimestamp($visitor->created_at),
                'session' => $visitor->session,
                'page' => $visitor->page,
            ];
        }, $filteredData);
    }

    public function hitStats($data){
        $uniqueSessions = $data->groupBy('session')->map(function ($group) {
            return $group->first();
        });
        $arra['online'] = $uniqueSessions->filter(function ($visitor) {
            return Carbon::parse($visitor->created_at)->isAfter(now()->subMinutes(5));
        })->count();

        //Menghitung pengunjung hari ini
        $arra['today']  = $data->filter(function ($visitor) {
            return Carbon::parse($visitor->created_at)->isToday();
        })->count();

        //Menghitung pengunjung kemarin
        $arra['yesterday']  = $data->filter(function ($visitor) {
            return Carbon::parse($visitor->created_at)->isYesterday();
        })->count();

        //Menghitung pengunjung bulan ini
        $arra['this_month']  = $data->filter(function ($visitor) {
            return Carbon::parse($visitor->created_at)->isCurrentMonth();
        })->count();

        //Menghitung pengunjung bulan kemarin
        $arra['last_month'] = $data->filter(function ($visitor) {
            return Carbon::parse($visitor->created_at)->subMonth()->isCurrentMonth();
        })->count();

        return json_decode(json_encode($arra));
    }

}
