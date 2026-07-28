<?php
namespace Leazycms\Web\Commands;
use Leazycms\Web\Models\User;
use Illuminate\Console\Command;

class ResetPassword extends Command
{
    protected $signature = 'cms:auth';
    protected $description = 'Reset akun superadmin';

    public function handle()
    {
           if (config('modules.installed') == 0) {
            $this->info("CMS belum terinstall, silahkan running php artisan cms:install");
        } else {
            $this->info('Username dan Password default : ');
            $user = User::find(1);
            if (!$user) {
                $user = new User;
                $user->id = 1;
                $user->name = 'Administrator';
                $user->username = str(str()->random(6))->lower();
                $user->email = 'admin@localhost';
                $appUrl = config('app.url', 'localhost');
                $user->host = parse_url($appUrl, PHP_URL_HOST) ?: $appUrl;
                $user->password = bcrypt('password');
                $user->level = 'admin';
                $user->slug = 'admin';
                $user->status = 'active';
                if (config('modules.multisite_enabled')) {
                    $user->tenant_id = 1;
                }
                $user->save();
                $this->info("User Administrator dibuat baru karena belum ada.");
            }
            $password = str(str()->random(8))->lower();
            $user->username = $user->username;
            $user->password = bcrypt($password);
            if (config('modules.multisite_enabled')) {
                $user->tenant_id = 1;
            }
            $user->save();
            $this->line('url : '.route('login'));
            $this->line('Username : '.$user->username);
            $this->line('Password : '.$password);
        }
    }

}
