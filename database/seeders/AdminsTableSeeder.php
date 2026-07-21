<?php



namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->truncate();
        DB::table('admins')->insert([
            [
                'name' => 'Admin PicMe',
                'email' => 'admin@picme225.com',
                'password' => bcrypt('123456'),
            ]
        ]);
    }
}
