<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider as Provider;
use Illuminate\Support\Facades\View;

class ViewComposer extends Provider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Add Contact Type
        View::composer(
            ['contacts.*'],
            'App\Http\ViewComposers\ContactType'
        );

        // Add Document Type
        View::composer(
            ['documents.*', 'portal.documents.*'],
            'App\Http\ViewComposers\DocumentType'
        );

        // Document Recurring Metadata
        View::composer(
            ['components.documents.form.metadata'],
            'App\Http\ViewComposers\DocumentRecurring'
        );

        View::composer(
            ['components.layouts.admin.notifications'],
            'App\Http\ViewComposers\ReadOnlyNotification'
        );

        View::composer(
            ['components.layouts.admin.header'],
            'App\Http\ViewComposers\PlanLimits'
        );

        // Prefill a credit note when creating from an invoice
        View::composer(
            [
                'components.documents.form.metadata',
                'components.documents.form.advanced',
                'components.documents.form.totals',
                'components.documents.script',
            ],
            \App\Http\ViewComposers\Document\PrefillCreditNote::class
        );

        // Prefill a debit note when creating from a bill
        View::composer(
            [
                'components.documents.form.metadata',
                'components.documents.form.advanced',
                'components.documents.form.totals',
                'components.documents.script',
            ],
            \App\Http\ViewComposers\Document\PrefillDebitNote::class
        );

        // Show an invoice selection in a credit note
        View::composer(
            ['components.documents.form.metadata'],
            \App\Http\ViewComposers\Document\AddInvoiceSelectionField::class
        );

        // Show a bill selection in a debit note
        View::composer(
            ['components.documents.form.metadata'],
            \App\Http\ViewComposers\Document\AddBillSelectionField::class
        );

        // Add a hidden 'original_contact_id' field
        View::composer(['components.documents.show.metadata'], \App\Http\ViewComposers\Document\AddOriginalContactIdField::class);

        // Show "Credit Customer Account" toggle in a credit note
        View::composer(
            ['components.documents.form.metadata'],
            \App\Http\ViewComposers\Document\AddCreditCustomerAccountField::class
        );

        // Show an invoice number in a credit note
        View::composer(
            [
                'sales.credit_notes.show',
            ],
            \App\Http\ViewComposers\Document\ShowInvoiceNumber::class
        );

        // Show a bill number in a debit note
        View::composer(
            [
                'purchases.debit_notes.show',
            ],
            \App\Http\ViewComposers\Document\ShowBillNumber::class
        );

        // Show a refunds list in a credit and debit note
        View::composer(
            [
                'sales.credit_notes.show',
                'purchases.debit_notes.show',
            ],
            \App\Http\ViewComposers\Document\ShowRefundsList::class
        );

        // Show a credits transactions list in an invoice
        View::composer(
            [
                'sales.invoices.show',
            ],
            \App\Http\ViewComposers\Document\ShowCreditsTransactions::class
        );

        // Show a create credit note button in an invoice
        View::composer(
            [
                'sales.invoices.show',
            ],
            \App\Http\ViewComposers\Document\ShowCreateCreditNoteButton::class
        );

        // Show a use credits button in an invoice
        View::composer(
            [
                'sales.invoices.show',
            ],
            \App\Http\ViewComposers\Document\UseCredits::class
        );

        // Show a create debit note button in a bill
        View::composer(
            [
                'purchases.bills.show',
                'sales.invoices.show',
            ],
            \App\Http\ViewComposers\Document\ShowCreateDebitNoteButton::class
        );

        // Show applied credits in an invoice
        View::composer(
            [
                'sales.invoices.show',
                'sales.invoices.print_default',
                'sales.invoices.print_classic',
                'sales.invoices.print_modern',
            ],
            \App\Http\ViewComposers\Document\ShowAppliedCredits::class
        );

        // Show credit notes list in an invoice
        View::composer(
            [
                'sales.invoices.show',
            ],
            \App\Http\ViewComposers\Document\ShowCreditNotes::class
        );

        // Show debit notes list in an invoice
        View::composer(
            [
                'sales.invoices.show',
            ],
            \App\Http\ViewComposers\Document\ShowDebitNotes::class
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
