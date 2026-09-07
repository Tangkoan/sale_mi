<div class="flex flex-col gap-4">
    <template x-for="order in orders" :key="'order-mobile-' + order.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative flex flex-col gap-3">
            
            <div class="flex justify-between items-start border-b border-dashed border-border-color pb-3">
                <div>
                    <h3 class="font-extrabold text-primary text-base" x-text="order.invoice_number"></h3>
                    <p class="text-xs text-secondary mt-1" x-text="new Date(order.created_at).toLocaleString('en-GB')"></p>
                </div>
                <span class="px-2 py-1 text-[10px] font-bold uppercase rounded border"
                    :class="order.payment_method === 'qr' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-green-50 text-green-600 border-green-200'" 
                    x-text="order.payment_method"></span>
            </div>

            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-secondary"><i class="ri-restaurant-line"></i></div>
                    <div>
                        <p class="text-[10px] text-secondary">តុ (Table)</p>
                        <p class="font-bold text-sm text-text-color" x-text="order.merged_table_names ? order.merged_table_names : (order.table ? order.table.name : 'Takeaway')"></p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-secondary">សរុប (Total)</p>
                    <p class="font-black text-green-600" x-text="parseFloat(order.total_amount).toLocaleString() + ' ៛'"></p>
                </div>
            </div>

            <div class="flex gap-2 pt-2">
                <button @click="viewDetails(order.id)" class="flex-1 bg-blue-50 text-blue-600 font-bold py-2 rounded-xl border border-blue-100 hover:bg-blue-100 flex justify-center items-center gap-1 text-sm">
                    <i class="ri-eye-line"></i> លម្អិត
                </button>
                <button @click="reprint(order.id)" class="flex-1 bg-orange-50 text-orange-600 font-bold py-2 rounded-xl border border-orange-100 hover:bg-orange-100 flex justify-center items-center gap-1 text-sm">
                    <i class="ri-printer-line"></i> ព្រីន
                </button>
            </div>
            
        </div>
    </template>
    
    <div x-show="orders.length === 0" class="text-center py-12 text-secondary bg-card-bg rounded-2xl border border-dashed border-border-color shadow-sm">
        <i class="ri-inbox-line text-5xl mb-3 inline-block opacity-40"></i>
        <p class="font-bold">មិនមានទិន្នន័យទេ!</p>
    </div>
</div>