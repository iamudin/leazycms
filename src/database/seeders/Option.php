<?php

namespace Udiko\Larafastcms\Database\Seeders;
use Illuminate\Database\Seeder;

class Option extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $option = array(
            ['name'=>'site_maintenance','value'=>0,'autoload'=>1],
            ['name'=>'post_perpage','value'=>10,'autoload'=>1],
            ['name'=>'site_title','value'=>'Your Website Official'],
            ['name'=>'template','value'=>'default','autoload'=>0],
            ['name'=>'admin_path','value'=>'admin','autoload'=>0],
            ['name'=>'admin_path','value'=>'admin','autoload'=>0],
            ['name'=>'logo','value'=>'logo.png','autoload'=>1],
            ['name'=>'favicon','value'=>'favicon.png','autoload'=>1],
            ['name'=>'background','value'=>'background.png','autoload'=>1],
            ['name'=>'site_url','value'=>'fix.test','autoload'=>0],
            ['name'=>'site_keyword','value'=>'Web, Official, New','autoload'=>1],
            ['name'=>'site_description','value'=>'My Offical Web','autoload'=>1],
            ['name'=>'company_name','value'=>'My Offical Web','autoload'=>1],
            ['name'=>'address','value'=>'Anggrek Streen, 2','autoload'=>1],
            ['name'=>'phone','value'=>'123456789','autoload'=>1],
            ['name'=>'email','value'=>'your@email.com','autoload'=>1],
            ['name'=>'fax','value'=>'123456789','autoload'=>1],
            ['name'=>'latitude','value'=>null,'autoload'=>1],
            ['name'=>'longitude','value'=>null,'autoload'=>1],
            ['name'=>'facebook','value'=>'https://fb.com/yourcompany','autoload'=>1],
            ['name'=>'youtube','value'=>'https://youtube.com/@yourchannel','autoload'=>1],
            ['name'=>'instagram','value'=>null,'autoload'=>1],
            ['name'=>'google_verify','value'=>null,'autoload'=>1],
            ['name'=>'backup_password','value'=>null,'autoload'=>1],
            ['name'=>'backup_password','value'=>'12345678','autoload'=>1],
            ['name'=>'api_allow_ip','value'=>'127.0.0.1','autoload'=>1],
            ['name'=>'comment_status','value'=>0,'autoload'=>1],
            ['name'=>'home_page','value'=>'default','autoload'=>1],
        );


        foreach($option as $row){
            \Udiko\Larafastcms\Models\Option::create($row);
        }
    }
}
