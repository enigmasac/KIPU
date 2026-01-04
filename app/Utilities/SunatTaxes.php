<?php

namespace App\Utilities;

use App\Models\Setting\Tax;

class SunatTaxes
{
    public static function definitions(): array
    {
        return [
            [
                'name' => 'IGV',
                'sunat_code' => '1000',
                'rate' => 18.00,
                'type' => 'normal',
                'priority' => 2,
            ],
            [
                'name' => 'IVAP',
                'sunat_code' => '1016',
                'rate' => 4.00,
                'type' => 'normal',
                'priority' => 2,
            ],
            [
                'name' => 'ISC',
                'sunat_code' => '2000',
                'rate' => 0.00,
                'type' => 'normal',
                'priority' => 1,
            ],
            [
                'name' => 'ICBPER',
                'sunat_code' => '7152',
                'rate' => 0.50,
                'type' => 'fixed',
                'priority' => 3,
            ],
            [
                'name' => 'Exportacion',
                'sunat_code' => '9995',
                'rate' => 0.00,
                'type' => 'normal',
                'priority' => 0,
            ],
            [
                'name' => 'Gratuito',
                'sunat_code' => '9996',
                'rate' => 0.00,
                'type' => 'normal',
                'priority' => 0,
            ],
            [
                'name' => 'Exonerado',
                'sunat_code' => '9997',
                'rate' => 0.00,
                'type' => 'normal',
                'priority' => 0,
            ],
            [
                'name' => 'Inafecto',
                'sunat_code' => '9998',
                'rate' => 0.00,
                'type' => 'normal',
                'priority' => 0,
            ],
            [
                'name' => 'Otros tributos',
                'sunat_code' => '9999',
                'rate' => 0.00,
                'type' => 'normal',
                'priority' => 0,
            ],
        ];
    }

    public static function syncCompany(int $company_id, string $created_from = 'core::seed'): void
    {
        foreach (static::definitions() as $definition) {
            $tax = Tax::allCompanies()
                ->withTrashed()
                ->where('company_id', $company_id)
                ->where('sunat_code', $definition['sunat_code'])
                ->first();

            if (! $tax) {
                $tax = new Tax();
                $tax->company_id = $company_id;
                $tax->created_from = $created_from;
            }

            $tax->fill([
                'name' => $definition['name'],
                'rate' => $definition['rate'],
                'type' => $definition['type'],
                'priority' => $definition['priority'],
                'sunat_code' => $definition['sunat_code'],
                'enabled' => true,
                'is_system' => true,
            ]);

            $tax->deleted_at = null;
            $tax->save();
        }
    }
}
