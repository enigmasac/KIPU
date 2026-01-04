<?php

namespace App\Http\Controllers\Sales;

use App\Abstracts\Http\Controller;
use App\Events\Document\DocumentPrinting;
use App\Events\Document\DocumentMarkedSent;
use App\Jobs\Document\CreateDocument;
use App\Jobs\Document\DeleteDocument;
use App\Jobs\Document\DuplicateDocument;
use App\Jobs\Document\UpdateDocument;
use App\Models\Common\Contact;
use App\Traits\Documents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\Models\Document\CreditNote as Document;

class CreditNotes extends Controller
{
    use Documents;

    public $type = Document::CREDIT_NOTE_TYPE;

    public function index()
    {
        $credit_notes = Document::with('contact', 'transactions')->collect(['issued_at' => 'desc']);

        return $this->response('sales.credit_notes.index', compact('credit_notes'));
    }

    public function create()
    {
        $invoice_id = request('invoice_id') ?? request('invoice') ?? request('parent_id') ?? old('invoice_id') ?? old('parent_id');

        if (empty($invoice_id)) {
            flash("Las Notas de Crédito deben emitirse desde la vista de una factura.")->error();
            return redirect()->route('sales.credit-notes.index');
        }

        $invoice = \App\Models\Document\Document::invoice()->findOrFail($invoice_id);
        $contact = $invoice->contact;
        $invoice_items = $invoice->items;

        return view('sales.credit_notes.create', compact('invoice', 'contact', 'invoice_items'));
    }

    public function store(\App\Http\Requests\Sales\CreditNote $request): JsonResponse
    {
        $invoice_id = $request->input('invoice_id') ?: $request->input('parent_id');

        $response = $this->ajaxDispatch(new CreateDocument($request));

        if ($response['success']) {
            $response['redirect'] = route('sales.credit-notes.show', $response['data']->id);

            $message = trans('messages.success.added', ['type' => trans_choice('general.credit_notes', 1)]);

            flash($message)->success();
        } else {
            if ($invoice_id) {
                $response['redirect'] = route('sales.credit-notes.create', ['invoice_id' => $invoice_id]);
            } else {
                $response['redirect'] = route('sales.credit-notes.index');
            }

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function show(Document $credit_note)
    {
        // Get Credit Note Totals
        foreach ($credit_note->totals_sorted as $credit_note_total) {
            $credit_note->{$credit_note_total->code} = $credit_note_total->amount;
        }

        $currency_code = $credit_note->currency_code;

        $total = money($credit_note->total, $currency_code, true)->format();
        $credit_note->grand_total = money($total, $currency_code)->getAmount();

        foreach ($credit_note->transactions as $transaction) {
            $transaction->type = trans('credit_notes.refund');
        }

        return view('sales.credit_notes.show', compact('credit_note'));
    }

    public function edit(Document $credit_note)
    {
        return view('sales.credit_notes.edit', compact('credit_note'));
    }

    public function update(Document $credit_note, \App\Http\Requests\Sales\CreditNote $request): JsonResponse
    {
        $response = $this->ajaxDispatch(new UpdateDocument($credit_note, $request));

        if ($response['success']) {
            $response['redirect'] = route('sales.credit-notes.show', $response['data']->id);

            $message = trans('messages.success.updated', ['type' => trans_choice('general.credit_notes', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('sales.credit-notes.edit', $credit_note->id);

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function destroy(Document $credit_note): JsonResponse
    {
        $response = $this->ajaxDispatch(new DeleteDocument($credit_note));

        $response['redirect'] = route('sales.credit-notes.index');

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => trans_choice('general.credit_notes', 1)]);

            flash($message)->success();
        } else {
            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    public function duplicate(Document $credit_note): RedirectResponse
    {
        $clone = $this->dispatch(new DuplicateDocument($credit_note));

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.credit_notes', 1)]);

        flash($message)->success();

        return redirect()->route('sales.credit-notes.edit', $clone->id);
    }

    public function markSent(Document $credit_note): RedirectResponse
    {
        event(new DocumentMarkedSent($credit_note));

        $message = trans('documents.messages.marked_sent', ['type' => trans_choice('general.credit_notes', 1)]);

        flash($message)->success();

        return redirect()->back();
    }

    public function markCancelled(Document $credit_note): RedirectResponse
    {
        flash('No se puede anular una nota de credito emitida. Use una nota de debito si corresponde.')->error()->important();

        return redirect()->back();
    }

    public function printCreditNote(Document $credit_note): string
    {
        event(new DocumentPrinting($credit_note));

        $view = view($credit_note->template_path, compact('credit_note'));

        return mb_convert_encoding($view, 'HTML-ENTITIES', 'UTF-8');
    }

    public function pdfCreditNote(Document $credit_note): Response
    {
        event(new DocumentPrinting($credit_note));

        $currency_style = true;

        $view = view($credit_note->template_path, compact('credit_note', 'currency_style'))->render();
        $html = mb_convert_encoding($view, 'HTML-ENTITIES', 'UTF-8');
        $html = prepare_pdf_html($html);

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);

        $file_name = $this->getDocumentFileName($credit_note);

        return $pdf->download($file_name);
    }

    public function contactInvoices(Contact $contact): JsonResponse
    {
        $invoices = \App\Models\Document\Document::invoice()
            ->where('contact_id', $contact->id)
            ->whereIn('status', ['sent', 'partial', 'paid'])
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'name' => $invoice->document_number . ' (' . company_date($invoice->issued_at) . ')',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    public function invoice(\App\Models\Document\Document $invoice): JsonResponse
    {
        $invoice->load(['items.taxes', 'items.item', 'contact']);

        $items = $invoice->items->map(function ($item) {
            $sku = $item->sku ?: ($item->item->sku ?? '');
            $sunat_unit_code = $item->sunat_unit_code ?: ($item->item->sunat_unit_code ?? 'NIU');

            return [
                'item_id' => $item->item_id,
                'name' => $item->name,
                'sku' => $sku,
                'sunat_unit_code' => $sunat_unit_code,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'price' => (float) $item->price,
                'tax_ids' => $item->taxes->pluck('tax_id')->toArray(),
                'discount_rate' => (float) $item->discount_rate,
                'discount_type' => $item->discount_type,
                'taxes' => $item->taxes->map(function ($tm) {
                    return [
                        'id' => $tm->tax_id,
                        'tax_id' => $tm->tax_id,
                        'name' => $tm->name,
                        'amount' => (float) $tm->amount,
                    ];
                }),
                'total' => (float) $item->total,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'contact_id' => $invoice->contact_id,
                'contact_name' => $invoice->contact_name,
                'contact_email' => $invoice->contact_email,
                'contact_tax_number' => $invoice->contact_tax_number,
                'contact_phone' => $invoice->contact_phone,
                'contact_address' => $invoice->contact_address,
                'contact_country' => $invoice->contact_country,
                'contact_state' => $invoice->contact_state,
                'contact_zip_code' => $invoice->contact_zip_code,
                'contact_city' => $invoice->contact_city,
                'currency_code' => $invoice->currency_code,
                'currency_rate' => $invoice->currency_rate,
                'invoice_total' => (float) $invoice->amount,
                'items' => $items,
            ],
        ]);
    }
}
