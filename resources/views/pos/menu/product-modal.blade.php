<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>

<div x-show="isProductModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" style="display: none;" x-cloak>
    
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
         x-show="isProductModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeProductModal()"></div>
    
    {{-- Modal Card (Compact No Image) --}}
    <div class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-2xl overflow-hidden flex flex-col transition-all transform" 
            x-show="isProductModalOpen" 
            x-transition:enter="transition cubic-bezier(0.22, 1, 0.36, 1) duration-300" 
            x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4">
        
        {{-- 1. HEADER (Title & Close) --}}
        <div class="flex justify-between items-start p-6 pb-2">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-tight" x-text="tempItem.name"></h2>
                <span class="inline-block mt-1 px-2.5 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-wider" x-text="tempItem.category_name || 'Item'"></span>
            </div>
            <button @click="closeProductModal()" class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 rounded-full transition-colors focus:outline-none">
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>

        {{-- 2. CONTENT BODY --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar px-6 py-2 space-y-6">
            
            {{-- Price & Manual Quantity Input --}}
            <div class="flex items-center justify-between gap-4 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-2xl border border-gray-100 dark:border-gray-700">
                
                {{-- Price --}}
                <div class="flex flex-col pl-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Unit Price</span>
                    <span class="text-3xl font-black text-primary" x-text="'$' + parseFloat(tempItem.base_price).toFixed(2)"></span>
                </div>

                {{-- Quantity Control (Editable) --}}
                <div class="flex items-center bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-1">
                    {{-- Minus --}}
                    <button @click="if(tempItem.qty > 1) tempItem.qty--" 
                            class="w-10 h-10 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors active:scale-90">
                        <i class="ri-subtract-line font-bold text-lg"></i>
                    </button>

                    {{-- Input Number (Allows Typing) --}}
                    <input type="number" 
                           x-model.number="tempItem.qty" 
                           @click="$event.target.select()"
                           class="w-14 h-10 text-center font-black text-xl text-gray-900 dark:text-white bg-transparent border-none outline-none focus:ring-0 p-0"
                           min="1">

                    {{-- Plus --}}
                    <button @click="tempItem.qty++" 
                            class="w-10 h-10 rounded-lg flex items-center justify-center text-gray-400 hover:text-green-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors active:scale-90">
                        <i class="ri-add-line font-bold text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Divider --}}
            <div class="h-px bg-gray-100 dark:bg-gray-800 w-full"></div>

            {{-- Addons List --}}
            <div x-show="availableAddons.length > 0">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3 flex items-center gap-1">
                    <i class="ri-puzzle-2-line"></i> Add-ons
                </h3>
                <div class="grid gap-2 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                    <template x-for="addon in availableAddons" :key="addon.id">
                        <div @click="toggleAddon(addon)"
                             class="flex items-center justify-between p-3 rounded-xl border transition-all duration-200 cursor-pointer select-none"
                             :class="isAddonSelected(addon.id) ? 'border-primary bg-primary/5 ring-1 ring-primary/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300'">
                            
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors"
                                     :class="isAddonSelected(addon.id) ? 'bg-primary border-primary' : 'border-gray-300 bg-gray-50'">
                                    <i class="ri-check-line text-white text-xs" x-show="isAddonSelected(addon.id)"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-200" x-text="addon.name"></span>
                                    <span class="text-[10px] text-gray-500" x-text="'+$' + parseFloat(addon.price).toFixed(2)"></span>
                                </div>
                            </div>

                            {{-- Mini Qty for Addon --}}
                            <div x-show="isAddonSelected(addon.id)" @click.stop class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg h-7">
                                <button @click="updateAddonQty(addon.id, -1)" class="w-7 h-full text-gray-500 hover:text-red-500 flex items-center justify-center"><i class="ri-subtract-line text-[10px]"></i></button>
                                <span class="text-xs font-bold w-4 text-center" x-text="getAddonQty(addon.id)"></span>
                                <button @click="updateAddonQty(addon.id, 1)" class="w-7 h-full text-gray-500 hover:text-green-500 flex items-center justify-center"><i class="ri-add-line text-[10px]"></i></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Note Section --}}
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Note</h3>
                <div class="relative">
                    <textarea x-model="tempItem.note" 
                              rows="2" 
                              class="w-full pl-4 pr-10 py-3 rounded-xl bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white border-none focus:ring-2 focus:ring-primary/20 transition-all text-sm resize-none shadow-sm" 
                              placeholder="Special instructions..."></textarea>
                    <i class="ri-edit-2-line absolute right-3 top-3 text-gray-400"></i>
                </div>
            </div>
        </div>

        {{-- 3. FOOTER (Action Button) --}}
        <div class="p-6 pt-4 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
            <button @click="addToCart()" 
                    class="w-full bg-primary hover:bg-primary-600 text-white h-14 rounded-2xl font-bold text-base shadow-xl shadow-primary/30 transform transition-all active:scale-[0.98] flex items-center justify-between px-2 p-1 group">
                
                <div class="flex items-center gap-3 pl-4">
                    <span class="uppercase tracking-wider font-extrabold text-sm">Add to Order</span>
                </div>

                <div class="flex items-center bg-white/20 backdrop-blur-sm rounded-xl px-4 py-2 h-10 mr-1 min-w-[80px] justify-center group-hover:bg-white group-hover:text-primary transition-colors">
                    <span class="font-black text-base" x-text="'$' + calculateItemTotal().toFixed(2)"></span>
                </div>
            </button>
        </div>
    </div>
</div>