<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class test extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('accounts')->truncate();
        DB::table('accounts')->insert([
            'name' => 'Demo',
            'email' => 'demo@appoets.com',
            'password' => bcrypt('123456'),
        ],[
            'name' => 'Demo',
            'email' => 'demo@demo.com',
            'password' => bcrypt('123456'),
        ]);        //
    }
}
