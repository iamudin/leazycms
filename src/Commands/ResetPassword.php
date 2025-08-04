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
            $username = $user->username;
            $password = str(str()->random(8))->lower();
            $user->update([
                'password'=>bcrypt($password)
            ]);
            $this->line('url : '.route('login'));
            $this->line('Username : '.$username);
            $this->line('Password : '.$password);
        }
    }

}
