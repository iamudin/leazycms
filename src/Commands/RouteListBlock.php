<?php
namespace Leazycms\Web\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Console\RouteListCommand;

class RouteListBlock extends Command
{
    protected $signature = 'route:list';
    protected $description = 'This command is disabled';

    public function handle()
    {
        if (Cache::has('enablededitortemplate')) {
            // Jika enableviewroute aktif, jalankan route:list asli
            $this->call(RouteListCommand::class);
        } else {
            // Jika tidak, tampilkan pesan blokir
            $this->error('Access to this command is restricted.');
        }
    }
}
