<x-loading.content />

<div class="relative mt-4">
    <x-form 
        id="{{ $formId }}"
        :route="$formRoute"
        method="{{ $formMethod }}"
        :model="$document"
    >
        @if (! $hideCompany)
            <x-documents.form.company :type="$type" />
        @endif

        <x-documents.form.main type="{{ $type }}" />

        @if ($showRecurring)
            <x-documents.form.recurring type="{{ $type }}" />
        @endif

        @if (! $hideAdvanced)
            <x-documents.form.advanced type="{{ $type }}" />
        @endif

        <x-form.input.hidden name="type" :value="old('type', $type)" v-model="form.type" />
        <x-form.input.hidden name="status" :value="old('status', $status)" v-model="form.status" />
        <x-form.input.hidden name="amount" :value="old('amount', '0')" v-model="form.amount" />
        @if ($type === 'credit-note')
            <x-form.input.hidden
                name="invoice_id"
                :value="old('invoice_id', $invoice_id ?? data_get($document ?? null, 'invoice_id') ?? request('invoice_id'))"
                v-model="form.invoice_id"
            />
            <x-form.input.hidden
                name="parent_id"
                :value="old('parent_id', $invoice_id ?? data_get($document ?? null, 'invoice_id') ?? request('invoice_id'))"
                v-model="form.parent_id"
            />
        @endif

        @if (! $hideButtons)
            <x-documents.form.buttons :type="$type" />
        @endif
    </x-form>
</div>
