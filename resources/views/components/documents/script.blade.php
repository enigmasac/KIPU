@push('scripts_start')
    @php
        $document_items = 'false';
        $document_app_env = env('APP_ENV');

        if ($items) {
            $document_items = json_encode($items);
        } else if (old('items')) {
            $document_items = json_encode(old('items'));
        }

        $document_installments = '[]';
        if (! empty($document)) {
            if (is_object($document) && method_exists($document, 'installments') && $document->installments()->count()) {
                $document_installments = $document->installments->map(function($i) {
                    return ['amount' => $i->amount, 'due_at' => $i->due_at->format('Y-m-d')];
                })->toJson();
            } else if (old('installments')) {
                $document_installments = json_encode(old('installments'));
            }
        }

        if (is_object($document) && isset($document->sale_type)) {
            $document_sale_type = $document->sale_type;
        } else {
            $document_sale_type = old('sale_type', 'cash');
        }

        $aka_currency = config('money.currencies.PEN');

        if (! empty($document)) {
            if (is_object($document) && isset($document->currency)) {
                $aka_currency = $document->currency;
            } else if (is_array($document) && isset($document['currency'])) {
                $aka_currency = $document['currency'];
            }
        }

        $document_invoice_total = null;

        if ($type === 'credit-note' && ! empty($document) && is_object($document)) {
            if (isset($document->type) && $document->type === 'invoice' && isset($document->amount)) {
                $document_invoice_total = (float) $document->amount;
            }
        }

        $initial_contact = null;
        if (! empty($contact) && is_object($contact)) {
            $initial_contact = [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'tax_number' => $contact->tax_number,
                'phone' => $contact->phone,
                'address' => $contact->address,
                'country' => $contact->country,
                'state' => $contact->state,
                'zip_code' => $contact->zip_code,
                'city' => $contact->city,
            ];
        }
    @endphp

    <script type="text/javascript">
        // --- 1. VARIABLES CORE AKAUNTING ---
        var document_app_env = '{{ $document_app_env }}';
        var document_items = {!! $document_items !!};
        var document_taxes = {!! $taxes ?? '[]' !!};
        var aka_currency = {!! json_encode($aka_currency) !!};
        var document_default_currency = 'PEN';
        var document_invoice_total = {!! json_encode($document_invoice_total) !!};
        var initial_document_contact = {!! json_encode($initial_contact) !!};

        window.orderedTaxIds = function(row) {
            return (row && row.tax_ids) ? row.tax_ids : [];
        };

        // --- 2. LÓGICA SUNAT (TU SOLUCIÓN CON FIX DE SINCRONIZACIÓN) ---
        (function() {
            var checkVueInterval = setInterval(function() {
                var appEl = document.querySelector('#main-body');
                if (appEl && appEl.__vue__) {
                    var vm = appEl.__vue__;
                    clearInterval(checkVueInterval);
                    if (vm.type !== '{{ $type }}') {
                        vm.$set(vm, 'type', '{{ $type }}');
                    }

                    vm.getSunatTaxLabel = function(tax_id) {
                        var tax = document_taxes.find(function(t) { return t.id == tax_id; });
                        if (tax) return tax.name + " (" + parseInt(tax.rate) + "%)";
                        return "Impuesto";
                    };

                    vm.formatMoney = function(amount) {
                        return parseFloat(amount || 0).toFixed(2);
                    };

                    vm.normalizeCreditNoteReasonCode = function(value) {
                        var raw = value;

                        if (raw && typeof raw === 'object') {
                            raw = raw.id || raw.value || raw.code || raw.key || raw.name || '';
                        }

                        if (raw === null || raw === undefined) {
                            return '';
                        }

                        var str = String(raw).trim();
                        if (!str) {
                            return '';
                        }

                        var token = str.split(/[\s-]/)[0];
                        var match = token.match(/\d+/);
                        var code = match && match[0] ? match[0] : token;

                        if (code.length === 1) {
                            code = '0' + code;
                        }

                        return code;
                    };

                    if (typeof vm.creditNoteInvoiceTotal === 'undefined') {
                        vm.$set(vm, 'creditNoteInvoiceTotal', null);
                    }

                    if (typeof vm.creditNoteOverLimit === 'undefined') {
                        vm.$set(vm, 'creditNoteOverLimit', false);
                    }

                    if (vm.type === 'credit-note') {
                        if (vm.form && typeof vm.form.credit_note_reason_code === 'undefined') {
                            vm.$set(vm.form, 'credit_note_reason_code', '');
                        }

                        if (vm.form && typeof vm.form.invoice_id === 'undefined') {
                            vm.$set(vm.form, 'invoice_id', '');
                        }

                        if (vm.form && typeof vm.form.parent_id === 'undefined') {
                            vm.$set(vm.form, 'parent_id', '');
                        }
                    } else if (vm.form) {
                        if (typeof vm.form.parent_id !== 'undefined') {
                            delete vm.form.parent_id;
                        }

                    if (typeof vm.form.invoice_id !== 'undefined') {
                        delete vm.form.invoice_id;
                    }
                }

                if (initial_document_contact && vm.form && !vm.form.contact_id) {
                    vm.$set(vm.form, 'contact_id', initial_document_contact.id);
                    vm.$set(vm.form, 'contact_name', initial_document_contact.name);
                    vm.$set(vm.form, 'contact_email', initial_document_contact.email);
                    vm.$set(vm.form, 'contact_tax_number', initial_document_contact.tax_number);
                    vm.$set(vm.form, 'contact_phone', initial_document_contact.phone);
                    vm.$set(vm.form, 'contact_address', initial_document_contact.address);
                    vm.$set(vm.form, 'contact_country', initial_document_contact.country);
                    vm.$set(vm.form, 'contact_state', initial_document_contact.state);
                    vm.$set(vm.form, 'contact_zip_code', initial_document_contact.zip_code);
                    vm.$set(vm.form, 'contact_city', initial_document_contact.city);
                }

                    vm.creditNotePolicy = function() {
                        var allow_all = {
                            quantity: true,
                            price: true,
                            discount: true,
                            description: true,
                            delete: true
                        };

                        if (vm.type !== 'credit-note') {
                            return allow_all;
                        }

                        var reason = vm.normalizeCreditNoteReasonCode(vm.form ? vm.form.credit_note_reason_code : '');

                        switch (reason) {
                            case '01': // Anulación de la operación
                            case '02': // Anulación por error en el RUC
                            case '06': // Devolución total
                                return {
                                    quantity: false,
                                    price: false,
                                    discount: false,
                                    description: false,
                                    delete: false
                                };
                            case '03': // Corrección por error en la descripción
                                return {
                                    quantity: false,
                                    price: false,
                                    discount: false,
                                    description: true,
                                    delete: false
                                };
                            case '04': // Descuento global
                                return {
                                    quantity: false,
                                    price: false,
                                    discount: false,
                                    description: false,
                                    delete: false
                                };
                            case '05': // Descuento por ítem
                                return {
                                    quantity: false,
                                    price: false,
                                    discount: true,
                                    description: false,
                                    delete: false
                                };
                            case '07': // Devolución por ítem
                                return {
                                    quantity: true,
                                    price: false,
                                    discount: false,
                                    description: false,
                                    delete: true
                                };
                            case '08': // Bonificación
                                return {
                                    quantity: true,
                                    price: true,
                                    discount: false,
                                    description: false,
                                    delete: true
                                };
                            case '09': // Disminución en el valor
                                return {
                                    quantity: false,
                                    price: true,
                                    discount: false,
                                    description: false,
                                    delete: false
                                };
                            case '10': // Otros conceptos
                                return allow_all;
                            default:
                                return allow_all;
                        }
                    };

                    vm.canEditCreditNoteField = function(field) {
                        var policy = vm.creditNotePolicy();
                        return policy[field] !== false;
                    };

                    if (typeof vm.$watch === 'function') {
                        vm.$watch('totals.total', function(newVal) {
                            var total = parseFloat(newVal) || 0;

                            if (vm.creditNoteInvoiceTotal !== null && vm.type === 'credit-note') {
                                var limit = parseFloat(vm.creditNoteInvoiceTotal) || 0;
                                vm.creditNoteOverLimit = total > (limit + 0.01);
                            }
                        });

                        vm.$watch('form.credit_note_reason_code', function(newVal) {
                            var normalized = vm.normalizeCreditNoteReasonCode(newVal);
                            if (vm.form && normalized !== newVal) {
                                vm.form.credit_note_reason_code = normalized;
                            }

                            vm.$forceUpdate();
                        });
                    }

                    function buildInvoiceRows(i) {
                        var item_taxes = [];

                        if (i.taxes && i.taxes.length) {
                            i.taxes.forEach(function(item_tax) {
                                var tax_id = item_tax.id || item_tax.tax_id;

                                if (!tax_id) {
                                    return;
                                }

                                item_taxes.push({
                                    id: tax_id,
                                    name: item_tax.name,
                                    price: (item_tax.amount !== undefined && item_tax.amount !== null) ? item_tax.amount : item_tax.price,
                                    amount: (item_tax.amount !== undefined && item_tax.amount !== null) ? item_tax.amount : item_tax.price
                                });
                            });
                        }

                        var price = parseFloat(i.price) || 0;
                        var quantity = parseFloat(i.quantity) || 0;
                        var total = parseFloat(i.total) || 0;
                        var discount = (i.discount_rate !== undefined && i.discount_rate !== null) ? parseFloat(i.discount_rate) : 0;
                        var discount_type = i.discount_type || 'fixed';

                        var form_row = {
                            item_id: i.item_id,
                            name: i.name,
                            sku: i.sku || '',
                            sunat_unit_code: i.sunat_unit_code || 'NIU',
                            description: i.description || '',
                            quantity: quantity,
                            price: price,
                            tax_ids: i.tax_ids || [],
                            discount: discount,
                            discount_type: discount_type,
                            total: total
                        };

                        var view_row = {
                            item_id: i.item_id,
                            name: i.name,
                            sku: i.sku || '',
                            sunat_unit_code: i.sunat_unit_code || 'NIU',
                            description: i.description || '',
                            quantity: quantity,
                            price: price,
                            add_tax: false,
                            tax_ids: item_taxes,
                            taxes: item_taxes,
                            add_discount: discount > 0,
                            discount: discount,
                            discount_type: discount_type,
                            total: total
                        };

                        return {
                            form_row: form_row,
                            view_row: view_row
                        };
                    }

                    var originalOnAddItem = vm.onAddItem;
                    vm.onAddItem = function(payload) {
                        var self = this;
                        var item = payload && payload.item ? payload.item : payload;

                        originalOnAddItem.apply(this, arguments);

                        if (!item || !self.form || !self.form.items) {
                            return;
                        }

                        var sku = item.sku || '';
                        var unit = item.sunat_unit_code || 'NIU';

                        setTimeout(function() {
                            var index = self.form.items.length - 1;
                            if (index < 0) {
                                return;
                            }

                            self.$set(self.form.items[index], 'sku', sku);
                            self.$set(self.form.items[index], 'sunat_unit_code', unit);

                            if (self.items && self.items[index]) {
                                self.$set(self.items[index], 'sku', sku);
                                self.$set(self.items[index], 'sunat_unit_code', unit);
                                if (!self.items[index].taxes && self.items[index].tax_ids) {
                                    self.$set(self.items[index], 'taxes', self.items[index].tax_ids);
                                }
                            }

                            self.onCalculateTotal();
                        }, 0);
                    };

                    if (typeof vm.onCalculateTotal === 'function') {
                        var originalOnCalculateTotal = vm.onCalculateTotal;
                        vm.onCalculateTotal = function() {
                            originalOnCalculateTotal.apply(this, arguments);

                            if (this.creditNoteInvoiceTotal !== null && this.type === 'credit-note') {
                                var limit = parseFloat(this.creditNoteInvoiceTotal) || 0;
                                this.creditNoteOverLimit = (parseFloat(this.totals.total) || 0) > (limit + 0.01);
                            }
                        };
                    }

                    if (typeof document_items !== 'undefined' && document_items && vm.items && vm.items.length) {
                        document_items.forEach(function(source, index) {
                            if (!source) {
                                return;
                            }

                            if (vm.items[index]) {
                                if (!vm.items[index].sku && source.sku) {
                                    vm.$set(vm.items[index], 'sku', source.sku);
                                }

                                if (!vm.items[index].sunat_unit_code && source.sunat_unit_code) {
                                    vm.$set(vm.items[index], 'sunat_unit_code', source.sunat_unit_code);
                                }
                            }

                            if (vm.form && vm.form.items && vm.form.items[index]) {
                                if (!vm.form.items[index].sku && source.sku) {
                                    vm.$set(vm.form.items[index], 'sku', source.sku);
                                }

                                if (!vm.form.items[index].sunat_unit_code && source.sunat_unit_code) {
                                    vm.$set(vm.form.items[index], 'sunat_unit_code', source.sunat_unit_code);
                                }
                            }
                        });

                        if (vm.form && !vm.form.invoice_id && document_items[0] && document_items[0].document_id && document_items[0].type === 'invoice') {
                            vm.$set(vm.form, 'invoice_id', document_items[0].document_id);
                            vm.$set(vm.form, 'parent_id', document_items[0].document_id);
                        }

                        setTimeout(function() {
                            vm.onCalculateTotal();
                        }, 0);
                    }

                    if (typeof document_items !== 'undefined' && document_items && (!vm.items || !vm.items.length)) {
                        vm.items = [];
                        vm.$set(vm.form, 'items', []);

                        document_items.forEach(function(i) {
                            var rows = buildInvoiceRows(i);
                            vm.form.items.push(JSON.parse(JSON.stringify(rows.form_row)));
                            vm.items.push(rows.view_row);
                        });

                        if (!vm.form.invoice_id && document_items[0] && document_items[0].document_id && document_items[0].type === 'invoice') {
                            vm.$set(vm.form, 'invoice_id', document_items[0].document_id);
                            vm.$set(vm.form, 'parent_id', document_items[0].document_id);
                        }

                        if (vm.creditNoteInvoiceTotal === null) {
                            if (document_invoice_total !== null) {
                                vm.creditNoteInvoiceTotal = parseFloat(document_invoice_total) || 0;
                            } else if (document_items.length) {
                                vm.creditNoteInvoiceTotal = document_items.reduce(function(sum, row) {
                                    var item_total = parseFloat(row.total) || 0;
                                    var item_taxes = 0;

                                    if (row.taxes && row.taxes.length) {
                                        item_taxes = row.taxes.reduce(function(tax_sum, tax) {
                                            var amount = (tax.amount !== undefined && tax.amount !== null) ? tax.amount : tax.price;
                                            return tax_sum + (parseFloat(amount) || 0);
                                        }, 0);
                                    }

                                    return sum + item_total + item_taxes;
                                }, 0);
                            }
                        }

                        if (typeof vm.$nextTick === 'function') {
                            vm.$nextTick(function() {
                                vm.onCalculateTotal();
                            });
                        } else {
                            setTimeout(function() {
                                vm.onCalculateTotal();
                            }, 0);
                        }
                    }

                    var originalOnSubmit = vm.onSubmit;
                    vm.onSubmit = function() {
                        if (this.type === 'credit-note' && this.form) {
                            var normalizedInvoiceId = this.form.invoice_id;

                            if (normalizedInvoiceId && typeof normalizedInvoiceId === 'object') {
                                normalizedInvoiceId = normalizedInvoiceId.id || normalizedInvoiceId.value || normalizedInvoiceId.invoice_id;
                            }

                            if (normalizedInvoiceId) {
                                this.form.invoice_id = normalizedInvoiceId;
                                this.form.parent_id = normalizedInvoiceId;
                            }

                            var normalizedReason = '';
                            if (typeof this.normalizeCreditNoteReasonCode === 'function') {
                                normalizedReason = this.normalizeCreditNoteReasonCode(this.form.credit_note_reason_code);
                                if (normalizedReason) {
                                    this.form.credit_note_reason_code = normalizedReason;
                                }
                            }

                            var missingFields = [];

                            if (!normalizedInvoiceId) {
                                this.form.errors.set('invoice_id', ['Seleccione la factura de referencia.']);
                                missingFields.push('factura de referencia');
                            }

                            if (!normalizedReason) {
                                this.form.errors.set('credit_note_reason_code', ['Seleccione el motivo SUNAT.']);
                                missingFields.push('motivo SUNAT');
                            }

                            if (missingFields.length) {
                                var message = missingFields.length > 1
                                    ? 'Seleccione la factura de referencia y el motivo SUNAT.'
                                    : 'Seleccione el ' + missingFields[0] + '.';

                                if (typeof this.$notify === 'function') {
                                    this.$notify({
                                        verticalAlign: 'bottom',
                                        horizontalAlign: 'left',
                                        message: message,
                                        timeout: 5000,
                                        icon: 'error_outline',
                                        type: 'error'
                                    });
                                } else {
                                    alert(message);
                                }

                                return;
                            }
                        }

                        if (this.items && this.form && this.form.items) {
                            this.items.forEach(function(item, index) {
                                if (!this.form.items[index]) {
                                    return;
                                }

                                this.form.items[index].sku = item && item.sku ? item.sku : '';
                                this.form.items[index].sunat_unit_code = item && item.sunat_unit_code ? item.sunat_unit_code : 'NIU';
                            }, this);
                        }

                        if (this.type === 'credit-note' && this.creditNoteOverLimit) {
                            alert('La nota de crédito no puede ser mayor que la factura de referencia.');
                            return;
                        }

                        originalOnSubmit.apply(this, arguments);
                    };

                    if (vm.type === 'credit-note' && vm.form && typeof vm.form.onFail === 'function') {
                        var originalFormOnFail = vm.form.onFail.bind(vm.form);

                        vm.form.onFail = function(error) {
                            originalFormOnFail(error);

                            var errors = error && error.response && error.response.data && error.response.data.errors
                                ? error.response.data.errors
                                : null;

                            if (!errors) {
                                return;
                            }

                            var missing = [];

                            if (errors.invoice_id) {
                                missing.push('factura de referencia');
                            }

                            if (errors.credit_note_reason_code) {
                                missing.push('motivo SUNAT');
                            }

                            if (!missing.length) {
                                return;
                            }

                            var message = missing.length > 1
                                ? 'La factura de referencia y el motivo SUNAT son obligatorios.'
                                : (missing[0] === 'factura de referencia'
                                    ? 'La factura de referencia es obligatoria.'
                                    : 'El motivo SUNAT es obligatorio.');

                            if (typeof vm.$notify === 'function') {
                                vm.$notify({
                                    verticalAlign: 'bottom',
                                    horizontalAlign: 'left',
                                    message: message,
                                    timeout: 5000,
                                    icon: 'error_outline',
                                    type: 'error'
                                });
                            } else {
                                alert(message);
                            }
                        };
                    }

                    window.onSelectReferenceInvoice = function(invoice_id) {
                        var selectedInvoiceId = invoice_id;

                        if (selectedInvoiceId && typeof selectedInvoiceId === 'object') {
                            selectedInvoiceId = selectedInvoiceId.id || selectedInvoiceId.value || selectedInvoiceId.invoice_id;
                        }

                        if (!selectedInvoiceId) {
                            return;
                        }

                        vm.$set(vm.form, 'invoice_id', selectedInvoiceId);
                        vm.$set(vm.form, 'parent_id', selectedInvoiceId);

                        var apiUrl = url + '/sales/credit-notes/invoices/' + selectedInvoiceId;
                        window.axios.get(apiUrl).then(function (response) {
                            if (response.data.success) {
                                var data = response.data.data;

                                vm.form.contact_id = data.contact_id;
                                if (data.contact_name) {
                                    vm.form.contact_name = data.contact_name;
                                    vm.form.contact_email = data.contact_email || '';
                                    vm.form.contact_tax_number = data.contact_tax_number || '';
                                    vm.form.contact_phone = data.contact_phone || '';
                                    vm.form.contact_address = data.contact_address || '';
                                    vm.form.contact_country = data.contact_country || '';
                                    vm.form.contact_state = data.contact_state || '';
                                    vm.form.contact_zip_code = data.contact_zip_code || '';
                                    vm.form.contact_city = data.contact_city || '';
                                }

                                if (data.currency_code) {
                                    vm.form.currency_code = data.currency_code;
                                    vm.form.currency_rate = data.currency_rate;
                                    if (typeof vm.onChangeCurrency === 'function') {
                                        vm.onChangeCurrency(data.currency_code);
                                    }
                                }

                                if (data.invoice_total !== undefined && data.invoice_total !== null) {
                                    vm.creditNoteInvoiceTotal = parseFloat(data.invoice_total) || 0;
                                }

                                vm.items = [];
                                vm.$set(vm.form, 'items', []);
                                (data.items || []).forEach(function(i) {
                                    var rows = buildInvoiceRows(i);
                                    vm.form.items.push(JSON.parse(JSON.stringify(rows.form_row)));
                                    vm.items.push(rows.view_row);
                                });

                                if (typeof vm.$nextTick === 'function') {
                                    vm.$nextTick(function() {
                                        vm.onCalculateTotal();
                                    });
                                } else {
                                    setTimeout(function() { vm.onCalculateTotal(); }, 0);
                                }
                            }
                        });
                    };

                    if (typeof vm.$forceUpdate === 'function') {
                        vm.$forceUpdate();
                    }
                }
            }, 500);
        })();
    </script>
@endpush

<x-script :alias="$alias" :folder="$folder" :file="$file" />
