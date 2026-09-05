<style>
    /* បំបាត់សញ្ញាព្រួញឡើងចុះនៅក្នុង Input Number ឲ្យមើលទៅស្អាត */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    /* លាក់ Scrollbar ក្នុង Modal */
    .hide-modal-scroll::-webkit-scrollbar { display: none; }
    .hide-modal-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div x-show="isProductModalOpen" class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center sm:p-6" style="display: none;" x-cloak>
    
    {{-- Backdrop (ព្រាលផ្ទៃខាងក្រោយ) --}}
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" 
         x-show="isProductModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeProductModal()"></div>
    
    {{-- Modal Card (Bottom Sheet on Mobile, Centered Card on Desktop) --}}
    <div class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-t-[24px] sm:rounded-[24px] shadow-2xl overflow-hidden flex flex-col transition-all transform max-h-[90vh]" 
         x-show="isProductModalOpen" 
         x-transition:enter="transition cubic-bezier(0.22, 1, 0.36, 1) duration-300" 
         x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95" 
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95">
        
        {{-- Handle Bar សម្រាប់ Mobile (ប្រាប់ថាអាចទាញចុះក្រោមបាន) --}}
        <div class="w-full flex justify-center pt-3 pb-1 sm:hidden" @click="closeProductModal()">
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
        </div>

        {{-- 1. HEADER --}}
        <div class="flex justify-between items-start px-5 pt-2 sm:pt-5 pb-3">
            <div class="flex-1 pr-4">
                <h2 class="text-[18px] sm:text-[20px] font-bold text-gray-900 dark:text-white leading-tight" x-text="tempItem.name"></h2>
                <span class="inline-block mt-1 px-2 py-0.5 rounded text-gray-500 bg-gray-100 dark:bg-gray-800 text-[11px] font-medium uppercase tracking-wider" x-text="tempItem.category_name || 'Item'"></span>
            </div>
            <button @click="closeProductModal()" class="w-8 h-8 shrink-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-600 rounded-full transition-colors focus:outline-none active:scale-95">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>

        {{-- 2. CONTENT BODY --}}
        <div class="flex-1 overflow-y-auto hide-modal-scroll px-5 py-2 space-y-5">
            
            {{-- Price & Quantity Area --}}
            <div class="flex items-center justify-between gap-4">
                
                {{-- Price --}}
                <div class="flex flex-col">
                    <span class="text-[12px] font-medium text-gray-400">តម្លៃរាយ</span>
                    <span class="text-[22px] font-black text-gray-900 dark:text-white" x-text="formatNumber(tempItem.base_price) + ' ៛'"></span>
                </div>

                {{-- Quantity Control (App Style) --}}
                <div class="flex items-center bg-gray-50 dark:bg-gray-800 rounded-full border border-gray-100 dark:border-gray-700 p-1">
                    <button @click="if(tempItem.qty > 1) tempItem.qty--" 
                            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-600 hover:bg-white dark:hover:bg-gray-700 shadow-sm transition-all active:scale-90">
                        <i class="ri-subtract-line font-bold text-lg"></i>
                    </button>

                    <input type="number" 
                           x-model.number="tempItem.qty" 
                           @click="$event.target.select()"
                           class="w-12 h-10 text-center font-bold text-[16px] text-gray-900 dark:text-white bg-transparent border-none outline-none focus:ring-0 p-0"
                           min="1">

                    <button @click="tempItem.qty++" 
                            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-900 dark:text-white bg-white dark:bg-gray-700 shadow-sm transition-all active:scale-90">
                        <i class="ri-add-line font-bold text-lg"></i>
                    </button>
                </div>
            </div>

            <div class="h-px bg-gray-100 dark:bg-gray-800 w-full"></div>

            {{-- Addons List --}}
            <div x-show="availableAddons.length > 0">
                <h3 class="text-[13px] font-bold text-gray-900 dark:text-white mb-3">ជម្រើសបន្ថែម (Addons)</h3>
                <div class="space-y-2">
                    <template x-for="addon in availableAddons" :key="addon.id">
                        <div @click="toggleAddon(addon)"
                             class="flex items-center justify-between p-3 rounded-2xl border transition-all duration-200 cursor-pointer select-none active:scale-[0.98]"
                             :class="isAddonSelected(addon.id) ? 'border-gray-900 bg-gray-50 dark:bg-gray-800/50 dark:border-gray-500' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900'">
                            
                            <div class="flex items-center gap-3">
                                {{-- Checkbox Icon --}}
                                <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors"
                                     :class="isAddonSelected(addon.id) ? 'bg-gray-900 border-gray-900 dark:bg-gray-500' : 'border-gray-300 bg-gray-50 dark:border-gray-600'">
                                    <i class="ri-check-line text-white text-[12px]" x-show="isAddonSelected(addon.id)"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[14px] font-medium text-gray-800 dark:text-gray-200" x-text="addon.name"></span>
                                    <span class="text-[12px] text-gray-500 font-medium" x-text="'+ ' + formatNumber(addon.price) + ' ៛'"></span>
                                </div>
                            </div>

                            {{-- Addon Qty Control (បង្ហាញតែពេលជ្រើសរើស) --}}
                            <div x-show="isAddonSelected(addon.id)" @click.stop class="flex items-center bg-white dark:bg-gray-700 rounded-full border border-gray-200 dark:border-gray-600 h-8">
                                <button @click="updateAddonQty(addon.id, -1)" class="w-8 h-full text-gray-500 flex items-center justify-center rounded-l-full active:bg-gray-100"><i class="ri-subtract-line text-[12px]"></i></button>
                                <span class="text-[13px] font-bold w-5 text-center text-gray-900 dark:text-white" x-text="getAddonQty(addon.id)"></span>
                                <button @click="updateAddonQty(addon.id, 1)" class="w-8 h-full text-gray-500 flex items-center justify-center rounded-r-full active:bg-gray-100"><i class="ri-add-line text-[12px]"></i></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Note Section --}}
            <div>
                <h3 class="text-[13px] font-bold text-gray-900 dark:text-white mb-2">ចំណាំ</h3>
                <div class="relative">
                    <textarea x-model="tempItem.note" 
                              rows="2" 
                              class="w-full p-3 rounded-2xl bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-100 dark:border-gray-700 focus:ring-2 focus:ring-gray-200 focus:border-gray-400 outline-none text-[14px] resize-none transition-all" 
                              placeholder="ឧទាហរណ៍៖ មិនហឹរ, ផ្អែមល្មម..."></textarea>
                </div>
            </div>
            
            {{-- Spacing នៅខាងក្រោមដើម្បីកុំអោយទើសប៊ូតុង --}}
            <div class="h-2"></div>
        </div>

        {{-- 3. FOOTER (Action Button) --}}
        <div class="p-5 pt-3 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
            <button @click="addToCart()" 
                    class="w-full bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 h-14 rounded-2xl font-bold shadow-lg shadow-gray-900/20 transform transition-all active:scale-[0.98] flex items-center justify-between px-2 p-1 group">
                
                <div class="flex items-center pl-4">
                    <span class="text-[15px]">បញ្ចូលកុម្ម៉ង់</span>
                </div>

                {{-- Total Price Box in Button --}}
                <div class="flex items-center bg-white/20 dark:bg-black/10 rounded-xl px-4 py-2 h-11 justify-center">
                    <span class="font-bold text-[16px]" x-text="formatNumber(calculateItemTotal()) + ' ៛'"></span>
                </div>
            </button>
        </div>
    </div>
</div>