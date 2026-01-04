<x-form id="form-credit-transaction" :action="$route">
    <x-form.group.text name="currency" label="{{ trans_choice('general.currencies', 1) }}" value="{{ $invoice->currency->name }}" not-required disabled />

    <x-form.group.money name="amount" label="{{ trans('general.amount') }}" value="{{ $invoice->grand_total }}" autofocus="autofocus" :currency="$currency" dynamicCurrency="currency" />

    <x-form.group.textarea name="description" label="{{ trans('general.description') }}" rows="3" not-required />

    <x-form.input.hidden name="document_id" :value="$invoice->id" />
    <x-form.input.hidden name="category_id" :value="$invoice->category_id" />
    <x-form.input.hidden name="currency_code" :value="$invoice->currency_code" />
    <x-form.input.hidden name="currency_rate" :value="$invoice->currency_rate" />
    <x-form.input.hidden name="type" value="expense" />
</x-form>
