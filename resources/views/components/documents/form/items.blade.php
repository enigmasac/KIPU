<div class="relative sm:col-span-6 overflow-x-scroll large-overflow-unset" v-if="type != 'credit-note' || form.invoice_id">
    <div style="table-layout: fixed;">
        <div class="overflow-x-visible overflow-y-hidden">
            <table class="w-full" id="items" style="min-width: 1000px;">
                <colgroup>
                    <col style="width: 30px;"> <!-- Handle -->
                    <col style="width: 12%;"> <!-- Código -->
                    <col style="width: 28%;"> <!-- Item -->
                    <col style="width: 10%;"> <!-- Cant. -->
                    <col style="width: 10%;"> <!-- Unidad -->
                    <col style="width: 15%;"> <!-- Precio -->
                    <col style="width: 15%;"> <!-- Total -->
                    <col style="width: 30px;"> <!-- Delete -->
                </colgroup>

                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-2 py-2"></th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-500">CÓDIGO</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-500">ITEM / PRODUCTO</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-500">CANT.</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-500">U.M.</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-500">VALOR UNIT.</th>
                        <th class="px-2 py-2 text-right text-xs font-bold uppercase tracking-wider text-gray-500">PRECIO VENTA</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>

                <tbody id="invoice-item-rows">
                    <x-documents.form.line-item :type="$type" />

                    <!-- FILA AGREGAR PRODUCTO (ANCHO TOTAL) -->
                    <tr id="addItem" v-if="!form.invoice_id" class="border-t">
                        <td colspan="8" class="p-0">
                            <div class="w-full">
                                <x-documents.form.item-button
                                    type="{{ $type }}"
                                    is-sale="{{ $isSalePrice }}"
                                    is-purchase="{{ $isPurchasePrice }}"
                                    search-char-limit="{{ $searchCharLimit }}"
                                />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
