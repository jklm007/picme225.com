<?php

use Illuminate\Database\Seeder;
use App\Models\InterurbanCompany;

class InterurbanCompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = [
            ['name' => 'UTB'],
            ['name' => 'ART Luxury'],
            ['name' => 'STIF'],
            ['name' => 'SBTA'],
        ];

        foreach ($companies as $company) {
            InterurbanCompany::updateOrCreate(['name' => $company['name']], $company);
        }
    }
}
