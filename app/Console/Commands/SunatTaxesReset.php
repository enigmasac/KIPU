<?php

namespace App\Console\Commands;

use App\Models\Banking\TransactionTax;
use App\Models\Common\Company;
use App\Models\Common\ItemTax;
use App\Models\Document\DocumentItemTax;
use App\Utilities\SunatTaxes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SunatTaxesReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:reset-taxes {--company=* : Company ID(s)} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset taxes to the SUNAT catalog for one or more companies';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $company_ids = collect($this->option('company'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter();

        $companies = Company::query();

        if ($company_ids->isNotEmpty()) {
            $companies->whereIn('id', $company_ids->all());
        }

        $companies = $companies->get();

        if ($companies->isEmpty()) {
            $this->error('No companies found.');

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $names = $companies->pluck('name')->implode(', ');

            if (! $this->confirm("This will delete all taxes and tax links for: {$names}. Continue?")) {
                return self::SUCCESS;
            }
        }

        foreach ($companies as $company) {
            $this->line("Resetting taxes for company #{$company->id} ({$company->name})...");

            ItemTax::allCompanies()
                ->withTrashed()
                ->where('company_id', $company->id)
                ->forceDelete();

            DocumentItemTax::allCompanies()
                ->withTrashed()
                ->where('company_id', $company->id)
                ->forceDelete();

            TransactionTax::allCompanies()
                ->withTrashed()
                ->where('company_id', $company->id)
                ->forceDelete();

            DB::table('taxes')
                ->where('company_id', $company->id)
                ->delete();

            SunatTaxes::syncCompany($company->id, 'core::seed');
        }

        $this->info('SUNAT taxes synced.');

        return self::SUCCESS;
    }
}
