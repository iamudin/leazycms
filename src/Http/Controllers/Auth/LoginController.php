<?php
namespace Leazycms\Web\Http\Controllers\Auth;
use Leazycms\Web\Http\Controllers\UserController;
use Leazycms\Web\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Leazycms\Web\Models\OneTimeToken;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function loginByToken(Request $request, string $token)
    {
        abort_if(!config('modules.multisite_enabled'), 404);
        $otToken = DB::table('one_time_tokens')->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
        if (!$otToken) {
            return redirect('/' . admin_path())->with('error', 'Token login tidak valid atau sudah kadaluarsa.');
        }

        $user = User::find($otToken->user_id);
        if (!$user || $user->status !== 'active') {
            return redirect('/' . admin_path())->with('error', 'User tidak ditemukan atau tidak aktif.');
        }

        Auth::login($user);
        DB::table('one_time_tokens')->where('token', $token)->delete(); // Token hanya sekali pakai

        $request->session()->regenerate();
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'active_session' => md5(md5($request->session()->id())),
        ]);

        return redirect('/' . admin_path());
    }
    public function codeCaptcha(): void
    {
        Session::put('captcha', Str::random(6));
    }

    public function generateCaptcha(Request $request, $session)
    {
        abort_if($session != md5($request->session()->id()), '404');
        $image = imagecreatetruecolor(120, 40);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 0, 0, 120, 40, $bgColor);
        imagettftext($image, 20, 0, 10, 30, $textColor, public_path('backend/fonts/captcha.ttf'), Session::get('captcha'));

        ob_start();
        imagepng($image);
        $captchaImage = ob_get_clean();
        imagedestroy($image);

        // $request->session()->regenerateToken();
        return response($captchaImage)->header('Content-Type', 'image/png');
    }

    public function loginForm(Request $request)
    {



        if (Auth::check() && Auth::user()->level != 'admin') {
            if (!in_array(Auth::user()->level, (new UserController)->all_role()->toArray())) {
                Auth::logout();
                return redirect(admin_path())->with('error', 'Peran Akun tidak tidak valid');
            }
            return to_route('panel.dashboard');
        }
        $this->codeCaptcha();

        $captchaUrl = route('captcha', md5($request->session()->id()));
        $data = null;


        $data['title'] = get_option('site_title');
        $data['description'] = get_option('site_description');
        $data['loginsubmit'] = url(admin_path());
        $data['logo'] = get_option('logo');
        if (get_option('sub_app_enabled') && get_option('sub_app_enabled') == 'Y' && !config('modules.multisite_enabled')) {
            if (!is_main_domain()) {
                $getApp = collect(config('modules.extension_module'))->where('url', '=', 'http://' . $request->getHost())->first();
                $data['title'] = $getApp['title'];
                $data['description'] = $getApp['description'];
                $data['loginsubmit'] = url('login');
                $data['logo'] = $getApp['logo'];
            }
        }
        $viewContent = view('cms::auth.login', ['captcha' => $captchaUrl, 'data' => $data])->render();

        // Minimize output for performance
        $compressedOutput = preg_replace('/\s+/', ' ', $viewContent);

        return response($compressedOutput);
    }

    public function loginSubmit(Request $request, RateLimiter $limiter)
    {
        // Throttle login attempts
        $limiterKey = $request->ip() . '|' . $request->username;
        if ($limiter->tooManyAttempts($limiterKey, get_option('time_limit_login') ?? 5)) {
            if ($request->ajax())
                return response()->json(['status' => 'error', 'message' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']);
            return back()->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi nanti.');
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'captcha' => 'required',
        ]);

        if ($request->captcha !== Session::get('captcha')) {
            $request->session()->regenerateToken();

            if ($request->ajax())
                return response()->json(['status' => 'error', 'message' => 'Captcha tidak valid!']);
            return back()->with('error', 'Captcha tidak valid!');
        }

        if (Auth::attempt(array_merge(['username' => $request->username, 'password' => $request->password, 'host' => $request->getHost()], config('modules.multisite_enabled') ? ['tenant_id' => tenant()->id ?? null] : []), $request->remember ?? false)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->status === 'active') {
                $ip = e($request->ip());
                $email = e($request->input('email', $request->username));
                $url = e($request->fullUrl());
                $userAgent = e($request->userAgent());
                dispatch(function () use ($ip, $email, $url, $userAgent) {
                    $time = now()->format('Y-m-d H:i:s');

                    $message = "
<b>⚠️ LOGIN ATTEMPT</b>

<b>📍 IP Address:</b> <code>{$ip}</code>
<b>🕒 Waktu:</b> <code>{$time}</code>
<b>📧 Username:</b> <code>{$email}</code>

<b>🔗 URL:</b>
<code>{$url}</code>

<b>🖥 User Agent:</b>
<code>{$userAgent}</code>

<b>Status:</b> ⏳ Percobaan Login Berhasil
";

                    sendTelegramBotMessage($message);
                })->afterResponse();
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                    'active_session' => md5(md5($request->session()->id())),
                ]);

                if ($request->ajax())
                    return response()->json(['status' => 'success', 'redirect' => url('/' . admin_path())]);
                return redirect()->intended('/' . admin_path());
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            Log::channel('daily')->critical('Gagal login untuk username: ' . $request->username . ' dari IP: ' . get_client_ip() . ' ' . $request->headers->get('User-Agent'));
            if ($request->ajax())
                return response()->json(['status' => 'error', 'message' => 'Akun telah diblokir!']);
            return back()->with('error', 'Akun telah diblokir!');
        }

        $limiter->hit($limiterKey);
        $request->session()->regenerateToken();
        Log::channel('daily')->critical('Gagal login untuk username: ' . $request->username . ' dari IP: ' . get_client_ip() . ' ' . $request->headers->get('User-Agent'));
        $ip = get_client_ip();
        $email = e($request->input('username'));
        $url = e($request->fullUrl());
        $userAgent = e($request->userAgent());
        dispatch(function () use ($ip, $email, $url, $userAgent) {
            $time = now()->format('Y-m-d H:i:s');

            $message = "
<b>⚠️ LOGIN ATTEMPT</b>

<b>📍 IP Address:</b> <code>{$ip}</code>
<b>🕒 Waktu:</b> <code>{$time}</code>
<b>📧 Username:</b> <code>{$email}</code>

<b>🔗 URL:</b>
<code>{$url}</code>

<b>🖥 User Agent:</b>
<code>{$userAgent}</code>

<b>Status:</b> ⏳ Percobaan Login Tapi Gagal!
";

            sendTelegramBotMessage($message);
        })->afterResponse();
        if ($request->ajax())
            return response()->json(['status' => 'error', 'message' => 'Akun tidak ditemukan!']);
        return back()->with('error', 'Akun tidak ditemukan!');
    }

    public function logout(Request $request)
    {
        abort_if($request->isMethod('get'), 404);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
