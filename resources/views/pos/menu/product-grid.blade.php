<div class="flex-1 overflow-y-auto overflow-x-hidden p-3 sm:p-4 pb-24 sm:pb-32 custom-scrollbar">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">
        <template x-for="product in filteredProducts" :key="product.id">
            
            {{-- MAIN CARD --}}
            {{-- 🔥 កែសម្រួលត្រង់នេះ៖ ឆែកមើលថាតើវាជា Addon ឬ Product ធម្មតា --}}
            <div @click="product.type === 'addon_item' ? addStandaloneAddon(product) : (product.is_active ? openProductModal(product) : null)" 
                 class="group bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl p-2 sm:p-2.5 shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-300 relative overflow-hidden"
                 :class="product.is_active ? 'hover:shadow-lg cursor-pointer hover:-translate-y-1 active:scale-95' : 'cursor-not-allowed bg-gray-50 dark:bg-gray-900'">
                
                {{-- 1. IMAGE CONTAINER --}}
                <div class="aspect-square rounded-lg sm:rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-700 relative mb-2 sm:mb-3">
                    
                    {{-- OVERLAY: "អស់ពីស្តុក" --}}
                    <div x-show="!product.is_active" class="absolute inset-0 z-20 flex items-center justify-center bg-white/60 dark:bg-gray-900/60 backdrop-blur-[1px]">
                         <div class="bg-red-500 text-white text-[10px] sm:text-xs font-bold px-2 py-1 rounded shadow-md transform -rotate-12 border border-white">
                             Out of Stock
                        </div>
                    </div>

                    {{-- រូបភាព --}}
                    <template x-if="product.image">
                        <img :src="'/storage/' + product.image" 
                             class="w-full h-full object-cover transition-transform duration-500" 
                             :class="product.is_active ? 'group-hover:scale-110' : 'grayscale opacity-70'">
                    </template>
                    
                    {{-- Placeholder --}}
                    <template x-if="!product.image">
                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-600"
                             :class="!product.is_active ? 'opacity-50' : ''">
                            <i class="ri-image-2-line text-2xl sm:text-3xl mb-1"></i>
                            <span class="text-[8px] sm:text-[10px] uppercase font-bold tracking-widest">No Image</span>
                        </div>
                    </template>
                    
                    {{-- Add Icon (បង្ហាញតែពេល Active) --}}
                    <div x-show="product.is_active" class="absolute bottom-1 right-1 sm:bottom-2 sm:right-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center shadow-md text-primary opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                        <i class="ri-add-line font-bold text-base sm:text-lg"></i>
                    </div>
                </div>

                {{-- 2. TEXT CONTENT --}}
                <div class="px-0.5 sm:px-1">
                    <h3 class="font-bold text-xs sm:text-sm leading-snug line-clamp-2 min-h-[2.5em]" 
                        :class="product.is_active ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500 line-through decoration-gray-300'"
                        x-text="product.name">
                    </h3>
                    
                    <div class="mt-1 sm:mt-2 flex items-center justify-between">
                        <span class="font-black text-sm sm:text-base" 
                              :class="product.is_active ? 'text-primary' : 'text-gray-400 dark:text-gray-600'" 
                              x-text="'$' + parseFloat(product.price).toFixed(2)">
                        </span>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="filteredProducts.length === 0" class="col-span-full flex flex-col items-center justify-center py-10 sm:py-20 text-gray-400">
            <i class="ri-search-2-line text-3xl sm:text-4xl mb-2"></i>
            <p class="font-medium text-sm sm:text-base">No products found.</p>
        </div>
    </div>
</div>