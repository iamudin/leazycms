<?php

namespace Udiko\Larafastcms\Database\Seeders;
use Illuminate\Database\Seeder;

class User extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach(array([
            'username'=>'admin',
            'password'=>bcrypt('admin'),
            'email'=>'admin@email.com',
            'status'=>1,
            'slug'=>'admin-web',
            'name'=>'Admin Web',
            'url'=>'author/admin-web',
            'photo'=>null,
            'level'=>'admin'
            ]) as $row){
             \Udiko\Larafastcms\Models\User::create($row);
        }

    }
}
