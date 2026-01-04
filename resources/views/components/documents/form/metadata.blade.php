<div class="grid sm:grid-cols-7 sm:col-span-6 gap-x-8 gap-y-6 my-3.5">
    <div class="sm:col-span-2">
        <x-form.label for="contact" required>
            {{ trans_choice($textContact, 1) }}
        </x-form.label>

        <x-documents.form.contact
            type="{{ $typeContact }}"
            :contact="$contact"
            :contacts="$contacts"
            :search-route="$searchContactRoute"
            :create-route="$createContactRoute"
            error="form.errors.get('contact_name')"
            :text-add-contact="$textAddContact"
            :text-create-new-contact="$textCreateNewContact"
            :text-edit-contact="$textEditContact"
            :text-contact-info="$textContactInfo"
            :text-choose-different-contact="$textChooseDifferentContact"
        />
    </div>

    <div class="sm:col-span-1"></div>

    <div class="sm:col-span-4 grid sm:grid-cols-4 gap-x-8 gap-y-6">
        @stack('issue_start')

        @if (! $hideIssuedAt)
            <div class="sm:col-span-2">
                <x-form.label for="issued_at" required>
                    {{ trans($textIssuedAt) }}
                </x-form.label>
                <div class="flex items-center text-sm text-gray-700 bg-gray-100 p-2 rounded border border-gray-300">
                    <span class="material-icons-outlined text-base mr-2">calendar_today</span>
                    {{ company_date(now()) }}
                </div>
                <input type="hidden" name="issued_at" value="{{ now()->format('Y-m-d H:i:s') }}">
            </div>
        @endif

        @stack('document_number_start')

        @if (! $hideDocumentNumber)
            <div class="sm:col-span-2">
                <x-form.label for="document_number" required>
                    {{ trans($textDocumentNumber) }}
                </x-form.label>
                <div id="document_number_display" class="flex items-center text-sm text-gray-700 bg-gray-100 p-2 rounded border border-gray-300">
                    {{ $documentNumber }}
                </div>
                <input type="hidden" id="document_number" name="document_number" value="{{ $documentNumber }}" v-model="form.document_number">
            </div>
        @endif

        @stack('due_start')

        @if (! $hideDueAt)
            <x-form.group.date
                name="due_at"
                label="{{ trans($textDueAt) }}"
                icon="calendar_today"
                value="{{ $dueAt }}"
                show-date-format="{{ company_date_format() }}"
                date-format="Y-m-d"
                autocomplete="off"
                period="{{ $periodDueAt }}"
                min-date="form.issued_at"
                min-date-dynamic="min_due_date"
                data-value-min
                form-group-class="sm:col-span-2"
            />
        @else
            <x-form.input.hidden
                name="due_at"
                :value="old('issued_at', $issuedAt)"
                v-model="form.issued_at"
                form-group-class="sm:col-span-2"
            />
        @endif

        @stack('order_number_start')

        @if (! $hideOrderNumber)
            <x-form.group.text
                name="order_number"
                label="{{ trans($textOrderNumber) }}"
                value="{{ $orderNumber }}"
                form-group-class="sm:col-span-2"
                not-required
            />
        @endif

        @if ($type == 'invoice')
            <x-form.group.select name="sunat_document_type" label="Tipo de Comprobante" :options="['01' => 'Factura', '03' => 'Boleta']" :selected="data_get($document, 'sunat_document_type', '01')" v-model="form.sunat_document_type" form-group-class="sm:col-span-2" />

            <x-form.group.select name="sunat_operation_type" label="Tipo de Operación" :options="['01' => 'Venta Normal', '02' => 'Venta Gratuita']" :selected="data_get($document, 'sunat_operation_type', '01')" form-group-class="sm:col-span-2" />

            <x-form.group.select name="sale_type" label="Tipo de Venta" :options="['cash' => 'Venta al Contado', 'credit' => 'Venta al Crédito']" v-model="form.sale_type" :selected="data_get($document, 'sale_type', 'cash')" form-group-class="sm:col-span-2" />
        @endif
    </div>
</div>
