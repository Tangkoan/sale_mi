{{-- CHANGE ITEM LIST MODAL (បញ្ជីមុខម្ហូបលើតុ) --}}
<div x-show="isChangeItemListModalOpen" 
     style="display: none;"
     class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6"
     x-cloak>
    
    <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm" x-show="isChangeItemListModalOpen" x-transition.opacity @click="isChangeItemListModalOpen = false"></div>

    <div class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-[20px] shadow-2xl flex flex-col max-h-[85vh] overflow-hidden"
         x-show="isChangeItemListModalOpen"
         x-transition:enter="transition ease-out duration-300 transform" 
         x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform" 
         x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
         x-transition:leave-end="opacity-0 translate-y-8 scale-95">

        <div class="px-5 py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center shrink-0">
            <div>
                <h2 class="text-lg font-black text-gray-800 dark:text-white">បញ្ជីមុខម្ហូប - តុ <span x-text="selectedTable ? selectedTable.name : ''"></span></h2>
                <p class="text-xs text-gray-500">ចុចប៊ូតុង "ប្ដូរ" ដើម្បីផ្លាស់ប្ដូរមុខម្ហូបណាមួយ</p>
            </div>
            <button @click="isChangeItemListModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 text-gray-600 hover:bg-red-100 hover:text-red-500 transition-colors">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-gray-50 dark:bg-gray-900/50 relative">
            
            {{-- Loading Spinner --}}
            <div x-show="isLoadingChangeItemList" class="absolute inset-0 flex items-center justify-center bg-gray-50/80 dark:bg-gray-900/80 z-10">
                <i class="ri-loader-4-line text-4xl animate-spin text-blue-500"></i>
            </div>

            <div class="space-y-3">
                <template x-if="!isLoadingChangeItemList && changeItemListItems.length === 0">
                    <div class="text-center py-10 text-gray-400">មិនទាន់មានការកម្ម៉ង់ទេ</div>
                </template>

                <template x-for="item in changeItemListItems" :key="'list-'+item.id">
                    <div class="bg-white dark:bg-gray-800 p-3.5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex-1 min-w-0 pr-4">
                            <div class="font-bold text-[14px] text-gray-900 dark:text-white truncate" x-text="item.product ? item.product.name : 'មុខម្ហូប'"></div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                <span class="font-bold text-gray-700 dark:text-gray-300" x-text="'Qty: ' + item.quantity"></span> 
                                <span class="mx-1">•</span> 
                                <span class="text-blue-600 font-bold" x-text="formatRiel(item.price) + '៛'"></span>
                                <span class="mx-1">•</span> 
                                <span :class="item.is_printed ? 'text-green-500' : 'text-orange-500'" x-text="item.is_printed ? 'កំពុងធ្វើ' : 'រង់ចាំព្រីន'"></span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 shrink-0">
                            {{-- ប៊ូតុងបន្ថែមម្ហូបចូលតុ (ទៅទំព័រ Menu) --}}
                            <a :href="'/pos/menu/' + selectedTable.id" class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-200 transition-colors">បន្ថែមថែម</a>
                            
                            {{-- 🔥 ប៊ូតុងប្ដូរម្ហូប (ហៅ Modal ប្ដូរ) --}}
                            <button @click="openChangeItemModal(item)" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 text-xs font-bold hover:bg-blue-600 hover:text-white transition-colors flex items-center gap-1">
                                <i class="ri-loop-left-line"></i> ប្ដូរ
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>