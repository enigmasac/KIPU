<x-form.group.radio
    name="credit_customer_account"
    label="{{ trans('credit_notes.credit_customer_account') }}"
    :options="[
        '1' => trans('general.yes'),
        '0' => trans('general.no'),
    ]"
    :checked="$credit_customer_account ? '0' : '0'"
    not-required
    form-group-class="sm:col-span-2 d-none"
/>
