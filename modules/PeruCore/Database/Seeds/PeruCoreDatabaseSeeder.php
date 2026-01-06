<?php

namespace Modules\PeruCore\Database\Seeds;

use Illuminate\Database\Seeder;
use App\Models\Setting\Currency;
use App\Models\Setting\Tax;
use App\Models\Common\Company;

class PeruCoreDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::first();
        
        if (!$company) {
            $this->command->error("No se encontró ninguna empresa para configurar.");
            return;
        }

        $company_id = $company->id;

        // 1. Configurar Moneda Sol (PEN)
        Currency::updateOrCreate(
            ['code' => 'PEN', 'company_id' => $company_id],
            [
                'name' => 'Sol Peruano',
                'rate' => 1.0,
                'precision' => 2,
                'symbol' => 'S/',
                'symbol_first' => 1,
                'decimal_mark' => '.',
                'thousands_separator' => ',',
                'enabled' => 1,
            ]
        );

        // 2. Catálogo de Impuestos SUNAT (Basado en Catálogo 05 y 07)
        $taxes = [
            [
                'name' => 'IGV (18%)',
                'rate' => 18.00,
                'sunat_code' => '1000', // IGV Impuesto General a las Ventas
                'type' => 'normal',
            ],
            [
                'name' => 'Exonerado (0%)',
                'rate' => 0.00,
                'sunat_code' => '9997', // EXONERADO
                'type' => 'normal',
            ],
            [
                'name' => 'Inafecto (0%)',
                'rate' => 0.00,
                'sunat_code' => '9998', // INAFECTO
                'type' => 'normal',
            ],
            [
                'name' => 'Exportación (0%)',
                'rate' => 0.00,
                'sunat_code' => '9995', // EXPORTACIÓN
                'type' => 'normal',
            ],
            [
                'name' => 'ICBPER',
                'rate' => 0.50, // Monto actual bolsas
                'sunat_code' => '7152', // ICBPER
                'type' => 'fixed',
            ],
        ];

        foreach ($taxes as $taxData) {
            Tax::updateOrCreate(
                ['company_id' => $company_id, 'sunat_code' => $taxData['sunat_code']],
                [
                    'name' => $taxData['name'],
                    'rate' => $taxData['rate'],
                    'type' => $taxData['type'],
                    'enabled' => 1,
                ]
            );
        }
        
        $this->command->info("Configuración completa de Perú (Moneda e Impuestos SUNAT) para: " . $company->name);
    }
}