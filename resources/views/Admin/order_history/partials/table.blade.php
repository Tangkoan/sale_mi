<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 font-bold" x-show="showCols.invoice">លេខវិក្កយបត្រ</th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.table">តុបញ្ជាទិញ</th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary group" @click="sort('total_amount')" x-show="showCols.amount">
                        <div class="flex items-center gap-1">ទឹកប្រាក់សរុប <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.method">បង់តាម</th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary group" @click="sort('created_at')" x-show="showCols.date">
                        <div class="flex items-center gap-1">កាលបរិច្ឆេទ <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold text-center">សកម្មភាព (Actions)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="order in orders" :key="'order-desktop-' + order.id">
                    <tr class="hover:bg-page-bg/30 transition-colors">
                        <td class="px-6 py-4 font-bold text-primary" x-show="showCols.invoice" x-text="order.invoice_number"></td>
                        <td class="px-6 py-4 text-text-color font-semibold" x-show="showCols.table" x-text="order.merged_table_names ? order.merged_table_names : (order.table ? order.table.name : 'Takeaway/Delivery')"></td>
                        <td class="px-6 py-4 font-black text-green-600" x-show="showCols.amount" x-text="parseFloat(order.total_amount).toLocaleString() + ' ៛'"></td>
                        <td class="px-6 py-4" x-show="showCols.method">
                            <span class="px-3 py-1 text-[11px] font-bold uppercase rounded-md border"
                                :class="{
                                    'bg-blue-50 text-blue-600 border-blue-200': order.payment_method === 'qr',
                                    'bg-green-50 text-green-600 border-green-200': order.payment_method === 'cash',
                                    'bg-purple-50 text-purple-600 border-purple-200': order.payment_method === 'card'
                                }" x-text="order.payment_method"></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary" x-show="showCols.date">
                            <span x-text="new Date(order.created_at).toLocaleString('en-GB')"></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                {{-- ប៊ូតុងមើលលម្អិត --}}
                                @can('order-history-view')
                                <button @click="viewDetails(order.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100" title="មើលលម្អិត"><i class="ri-eye-line"></i></button>
                                @endcan

                                @can('order-history-reprint')
                                {{-- ប៊ូតុង Print --}}
                                <button @click="reprint(order.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-orange-50 text-orange-600 hover:bg-orange-100" title="ព្រីនឡើងវិញ">
                                    <i class="ri-printer-line" x-show="!isPrinting"></i>
                                    <i class="ri-loader-4-line animate-spin" x-show="isPrinting" style="display: none;"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="orders.length === 0"><td colspan="100%" class="px-6 py-12 text-center text-secondary"><i class="ri-inbox-line text-4xl mb-2 inline-block"></i><p>មិនមានប្រវត្តិការកម្ម៉ង់ទេ!</p></td></tr>
            </tbody>
        </table>
    </div>
</div>