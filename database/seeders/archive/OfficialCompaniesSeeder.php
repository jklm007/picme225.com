<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InterurbanCompany;
use App\Models\Fleet;
use Illuminate\Support\Facades\Hash;

class OfficialCompaniesSeeder extends Seeder
{
    public function run()
    {
        // 1. Create a dummy Fleet Owner if none exists
        $fleet = Fleet::firstOrCreate(
            ['email' => 'contact@picme-trans.com'],
            [
                'name' => 'PICME International Transport',
                'company' => 'PICME TRANS GROUP',
                'mobile' => '22501010101',
                'password' => Hash::make('password123'),
            ]
        );

        $companies = [
            [
                'name' => 'UTB',
                'contact_phone' => '22512345678',
                'contact_email' => 'info@utb.ci',
                'address' => 'Gare UTB Adjamé, Abidjan',
            ],
            [
                'name' => 'SBTA',
                'contact_phone' => '22587654321',
                'contact_email' => 'contact@sbta.ci',
                'address' => 'Gare SBTA Treichville',
            ],
            [
                'name' => 'CTE',
                'contact_phone' => '22544556677',
                'contact_email' => 'booking@cte.ci',
                'address' => 'Boucle du Cacao, San Pedro',
            ],
            [
                'name' => 'STIF',
                'contact_phone' => '22500112233',
                'contact_email' => 'stif@transport.ci',
                'address' => 'Zone Nord, Korhogo',
            ]
        ];

        foreach ($companies as $comp) {
            $company = InterurbanCompany::updateOrCreate(
                ['name' => $comp['name']],
                array_merge($comp, [
                    'fleet_id' => $fleet->id,
                    'is_active' => true,
                ])
            );

            // Link existing ServiceType to this company
            \App\Models\ServiceType::where('name', $comp['name'])
                ->update(['interurban_company_id' => $company->id]);
        }

        $this->command->info('✅ UTB, SBTA, CTE et STIF ont été créées et liées à leurs services respectifs.');
    }
}
