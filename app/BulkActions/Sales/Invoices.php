<?php

namespace App\BulkActions\Sales;

use App\Abstracts\BulkAction;
use App\Models\Document\Document;
use App\Jobs\Document\DeleteDocument;
use App\Jobs\Common\CreateZipForDownload;
use App\Jobs\Document\UpdateDocument;
use App\Events\Document\DocumentCreated;
use App\Events\Document\DocumentCancelled;
use App\Events\Document\DocumentMarkedSent;
use App\Exports\Sales\Invoices\Invoices as Export;

class Invoices extends BulkAction
{
    public $model = Document::class;

    public $text = 'general.invoices';

    public $path = [
        'group' => 'sales',
        'type' => 'invoices',
    ];

    public $actions = [
        'sent' => [
            'icon' => 'send',
            'name' => 'invoices.mark_sent',
            'message' => 'bulk_actions.message.sent',
            'permission' => 'update-sales-invoices',
        ],
        'delete' => [
            'icon' => 'delete',
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'permission' => 'delete-sales-invoices',
        ],
        'export' => [
            'icon' => 'file_download',
            'name' => 'general.export',
            'message' => 'bulk_actions.message.export',
            'type' => 'download',
        ],
        'download' => [
            'icon' => 'download',
            'name' => 'general.download',
            'message' => 'bulk_actions.message.download',
            'type' => 'download',
        ],
        'sunat_emit' => [
            'icon' => 'cloud_upload',
            'name' => 'Reenviar a SUNAT',
            'message' => '¿Está seguro de emitir/reenviar a SUNAT?',
            'permission' => 'update-sales-invoices',
        ],
    ];

    public function sent($request)
    {
        $invoices = $this->getSelectedRecords($request);

        foreach ($invoices as $invoice) {
            if ($invoice->status == 'sent') {
                continue;
            }

            event(new DocumentMarkedSent($invoice));
        }
    }

    public function sunat_emit($request)
    {
        $invoices = $this->getSelectedRecords($request);

        foreach ($invoices as $invoice) {
            try {
                app(\Modules\Sunat\Services\GreenterService::class)->processEmission($invoice);
            } catch (\Exception $e) {
                // Ignore errors for individual documents to continue processing others
                report($e);
            }
        }
    }

    public function cancelled($request)
    {
        $invoices = $this->getSelectedRecords($request);

        foreach ($invoices as $invoice) {
            if (in_array($invoice->status, ['cancelled', 'draft'])) {
                continue;
            }

            event(new DocumentCancelled($invoice));
        }
    }

    public function duplicate($request)
    {
        $invoices = $this->getSelectedRecords($request);

        foreach ($invoices as $invoice) {
            $clone = $invoice->duplicate();

            event(new DocumentCreated($clone, $request));
        }
    }

    public function destroy($request)
    {
        $invoices = $this->getSelectedRecords($request, [
            'items',
            'item_taxes',
            'histories',
            'transactions',
            'recurring',
            'totals'
        ]);

        foreach ($invoices as $invoice) {
            try {
                $this->dispatch(new DeleteDocument($invoice));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }

    public function export($request)
    {
        $selected = $this->getSelectedInput($request);

        return $this->exportExcel(new Export($selected), trans_choice('general.invoices', 2));
    }

    public function download($request)
    {
        $selected = $this->getSelectedRecords($request);

        $file_name = Document::INVOICE_TYPE . '-' . date('Y-m-d-H-i-s');

        $class = '\App\Jobs\Document\DownloadDocument';

        return $this->downloadPdf($selected, $class, $file_name, trans_choice('general.invoices', 2));
    }
}

