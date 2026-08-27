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



    public function loginForm(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->level != 'admin' && !in_array(Auth::user()->level, (new UserController)->all_role()->toArray())) {
                Auth::logout();
                return redirect(admin_path())->with('error', 'Peran Akun tidak tidak valid');
            }
            return to_route('panel.dashboard');
        }

        // Jika multisite aktif dan diakses dari domain/subdomain tenant (bukan main domain)
        if (config('modules.multisite_enabled')  &&  env('LOGIN_FORM_REDIRECT_TO')) {
            $redirectSetting = env('LOGIN_FORM_REDIRECT_TO', 'dashboard');
            if ($redirectSetting !== false && $redirectSetting !== 'false' && $redirectSetting !== '0') {
                $target = is_string($redirectSetting) ? trim($redirectSetting) : 'dashboard';
                if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
                    $redirectUrl = $target;
                } else {
                    $cleanTarget = str_replace(['maindomain/', 'main_domain/', 'maindomain', 'main_domain'], '', $target);
                    $cleanTarget = ltrim($cleanTarget, '/');
                    $redirectUrl = main_domain($cleanTarget);
                }

                $separator = str_contains($redirectUrl, '?') ? '&' : '?';
                $redirectUrl .= $separator . 'notice=login_required&tenant=' . urlencode($request->getHost());

                return redirect()->away($redirectUrl);
            }
        }

        $data = null;
        $data['title'] = get_option('site_title');
        $data['description'] = get_option('site_description');
        $data['loginsubmit'] = url(admin_path());
        $data['logo'] = get_option('logo');

        $viewContent = view('cms::auth.login', ['data' => $data])->render();

        // Minimize output for performance
        $compressedOutput = preg_replace('/\s+/', ' ', $viewContent);

        return response($compressedOutput);
    }

    public function loginSubmit(Request $request, RateLimiter $limiter)
    {
        // Jika multisite aktif dan request submit login ke tenant secara langsung (bukan main domain)
        if (config('modules.multisite_enabled') && !is_main_domain()) {
            $redirectSetting = env('LOGIN_FORM_REDIRECT_TO');
            if ($redirectSetting) {
                $target = is_string($redirectSetting) ? trim($redirectSetting) : 'dashboard';
                if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
                    $redirectUrl = $target;
                } else {
                    $cleanTarget = str_replace(['maindomain/', 'main_domain/', 'maindomain', 'main_domain'], '', $target);
                    $cleanTarget = ltrim($cleanTarget, '/');
                    $redirectUrl = main_domain($cleanTarget);
                }

                $separator = str_contains($redirectUrl, '?') ? '&' : '?';
                $redirectUrl .= $separator . 'notice=login_required&tenant=' . urlencode($request->getHost());

                if ($request->ajax()) {
                    return response()->json(['status' => 'error', 'redirect' => $redirectUrl, 'message' => 'Silakan login terlebih dahulu ke Dashboard.']);
                }
                return redirect()->away($redirectUrl)->with('info', 'Silakan login terlebih dahulu ke Dashboard untuk mengakses CMS Website.');
            }
        }

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

        if (!captcha_check($request->captcha)) {
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

                $intendedUrl = redirect()->intended('/' . admin_path())->getTargetUrl();

                if ($request->ajax()) {
                    return response()->json(['status' => 'success', 'redirect' => $intendedUrl]);
                }
                
                return redirect($intendedUrl);
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
