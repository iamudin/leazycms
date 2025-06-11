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

        // $request->session()->regenerateToken();
        return response($captchaImage)->header('Content-Type', 'image/png');
    }

    public function loginForm(Request $request)
    {


        if (Auth::check()) {
            if(!$request->user()->isAdmin() && config('app.sub_app_enabled') && !is_main_domain()){
            return to_route( $request->user()->level.'.dashboard');
            }

            if(!is_main_domain() && $request->user()->isAdmin()){
            Auth::logout();
        }
        if(is_main_domain() && !$request->user()->isAdmin()){
            Auth::logout();
        }

        return to_route('panel.dashboard');

        }else{
        if(!is_main_domain() && $request->segment(1) == admin_path()){
            return redirect('login');
        }
        }


        $this->codeCaptcha();

        $captchaUrl = route('captcha').'?time='.time();
        $data = null;


            $data['title'] = get_option('site_title');
            $data['description'] = get_option('site_description');
            $data['loginsubmit'] = url(admin_path());
            $data['logo'] = get_option('logo');
            if(config('app.sub_app_enabled')){
                if(!is_main_domain()){
                    $getApp = collect(config('modules.extension_module'))->where('url','=','http://'.$request->getHost())->first();
                    $data['title'] = $getApp['title'];
                    $data['description'] = $getApp['description'];
                    $data['loginsubmit'] = url('login');
                    $data['logo'] = $getApp['logo'];
                }
            }
        $viewContent = view('cms::auth.login', ['captcha' => $captchaUrl,'data'=>$data])->render();

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

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password],$request->remember ?? false)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->status === 'active') {
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                    'active_session' => md5(md5($request->session()->id())),
                ]);
                if(is_main_domain()){
                    if($user->isAdmin() || (config('sub_app_enabled') && !in_array($user->level,collect(config('modules.extension_module'))->pluck('path')->toArray()))){
                        return redirect()->intended('/'.admin_path());
                    }else{
                        Auth::logout();
                    }
                }else{
                    if($user->isAdmin() ){
                        Auth::logout();
                    }else{
                        return redirect()->intended('/login');
                    }
                }
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
        abort_if($request->isMethod('get'),404);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
