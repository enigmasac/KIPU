@if (in_array($document->type, ['credit-note', 'debit-note']) && $document->invoice_number)
    <x-show.accordion type="affected_document" :open="false">
        <x-slot name="head">
            <div class="relative w-full text-left cursor-pointer group">
                <div class="ltr:text-left rtl:text-right">
                    <h2 class="lg:text-lg font-medium text-black">
                        <span class="to-black group-hover:bg-full-2 bg-no-repeat bg-0-2 bg-0-full bg-gradient-to-b from-transparent transition-backgroundSize cursor-pointer">
                            Comprobante Afectado
                        </span>
                    </h2>
                    <span class="text-sm font-light text-black block gap-x-1 mt-1">
                        {{ $document->invoice_number }}
                    </span>
                </div>
            </div>
        </x-slot>

        <x-slot name="body">
            <div class="text-sm">
                <p class="mb-2">Este documento afecta legalmente al comprobante:</p>
                <x-link href="{{ route('invoices.show', $document->invoice_id) }}" class="font-bold hover:underline" override="class">
                    {{ $document->invoice_number }}
                </x-link>
            </div>
        </x-slot>
    </x-show.accordion>
@endif
