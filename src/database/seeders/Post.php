<?php

namespace Udiko\Larafastcms\Database\Seeders;

use Illuminate\Database\Seeder;

class Post extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $id = \Udiko\Larafastcms\Models\User::first();
      $a=0;
      while($a<=50):
      $id->post()->create(
        [
            'title'=>$title = fake()->sentence,
            'slug'=> $slug = str()->slug($title),
            'content'=> fake()->paragraph,
            'thumbnail'=> 'thumbnail.png',
            'url'=> 'berita/'.$slug,
            'status'=> 'publish',
            'type'=> 'berita',
        ]
        );
       $a++;

    endwhile;
//create menu
    $id->post()->create(
        [
            'title'=> $title = 'Header',
            'slug'=> $slug = str()->slug($title),
            'status'=> 'publish',
            'type'=> 'menu',
            'data_loop'=> json_encode([
                ['id'=>'m1','parent'=>0,'parent'=>0,'name'=>'Profil','description'=>null,'link'=>'#','icon'=>null],
                ['id'=>'m2','parent'=>'m1','parent'=>0,'name'=>'Visi Misi','description'=>null,'link'=>'#','icon'=>null],
                ['id'=>'m3','parent'=>'m1','parent'=>0,'name'=>'Sejarah','description'=>null,'link'=>'#','icon'=>null],
                ['id'=>'m4','parent'=>0,'parent'=>0,'name'=>'Publikasi','description'=>null,'link'=>'#','icon'=>null],
                ['id'=>'m5','parent'=>'m4','parent'=>0,'name'=>'Berita','description'=>null,'link'=>'#','icon'=>null],
                ['id'=>'m6','parent'=>'m4','parent'=>0,'name'=>'Agenda','description'=>null,'link'=>'#','icon'=>null],
            ]),
        ]
        );
    }
}
