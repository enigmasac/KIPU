@push('scripts_start')
    @php
        $document_items = 'false';
        $document_app_env = env('APP_ENV');

        if ($items) {
            $document_items = json_encode($items);
        } else if (old('items')) {
            $document_items = json_encode(old('items'));
        }
    @endphp

    <script type="text/javascript">
        var document_items = {!! $document_items !!};
        var document_default_currency = '{{ $currency_code }}';
        var document_currencies = {!! $currencies !!};
        var document_taxes = {!! $taxes !!};
        var document_app_env = '{{ $document_app_env }}';

        if (typeof aka_currency !== 'undefined') {
            aka_currency = {!! json_encode(! empty($document) ? $document->currency : config('money.currencies.' . company()->currency)) !!};
        } else {
            var aka_currency = {!! json_encode(! empty($document) ? $document->currency : config('money.currencies.' . company()->currency)) !!};
        }

        document.addEventListener('DOMContentLoaded', function() {
            var contactSelect = document.getElementById('contact_id');
            if (contactSelect && typeof $ != 'undefined') {
                $(contactSelect).on('select2:select', function (e) {
                    var data = e.params.data;
                    if (data.default_sunat_document_type) {
                        $('#sunat_document_type').val(data.default_sunat_document_type).trigger('change');
                    }
                    if (data.default_sunat_operation_type) {
                        $('#sunat_operation_type').val(data.default_sunat_operation_type).trigger('change');
                    }
                });
            }
        });
    </script>
@endpush

<x-script :alias="$alias" :folder="$folder" :file="$file" />
