<?php

namespace Udiko\Larafastcms\Database\Seeders;

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {


        $this->call([
            User::class,
            Post::class,
            Option::class,
        ]);
    }
}
