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
use App\Models\Document\Document;
use App\Traits\Documents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\Http\Requests\Sales\DebitNote as DebitNoteRequest;

class DebitNotes extends Controller
{
    use Documents;

    public $type = Document::DEBIT_NOTE_TYPE;

    public function index()
    {
        $debit_notes = Document::where('type', Document::DEBIT_NOTE_TYPE)
            ->with('contact', 'transactions', 'referenced_document')
            ->collect(['issued_at' => 'desc']);

        return $this->response('sales.debit_notes.index', compact('debit_notes'));
    }

    public function create()
    {
        $invoice_id = request('invoice_id') ?? request('invoice') ?? request('parent_id') ?? old('invoice_id') ?? old('parent_id');

        if (empty($invoice_id)) {
            flash('Las Notas de Débito deben emitirse desde la vista de una factura.')->error();
            return redirect()->route('sales.debit-notes.index');
        }

        $invoice = \App\Models\Document\Document::invoice()->findOrFail($invoice_id);
        $contact = $invoice->contact;
        $invoice_items = $invoice->items;

        return view('sales.debit_notes.create', compact('invoice', 'contact', 'invoice_items'));
    }

    public function store(DebitNoteRequest $request): JsonResponse
    {
        $invoice_id = $request->input('invoice_id') ?: $request->input('parent_id');

        $response = $this->ajaxDispatch(new CreateDocument($request));

        if ($response['success']) {
            $response['redirect'] = route('sales.debit-notes.show', $response['data']->id);

            $message = trans('messages.success.added', ['type' => trans_choice('general.debit_notes', 1)]);

            flash($message)->success();
        } else {
            if ($invoice_id) {
                $response['redirect'] = route('sales.debit-notes.create', ['invoice_id' => $invoice_id]);
            } else {
                $response['redirect'] = route('sales.debit-notes.index');
            }

            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    public function show(Document $debit_note)
    {
        foreach ($debit_note->totals_sorted as $debit_note_total) {
            $debit_note->{$debit_note_total->code} = $debit_note_total->amount;
        }

        $currency_code = $debit_note->currency_code;

        $total = money($debit_note->total, $currency_code, true)->format();
        $debit_note->grand_total = money($total, $currency_code)->getAmount();

        foreach ($debit_note->transactions as $transaction) {
            $transaction->type = trans('debit-notes.refund');
        }

        return view('sales.debit_notes.show', compact('debit_note'));
    }

    public function edit(Document $debit_note)
    {
        return view('sales.debit_notes.edit', compact('debit_note'));
    }

    public function update(Document $debit_note, DebitNoteRequest $request): JsonResponse
    {
        $response = $this->ajaxDispatch(new UpdateDocument($debit_note, $request));

        if ($response['success']) {
            $response['redirect'] = route('sales.debit-notes.show', $response['data']->id);

            $message = trans('messages.success.updated', ['type' => trans_choice('general.debit_notes', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('sales.debit-notes.edit', $debit_note->id);

            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    public function destroy(Document $debit_note): JsonResponse
    {
        $response = $this->ajaxDispatch(new DeleteDocument($debit_note));

        $response['redirect'] = route('sales.debit-notes.index');

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => trans_choice('general.debit_notes', 1)]);

            flash($message)->success();
        } else {
            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    public function duplicate(Document $debit_note): RedirectResponse
    {
        $clone = $this->dispatch(new DuplicateDocument($debit_note));

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.debit_notes', 1)]);

        flash($message)->success();

        return redirect()->route('sales.debit-notes.edit', $clone->id);
    }

    public function markSent(Document $debit_note): RedirectResponse
    {
        event(new DocumentMarkedSent($debit_note));

        $message = trans('documents.messages.marked_sent', ['type' => trans_choice('general.debit_notes', 1)]);

        flash($message)->success();

        return redirect()->back();
    }

    public function markCancelled(Document $debit_note): RedirectResponse
    {
        flash('No se puede anular una nota de débito emitida.')->error()->important();

        return redirect()->back();
    }

    public function printDebitNote(Document $debit_note): string
    {
        event(new DocumentPrinting($debit_note));

        $view = view($debit_note->template_path, compact('debit_note'));

        return mb_convert_encoding($view, 'HTML-ENTITIES', 'UTF-8');
    }

    public function pdfDebitNote(Document $debit_note): Response
    {
        event(new DocumentPrinting($debit_note));

        $currency_style = true;

        $view = view($debit_note->template_path, compact('debit_note', 'currency_style'))->render();
        $html = mb_convert_encoding($view, 'HTML-ENTITIES', 'UTF-8');
        $html = prepare_pdf_html($html);

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);

        $file_name = $this->getDocumentFileName($debit_note);

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

    public function invoice(Document $invoice): JsonResponse
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
