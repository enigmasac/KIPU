<p class="mb-0">
    <span class="font-semibold spacing w-numbers">
        {{ trans('credit_notes.related_invoice_number') }}:
    </span>

    <span class="float-right spacing">
        @if(!$print)
        <a href="{{ $invoice_route }}" class="text-purple">
        @endif
        {{ $credit_note->invoice_number }}
        @if(!$print)
        </a>
        @endif
    </span>
</p>

@if(!empty($reason_description))
<p class="mb-0">
    <span class="font-semibold spacing w-numbers">
        Motivo SUNAT:
    </span>

    <span class="float-right spacing">
        {{ $reason_description }}
    </span>
</p>
@endif