<?php

namespace App\Http\Controllers\Purchases;

use App\Abstracts\Http\Controller;
use App\Events\Document\DocumentCancelled;
use App\Events\Document\DocumentPrinting;
use App\Events\Document\DocumentMarkedSent;
use App\Jobs\Document\CreateDocument;
use App\Jobs\Document\DeleteDocument;
use App\Jobs\Document\DuplicateDocument;
use App\Jobs\Document\UpdateDocument;
use App\Traits\Documents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\Models\Document\DebitNote as Document;

class DebitNotes extends Controller
{
    use Documents;

    public $type = \App\Models\Document\Document::DEBIT_NOTE_TYPE;

    public function index()
    {
        $debit_notes = Document::with('contact', 'transactions')->collect(['issued_at' => 'desc']);

        return $this->response('purchases.debit_notes.index', compact('debit_notes'));
    }

    public function create()
    {
        $bill_items = collect([]);
        if ($bill_id = request()->query('bill', null)) {
            $bill = \App\Models\Document\Document::bill()->findOrFail($bill_id);
            $bill_items = $bill->items;
        }

        return view('purchases.debit_notes.create', compact('bill_items'));
    }

    public function store(\Illuminate\Http\Request $request): JsonResponse
    {
        $response = $this->ajaxDispatch(new CreateDocument($request));

        if ($response['success']) {
            $response['redirect'] = route('purchases.debit-notes.show', $response['data']->id);

            $message = trans('messages.success.added', ['type' => trans_choice('general.debit_notes', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('purchases.debit-notes.create');

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function show(Document $debit_note)
    {
        // Get Debit Note Totals
        foreach ($debit_note->totals_sorted as $debit_note_total) {
            $debit_note->{$debit_note_total->code} = $debit_note_total->amount;
        }

        $currency_code = $debit_note->currency_code;

        $total = money($debit_note->total, $currency_code, true)->format();
        $debit_note->grand_total = money($total, $currency_code)->getAmount();

        foreach ($debit_note->transactions as $transaction) {
            $transaction->type = trans('debit_notes.refund');
        }

        return view('purchases.debit_notes.show', compact('debit_note'));
    }

    public function edit(Document $debit_note)
    {
        $debit_note->vendor_bills = $debit_note->contact->bills()
            ->whereIn('status', ['received', 'partial', 'paid'])
            ->pluck('document_number', 'id');

        return view('purchases.debit_notes.edit', compact('debit_note'));
    }

    public function update(Document $debit_note, \Illuminate\Http\Request $request): JsonResponse
    {
        $response = $this->ajaxDispatch(new UpdateDocument($debit_note, $request));

        if ($response['success']) {
            $response['redirect'] = route('purchases.debit-notes.show', $response['data']->id);

            $message = trans('messages.success.updated', ['type' => trans_choice('general.debit_notes', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('purchases.debit-notes.edit', $debit_note->id);

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function destroy(Document $debit_note): JsonResponse
    {
        $response = $this->ajaxDispatch(new DeleteDocument($debit_note));

        $response['redirect'] = route('purchases.debit-notes.index');

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

        return redirect()->route('purchases.debit-notes.edit', $clone->id);
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
        event(new DocumentCancelled($debit_note));

        $message = trans('documents.messages.marked_cancelled', ['type' => trans_choice('general.debit_notes', 1)]);

        flash($message)->success();

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

    public function contactBills(Contact $contact): JsonResponse
    {
        $bills = \App\Models\Document\Document::bill()
            ->where('contact_id', $contact->id)
            ->whereIn('status', ['received', 'partial', 'paid'])
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'name' => $bill->document_number . ' (' . company_date($bill->issued_at) . ')',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $bills,
        ]);
    }

    public function bill(\App\Models\Document\Document $bill): JsonResponse
    {
        $bill->load(['items.tax_methods', 'contact']);

        $items = $bill->items->map(function ($item) {
            return [
                'item_id' => $item->item_id,
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'price' => (float) $item->price,
                'tax_ids' => $item->tax_methods->pluck('tax_id')->toArray(),
                'taxes' => $item->tax_methods->map(function ($tm) {
                    return [
                        'id' => $tm->tax_id,
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
                'contact_id' => $bill->contact_id,
                'currency_code' => $bill->currency_code,
                'currency_rate' => $bill->currency_rate,
                'items' => $items,
            ],
        ]);
    }
}
