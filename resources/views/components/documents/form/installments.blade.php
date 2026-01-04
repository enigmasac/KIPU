<div v-if="form.sale_type === 'credit'" class="mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Cuotas del Crédito</h3>
        <button type="button" @click="addInstallment" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span class="material-icons-outlined text-sm mr-1">add</span>
            Agregar Cuota
        </button>
    </div>

    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">#</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Monto</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Vencimiento</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Eliminar</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(installment, index) in form.installments" :key="index">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                        @{{ index + 1 }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <input type="number" :name="'installments[' + index + '][amount]'" v-model="installment.amount" step="0.01" class="w-full text-sm text-gray-700 bg-white p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <input type="date" :name="'installments[' + index + '][due_at]'" v-model="installment.due_at" class="w-full text-sm text-gray-700 bg-white p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </td>
                    <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                        <button type="button" @click="removeInstallment(index)" class="text-red-600 hover:text-red-900">
                            <span class="material-icons-outlined text-sm">delete</span>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div v-if="installmentTotalMismatch" class="mt-2 text-sm text-red-600 font-medium">
        La suma de las cuotas (@{{ formatMoney(totalInstallments) }}) no coincide con el total de la factura (@{{ formatMoney(form.amount) }}).
    </div>
</div>
