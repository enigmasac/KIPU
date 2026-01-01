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

        (function() {
            function applySunatLogic(vm) {
                if (!vm || vm._sunat_applied) return;
                
                console.log("Aplicando Lógica SUNAT de Prioridades...");

                const originalCalculate = vm.calculateItemTax;
                
                vm.calculateItemTax = function(item, totals_taxes, total_discount_amount) {
                    let taxes_available = this.dynamic_taxes;
                    if (!item.tax_ids || !item.tax_ids.length) return;

                    let selected_tax_objects = [];
                    item.tax_ids.forEach(itx => {
                        let id = (typeof itx === 'object') ? itx.id : itx;
                        let obj = taxes_available.find(t => t.id == id);
                        if (obj) selected_tax_objects.push(obj);
                    });

                    // Agrupar y ordenar por prioridad
                    let groups = {};
                    selected_tax_objects.forEach(t => {
                        let p = parseInt(t.priority) || 0;
                        if (!groups[p]) groups[p] = [];
                        groups[p].push(t);
                    });
                    let priorities = Object.keys(groups).sort((a, b) => a - b);

                    let quantity = parseFloat(String(item.quantity).replace(/[^\\d.-]/g, '')) || 0;
                    let price = parseFloat(String(item.price).replace(/[^\\d.-]/g, '')) || 0;
                    let current_base = price * quantity;

                    // Aplicar descuento total (línea + global) provisto por el padre
                    let total_disc = parseFloat(String(total_discount_amount).replace(/[^\\d.-]/g, '')) || 0;
                    current_base -= total_disc;

                    let initial_net = current_base;
                    let accumulated_tax = 0;

                    priorities.forEach(p => {
                        let group = groups[p];
                        let level_sum = 0;
                        let base_at_level = current_base;

                        group.forEach(tax => {
                            let amt = 0;
                            let rate = parseFloat(tax.rate) || 0;
                            if (tax.type === 'fixed') amt = rate * quantity;
                            else if (tax.type === 'inclusive') amt = base_at_level - (base_at_level / (1 + rate / 100));
                            else if (tax.type === 'withholding') amt = -(base_at_level * (rate / 100));
                            else amt = base_at_level * (rate / 100);

                            let it_entry = item.tax_ids.find(x => (x.id == tax.id || x == tax.id));
                            if (it_entry && typeof it_entry === 'object') {
                                it_entry.name = tax.title;
                                it_entry.price = this.numberFormat(amt, this.currency.precision);
                            }
                            level_sum += amt;
                            totals_taxes = this.calculateTotalsTax(totals_taxes, tax.id, tax.title, amt);
                        });
                        current_base += level_sum;
                        accumulated_tax += level_sum;
                    });

                    item.total = initial_net;
                    item.grand_total = initial_net + accumulated_tax;
                };

                vm._sunat_applied = true;
                vm.onCalculateTotal();
            }

            var checkInterval = setInterval(() => {
                var appContainer = document.querySelector('[v-cloak]') || document.getElementById('document') || document.getElementById('main-body');
                if (appContainer && appContainer.__vue__) {
                    applySunatLogic(appContainer.__vue__);
                    // No limpiamos el intervalo para asegurar que si Vue reinicia el componente, lo volvemos a capturar
                }
            }, 500);
        })();

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
