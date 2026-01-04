@php
    $sunat_units = config('peru-core.sunat.units', [['id' => 'NIU', 'name' => 'NIU (UNIDAD)']]);
@endphp
<tbody is="draggable" tag="tbody" handle=".handle" @start="dragging = true" @end="dragging = false" @update="onItemSortUpdate">
    <tr v-for="(row, index) in items" :key="index" class="hover:bg-gray-50 border-b">
        <td class="p-0" colspan="8">
            <table class="w-full table-fixed">
                <colgroup>
                    <col style="width: 30px;">
                    <col style="width: 12%;"> <!-- CÓDIGO -->
                    <col style="width: 28%;"> <!-- ITEM -->
                    <col style="width: 10%;"> <!-- CANT. -->
                    <col style="width: 10%;"> <!-- U.M. -->
                    <col style="width: 15%;"> <!-- VALOR UNIT. -->
                    <col style="width: 15%;"> <!-- PRECIO VENTA -->
                    <col style="width: 30px;"> <!-- DELETE -->
                </colgroup>
                <tbody>
                    <tr>
                        <td class="align-middle text-center pt-1"><span class="material-icons text-gray-300 cursor-move handle" style="font-size: 18px;">list</span></td>
                        
                        <!-- 1. CÓDIGO -->
                        <td class="px-2 py-3 align-middle text-[11px] text-gray-600 font-mono">
                            <div class="min-h-[38px] flex items-center px-1">@{{ row.sku || '---' }}</div>
                            <input type="hidden" v-model="row.sku">
                        </td>

                        <!-- 2. ITEM -->
                        <td class="px-2 py-3 align-middle">
                            <span class="flex items-center text-sm min-h-[38px] px-1 font-bold text-black" v-if="row.item_id" v-html="row.name"></span>
                            <input v-else type="text" class="w-full text-sm px-2 py-1.5 rounded border" v-model="row.name">
                        </td>

                        <!-- 3. CANTIDAD -->
                        <td class="px-2 py-3 align-middle">
                            <input
                                type="number"
                                class="w-full text-sm px-2 py-1.5 text-right rounded border focus:ring-0"
                                :class="(typeof canEditCreditNoteField === 'function' && !canEditCreditNoteField('quantity')) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''"
                                v-model.number="row.quantity"
                                @input="onCalculateTotal"
                                :readonly="(typeof canEditCreditNoteField === 'function' && !canEditCreditNoteField('quantity'))"
                                step="any"
                            >
                        </td>

                        <!-- 4. U.M. -->
                        <td class="px-2 py-3 align-middle text-center text-[11px] text-gray-600 uppercase">
                            <div class="min-h-[38px] flex items-center justify-center">@{{ row.sunat_unit_code || 'NIU' }}</div>
                            <input type="hidden" v-model="row.sunat_unit_code">
                        </td>

                        <!-- 5. VALOR UNITARIO -->
                        <td class="px-2 py-3 align-middle text-right pr-2">
                            <div class="flex items-center justify-end">
                                <span class="text-[10px] text-gray-400 mr-1">S/</span>
                                <input
                                    type="number"
                                    class="w-24 text-sm px-2 py-1.5 text-right rounded border focus:ring-0"
                                    :class="(typeof canEditCreditNoteField === 'function' && !canEditCreditNoteField('price')) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''"
                                    v-model.number="row.price"
                                    @input="onCalculateTotal"
                                    :readonly="(typeof canEditCreditNoteField === 'function' && !canEditCreditNoteField('price'))"
                                    step="any"
                                >
                            </div>
                        </td>

                        <!-- 6. PRECIO VENTA -->
                        <td class="px-2 py-3 align-middle text-right font-bold text-black text-sm pr-4">
                            <div class="min-h-[38px] flex items-center justify-end">
                                <span class="text-[10px] text-gray-400 mr-1 font-normal">S/</span>
                                @{{ row.total }}
                            </div>
                        </td>

                        <td class="align-middle text-center">
                            <button
                                type="button"
                                @click="(typeof canEditCreditNoteField === 'function' ? canEditCreditNoteField('delete') : true) && onDeleteItem(index)"
                                :disabled="(typeof canEditCreditNoteField === 'function' && !canEditCreditNoteField('delete'))"
                                class="text-gray-300 hover:text-red-500 transition-colors"
                                :class="(typeof canEditCreditNoteField === 'function' && !canEditCreditNoteField('delete')) ? 'opacity-50 cursor-not-allowed' : ''"
                            ><span class="material-icons-outlined text-lg">delete</span></button>
                        </td>
                    </tr>
                    
                    <!-- SECCIÓN DETALLE -->
                    <tr class="bg-white border-none">
                        <td colspan="2"></td>
                        <td colspan="3" class="px-2 pb-2 align-top">
                            <textarea
                                class="w-full text-sm text-black bg-gray-50 border-none focus:ring-0 focus:bg-white rounded p-2 resize-none leading-tight"
                                :class="(typeof canEditCreditNoteField === 'function' && !canEditCreditNoteField('description')) ? 'text-gray-400 cursor-not-allowed' : ''"
                                rows="1"
                                v-model="row.description"
                                :readonly="(typeof canEditCreditNoteField === 'function' && !canEditCreditNoteField('description'))"
                                placeholder="Añadir descripción..."
                            ></textarea>
                        </td>
                        <td colspan="3" class="px-2 pb-2 text-right pr-4 align-top">
                            <div class="flex flex-col items-end space-y-1">
                                <!-- Impuestos (Solo visualización segura) -->
                                <div v-for="tax in (row.taxes || [])" class="text-sm text-gray-400 font-medium uppercase">
                                    @{{ tax.name }}: S/ @{{ (tax.amount !== undefined && tax.amount !== null) ? tax.amount : tax.price }}
                                </div>

                                <!-- Botón Descuento (CSS EXACTO SOLICITADO) -->
                                <div class="mt-1" v-if="typeof canEditCreditNoteField !== 'function' || canEditCreditNoteField('discount')">
                                    <button v-if="!row.add_discount" type="button" class="hover:underline focus:outline-none" style="font-size:12px;line-height:1;color:#6b7280;background:transparent;padding:0;border:0" @click="$set(row, 'add_discount', true)">+ Agregar Descuento</button>
                                    
                                    <div v-if="row.add_discount" class="flex items-center bg-gray-50 border rounded p-1 scale-90 origin-right shadow-sm">
                                        <button type="button" class="px-2 py-0.5 text-[10px] rounded" :class="row.discount_type === 'percentage' ? 'bg-purple text-white' : ''" @click="row.discount_type = 'percentage'; onCalculateTotal()">%</button>
                                        <button type="button" class="px-2 py-0.5 text-[10px] rounded" :class="row.discount_type === 'fixed' ? 'bg-purple text-white' : ''" @click="row.discount_type = 'fixed'; onCalculateTotal()">S/</button>
                                        <input type="number" class="w-16 ml-1 text-xs border-none bg-transparent text-right" v-model.number="row.discount" @input="onCalculateTotal">
                                        <button type="button" @click="$set(row, 'add_discount', false); row.discount = 0; onCalculateTotal()" class="ml-1 text-red-500 font-bold">×</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</tbody>
