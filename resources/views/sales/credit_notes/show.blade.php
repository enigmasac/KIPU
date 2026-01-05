<x-layouts.admin>
    <x-slot name="title">
        {{ $credit_note->document_number }}
    </x-slot>

    <x-slot name="status">
        <x-show.status status="{{ $credit_note->status }}"
                       background-color="bg-{{ $credit_note->status_label }}"
                       text-color="text-text-{{ $credit_note->status_label }}"
        />
    </x-slot>

{{--TODO: fix building the permission names and the text-create in the core to not provide it explicitly here--}}
    <x-slot name="buttons">
        <x-documents.show.buttons
            type="credit-note"
            :document="$credit_note"
            permission-create="create-sales-credit-notes"
            permission-update="update-sales-credit-notes"
            text-create="{{ trans('credit-notes.create_credit_note') }}"
            hide-create
            :hide-edit="$credit_note->status !== 'draft'"
        />
    </x-slot>

    <x-slot name="moreButtons">
        <x-documents.show.more-buttons type="credit-note" :document="$credit_note" hide-cancel />
    </x-slot>

    <x-slot name="content">
        <x-documents.show.content
            type="credit-note"
            :document="$credit_note"
            hide-receive
            hide-make-payment
            hide-schedule
            hide-children
            hide-due-at
            hide-header-due-at
            hide-button-received
            hide-email
            hide-get-paid
            hide-restore
            permission-update="update-sales-credit-notes"
        />
    </x-slot>

    @push('stylesheet')
        <link rel="stylesheet" href="{{ asset('public/css/print.css?v=' . version('short')) }}" type="text/css">
    @endpush

    <x-documents.script type="credit-note" />

{{--    TODO: check if this is needed--}}
    @push('body_end')
        <div id="credit-debit-notes-vue-entrypoint">
            <component v-bind:is="component"></component>
        </div>
    @endpush

{{--    TODO: check if this is needed--}}
{{--    @push('scripts_start')--}}
{{--        <script type="text/javascript">--}}
{{--            var envelopeBadge = document.querySelector('span.timeline-step.badge-danger')--}}

{{--            if (envelopeBadge) {--}}
{{--                envelopeBadge.className = 'timeline-step badge-success'--}}
{{--            }--}}
{{--        </script>--}}
{{--    @endpush--}}
</x-layouts.admin>
