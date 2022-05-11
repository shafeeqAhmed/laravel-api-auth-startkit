<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // foreach(['']) {

        // }
        DB::table('roles')->insert([
            ['name' => 'client', 'guard_name' => 'web', 'created_at' => now()],
            ['name' => 'publisher', 'guard_name' => 'web', 'created_at' => now()],
            ['name' => 'agent', 'guard_name' => 'web', 'created_at' => now()],
        ]);
    }
}
