<?php

namespace Database\Seeds;

use App\Abstracts\Model;
use App\Utilities\SunatTaxes as SunatTaxCatalog;
use Illuminate\Database\Seeder;

class SunatTaxes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $company_id = (int) $this->command->argument('company');

        SunatTaxCatalog::syncCompany($company_id, 'core::seed');

        Model::reguard();
    }
}
