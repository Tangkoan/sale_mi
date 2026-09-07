<div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

    <div class="relative w-full max-w-lg bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden flex flex-col max-h-[85vh]"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30">
            <h3 class="text-lg font-bold text-text-color"><i class="ri-file-list-3-line text-primary mr-2"></i> លម្អិតវិក្កយបត្រ</h3>
            <button @click="closeModal()" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
        </div>
        
        {{-- Body: Receipt Preview --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-gray-50 dark:bg-gray-900">
            <template x-if="selectedOrder">
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-xl shadow-sm border border-border-color">
                    <div class="text-center mb-4 border-b border-dashed border-gray-300 pb-4">
                        <h2 class="text-xl font-black text-text-color mb-1" x-text="selectedOrder.invoice_number"></h2>
                        <p class="text-xs text-secondary mb-1">តុ (Table): <span class="font-bold text-text-color" x-text="selectedOrder.merged_table_names || (selectedOrder.table ? selectedOrder.table.name : 'N/A')"></span></p>
                        <p class="text-xs text-secondary">កាលបរិច្ឆេទ: <span x-text="new Date(selectedOrder.created_at).toLocaleString('en-GB')"></span></p>
                    </div>

                    <div class="space-y-3 mb-4">
                        <template x-for="item in selectedOrder.items" :key="'prev-'+item.id">
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex-1 pr-4">
                                    <p class="font-bold text-text-color"><span x-text="item.quantity + 'x '"></span> <span x-text="item.product ? item.product.name : 'Unknown'"></span></p>
                                    <template x-if="item.addons && item.addons.length > 0">
                                        <div class="pl-4 mt-1 space-y-1">
                                            <template x-for="addon in item.addons">
                                                <p class="text-xs text-secondary">+ <span x-text="addon.addon ? addon.addon.name : 'Addon'"></span> (x<span x-text="addon.quantity"></span>)</p>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <span class="font-bold text-text-color whitespace-nowrap" x-text="(parseFloat(item.price) * item.quantity).toLocaleString() + ' ៛'"></span>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-dashed border-gray-300 pt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-secondary">វិធីទូទាត់:</span>
                            <span class="font-bold uppercase" x-text="selectedOrder.payment_method"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary">ប្រាក់ទទួលបាន:</span>
                            <span class="font-bold" x-text="parseFloat(selectedOrder.received_amount).toLocaleString() + ' ៛'"></span>
                        </div>
                        <div class="flex justify-between text-base">
                            <span class="font-black text-text-color">សរុប (Total):</span>
                            <span class="font-black text-green-600" x-text="parseFloat(selectedOrder.total_amount).toLocaleString() + ' ៛'"></span>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="!selectedOrder">
                <div class="flex justify-center items-center h-40 text-secondary"><i class="ri-loader-4-line animate-spin text-3xl"></i></div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="p-4 flex justify-end gap-3 border-t border-border-color bg-card-bg">
            <button type="button" @click="closeModal()" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg font-bold">បិទ</button>
            <template x-if="selectedOrder">
                <button type="button" @click="reprint(selectedOrder.id)" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 font-bold flex items-center gap-2 shadow-md" :disabled="isPrinting">
                    <i class="ri-printer-line" x-show="!isPrinting"></i>
                    <i class="ri-loader-4-line animate-spin" x-show="isPrinting"></i>
                    <span>ព្រីនវិក្កយបត្រ</span>
                </button>
            </template>
        </div>
    </div>
</div>