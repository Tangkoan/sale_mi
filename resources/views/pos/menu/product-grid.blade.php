<div class="flex-1 overflow-y-auto overflow-x-hidden p-3 sm:p-5 pb-24 sm:pb-32 custom-scrollbar bg-gray-50 dark:bg-gray-900">
    
    {{-- ========================================================== --}}
    {{-- COMPONENT 1: CATEGORY MENU (បង្ហាញដំបូងពេលចូលមកដល់) --}}
    {{-- ========================================================== --}}
    <div x-show="activeCategory === null && search === '' && viewMode === 'menu'" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4" 
         x-transition:enter-end="opacity-100 translate-y-0"
         class="h-full">
         
        <div class="mb-5">
            <h2 class="text-xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                <i class="ri-list-check text-primary"></i> ជ្រើសរើសប្រភេទម្ហូប
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">សូមជ្រើសរើសប្រភេទម្ហូបដែលលោកអ្នកចង់កុម្ម៉ង់</p>
        </div>

        {{-- Category Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3 sm:gap-4">
            <template x-for="cat in categories" :key="cat.id">
                <button @click="selectCategory(cat.id)" 
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 flex flex-row sm:flex-col items-center justify-start sm:justify-center gap-4 hover:shadow-lg hover:border-primary/50 transition-all active:scale-95 group">
                    
                    <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0 text-primary bg-primary/10 group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                        <i class="ri-goblet-line text-3xl"></i>
                    </div>
                    
                    <span class="font-bold text-base sm:text-sm text-gray-700 dark:text-gray-200 text-left sm:text-center line-clamp-2 leading-tight" x-text="cat.name"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- COMPONENT 2: PRODUCT LIST (បង្ហាញពេលចុចលើ Category ណាមួយ) --}}
    {{-- ========================================================== --}}
    <div x-show="activeCategory !== null || search !== '' || viewMode === 'addon'" 
         x-transition:enter="transition ease-out duration-300 delay-100" 
         x-transition:enter-start="opacity-0 translate-x-4" 
         x-transition:enter-end="opacity-100 translate-x-0" 
         style="display: none;">
        
        {{-- Toolbar: Back Button & Title --}}
        <div class="flex items-center gap-3 mb-5 bg-white dark:bg-gray-800 p-3 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700" x-show="viewMode === 'menu'">
            <button @click="backToCategories()" class="w-10 h-10 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-gray-600 dark:text-gray-300 active:scale-90">
                <i class="ri-arrow-left-line text-xl"></i>
            </button>
            <div class="flex-1">
                <h2 class="text-lg font-black text-gray-800 dark:text-white leading-tight" x-text="currentCategoryName"></h2>
                {{-- ប្តូរអក្សរទៅជាកំពុងស្វែងរកពេល Loading --}}
                <p class="text-xs text-primary font-bold" x-text="isLoading ? 'កំពុងស្វែងរក...' : filteredProducts.length + ' មុខ'"></p>
            </div>
        </div>

        {{-- វិលៗ Loading State (បង្ហាញតែពេល isLoading = true) --}}
        <div x-show="isLoading" class="flex flex-col items-center justify-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
            <svg class="animate-spin h-10 w-10 text-primary mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-500 dark:text-gray-400 font-bold">កំពុងទាញទិន្នន័យ...</span>
        </div>

        {{-- Products Grid & Empty State (បង្ហាញតែពេល isLoading = false) --}}
        <div x-show="!isLoading" style="display: none;">
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">
                <template x-for="product in filteredProducts" :key="product.id">
                    
                    {{-- Product Card --}}
                    <div @click="product.type === 'addon_item' ? addStandaloneAddon(product) : (product.is_active ? openProductModal(product) : null)" 
                         class="group bg-white dark:bg-gray-800 rounded-2xl p-2.5 shadow-sm border border-gray-100 dark:border-gray-700 transition-all relative overflow-hidden"
                         :class="product.is_active ? 'hover:shadow-xl cursor-pointer hover:-translate-y-1 active:scale-[0.97]' : 'cursor-not-allowed bg-gray-50 dark:bg-gray-900'">
                        
                        {{-- Image --}}
                        <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 relative mb-3">
                            <div x-show="!product.is_active" class="absolute inset-0 z-20 flex items-center justify-center bg-white/60 dark:bg-gray-900/60 backdrop-blur-[2px]">
                                 <div class="bg-red-500 text-white text-xs font-black px-2 py-1 rounded shadow-lg transform -rotate-12 border border-white">{{ __('messages.out_of_stock') }}</div>
                            </div>
                            <template x-if="product.image">
                                <img :src="'/storage/' + product.image" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" :class="!product.is_active ? 'grayscale opacity-60' : ''">
                            </template>
                            <template x-if="!product.image">
                                <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600"><i class="ri-image-line text-3xl"></i></div>
                            </template>
                            
                            {{-- Add Icon --}}
                            <div x-show="product.is_active" class="absolute bottom-2 right-2 bg-primary/95 backdrop-blur-sm rounded-lg w-8 h-8 flex items-center justify-center shadow-lg text-white opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                                <i class="ri-add-line font-bold text-lg"></i>
                            </div>
                        </div>

                        {{-- Text Info --}}
                        <div class="px-1">
                            <h3 class="font-bold text-sm leading-snug line-clamp-2 min-h-[2.5em]" :class="product.is_active ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500'" x-text="product.name"></h3>
                            <div class="mt-2 text-primary font-black text-base" x-text="formatNumber(product.price) + ' ៛'"></div>
                        </div>
                    </div>
                </template>

                {{-- Empty State (លោតចេញតែពេលទាញទិន្នន័យរួចរាល់ ហើយគ្មាន Product) --}}
                <div x-show="filteredProducts.length === 0" class="col-span-full py-16 text-center text-gray-400 bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                    <i class="ri-search-eye-line text-4xl mb-2"></i>
                    <p class="font-bold text-lg">មិនមានទិន្នន័យ</p>
                    <p class="text-sm mt-1">សូមសាកល្បងស្វែងរកឈ្មោះផ្សេង</p>
                </div>
            </div>

        </div>
    </div>
</div>