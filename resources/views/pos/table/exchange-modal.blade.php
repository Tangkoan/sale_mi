{{-- CHANGE ITEM ACTION MODAL (ស្វែងរកម្ហូបថ្មី) --}}
<div x-show="isChangeItemModalOpen" style="display: none;" class="fixed inset-0 z-[80] flex items-center justify-center p-4 sm:p-6" x-cloak>
    <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm" x-show="isChangeItemModalOpen" x-transition.opacity @click="isChangeItemModalOpen = false"></div>

    <div class="relative w-full max-w-lg bg-gray-50 dark:bg-gray-900 rounded-[20px] shadow-2xl flex flex-col max-h-[90vh] overflow-hidden"
         x-show="isChangeItemModalOpen" x-transition:enter="transition ease-out duration-300 transform">

        <div class="px-5 py-4 bg-white dark:bg-gray-800 border-b border-gray-100 flex justify-between items-center shrink-0">
            <h2 class="text-lg font-black text-blue-600"><i class="ri-loop-left-line mr-2"></i>ប្ដូរមុខម្ហូប</h2>
            <button @click="isChangeItemModalOpen = false" class="text-gray-500 hover:text-red-500"><i class="ri-close-line text-xl"></i></button>
        </div>

        <div class="flex-1 overflow-y-auto p-5 space-y-5 custom-scrollbar">
            <div class="bg-white rounded-xl p-4 border border-red-100 relative">
                <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">1. មុខម្ហូបចាស់ដែលត្រូវដកចេញ</label>
                <div class="flex justify-between items-center">
                    <div class="font-bold text-gray-900 text-[15px]" x-text="changeItemData.old_item_name"></div>
                    <div class="flex items-center bg-gray-100 rounded-lg p-1 border">
                        <button @click="if(changeItemData.exchange_qty > 1) changeItemData.exchange_qty--" class="w-8 h-8 rounded-md bg-white shadow-sm hover:text-red-500"><i class="ri-subtract-line"></i></button>
                        <div class="w-10 text-center font-black" x-text="changeItemData.exchange_qty"></div>
                        <button @click="if(changeItemData.exchange_qty < changeItemData.max_qty) changeItemData.exchange_qty++" class="w-8 h-8 rounded-md bg-white shadow-sm hover:text-blue-500"><i class="ri-add-line"></i></button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-blue-100 relative">
                <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">2. ជ្រើសរើសមុខម្ហូបថ្មីជំនួស</label>
                
                <div class="relative mb-3">
                    <i class="ri-search-2-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" x-model="changeItemSearch" @input="debounceChangeItemSearch()" placeholder="ស្វែងរកមុខម្ហូប..." 
                           class="w-full bg-gray-50 border rounded-lg pl-9 pr-4 py-2.5 text-sm outline-none">
                </div>

                <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-2 border rounded-lg p-1 bg-gray-50">
                    <template x-for="product in changeItemProducts" :key="'cip-'+product.id">
                        <button @click="selectNewChangeItemProduct(product)" 
                                class="w-full text-left p-2.5 rounded-lg border flex justify-between items-center"
                                :class="changeItemData.new_product_id === product.id ? 'bg-blue-50 border-blue-300' : 'bg-white border-transparent'">
                            <div class="font-bold text-sm" x-text="product.name"></div>
                            <div class="text-xs font-bold text-blue-600" x-text="formatRiel(product.price) + '៛'"></div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white border-t flex gap-3 shrink-0">
            <button @click="isChangeItemModalOpen = false" class="flex-1 py-3 rounded-xl border font-bold hover:bg-gray-50">បោះបង់</button>
            <button @click="submitChangeItem()" :disabled="!changeItemData.new_product_id || isProcessing"
                    class="flex-[2] py-3 rounded-xl font-bold text-white shadow-lg flex justify-center gap-2"
                    :class="!changeItemData.new_product_id || isProcessing ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700'">
                <span x-text="isProcessing ? 'កំពុងដំណើរការ...' : 'បញ្ជាក់ការប្ដូរម្ហូប'"></span>
            </button>
        </div>
    </div>
</div>