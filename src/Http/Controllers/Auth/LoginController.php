<?php
namespace Leazycms\Web\Http\Controllers\Auth;
use Leazycms\Web\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
class LoginController extends Controller
{


    public function codeCaptcha()
    {
        Session::put('captcha',Str::random(6));
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
        if(!$request->headers->get('referer') ){
        $request->session()->regenerateToken();
        return redirect('/');
        }
        return response($captchaImage)->header('Content-type', 'image/png');
    }
    public function loginForm(Request $request)
    {
        if(Auth::check())
        return redirect(admin_path().'/dashboard');
        $this->codeCaptcha();
        return view('cms::auth.login',['captcha'=>route('captcha')]);

    }
    public function loginSubmit(Request $request,RateLimiter $limiter,User $user)
    {
      ratelimiter($request,get_option('time_limit_login'));
        if($request->username && $request->password)
        {
            $request->validate([
                'username' => 'required',
                'password' => 'required'
            ]);

          if($request->captcha != Session::get('captcha')){
            $request->session()->regenerateToken();
            return back()->with('error','Captcha Tidak Valid! ');

          }
        if(Auth::attempt(array('username'=>$request->username,'password'=>$request->password,'host'=>$request->getHttpHost())))
        {
            $request->session()->regenerate();
            if(Auth::user()->status == 'active'){
             Auth::user()->update(['last_login_at'=>now(),'last_login_ip'=>$request->ip(),'active_session'=>md5(md5($request->session()->id()))]);
            return  redirect()->intended(url(admin_path().'/dashboard'));
            }
            else{
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->with('error','Akun telah diblokir!');
            }
        }
        $request->session()->regenerateToken();
        return back()->with('error','Akun tidak ditemukan!');
    }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->regenerateToken();
        return redirect('/');
    }

}
