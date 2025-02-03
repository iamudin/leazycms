<?php
namespace Leazycms\Web\Http\Controllers\Auth;

use Leazycms\Web\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function codeCaptcha(): void
    {
        Session::put('captcha', Str::random(6));
    }

    public function generateCaptcha(Request $request)
    {
        $image = imagecreatetruecolor(120, 40);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 0, 0, 120, 40, $bgColor);
        imagettftext($image, 20, 0, 10, 30, $textColor, public_path('backend/fonts/captcha.ttf'), Session::get('captcha'));

        ob_start();
        imagepng($image);
        $captchaImage = ob_get_clean();
        imagedestroy($image);

        // Redirect if request lacks referer
        if (!$request->headers->get('referer')) {
            $request->session()->regenerateToken();
            return redirect('/');
        }

        return response($captchaImage)->header('Content-Type', 'image/png');
    }

    public function loginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect(admin_path() . '/dashboard');
        }

        $this->codeCaptcha();

        $captchaUrl = route('captcha');
        $viewContent = view('cms::auth.login', ['captcha' => $captchaUrl])->render();

        // Minimize output for performance
        $compressedOutput = preg_replace('/\s+/', ' ', $viewContent);

        return response($compressedOutput);
    }

    public function loginSubmit(Request $request, RateLimiter $limiter)
    {
        // Throttle login attempts
        $limiterKey = $request->ip() . '|' . $request->username;
        if ($limiter->tooManyAttempts($limiterKey, get_option('time_limit_login'))) {
            return back()->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi nanti.');
        }

        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'captcha' => 'required',
        ]);

        if ($request->captcha !== Session::get('captcha')) {
            $request->session()->regenerateToken();
            return back()->with('error', 'Captcha tidak valid!');
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->status === 'active') {
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                    'active_session' => md5(md5($request->session()->id())),
                ]);

                return redirect()->intended(admin_path() . '/dashboard');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->with('error', 'Akun telah diblokir!');
        }

        $limiter->hit($limiterKey);
        $request->session()->regenerateToken();
        return back()->with('error', 'Akun tidak ditemukan!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
