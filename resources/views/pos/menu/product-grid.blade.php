<style>
    /* លាក់ Scrollbar សម្រាប់ Category និង Product List ឲ្យមើលទៅដូច App */
    .hide-scroll-bar::-webkit-scrollbar { display: none; }
    .hide-scroll-bar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="flex-1 flex flex-col h-full bg-[#F6F8FC] dark:bg-gray-900 overflow-hidden relative">
    
    {{-- ========================================================== --}}
    {{-- CATEGORY STRIP (STICKY TOP) - Mobile App Style --}}
    {{-- ========================================================== --}}
    <div x-show="viewMode === 'menu'" 
         class="sticky top-0 z-20 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 py-2 px-3 overflow-x-auto hide-scroll-bar shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)]">
        <div class="flex items-center gap-2 w-max">
            {{-- ប៊ូតុង All --}}
            <button @click="selectCategory('all')" 
                    class="px-5 py-2 rounded-full text-[13px] font-bold whitespace-nowrap transition-all duration-200"
                    :class="activeCategory === 'all' ? 'bg-primary text-white shadow-md shadow-primary/30' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200'">
                ទាំងអស់ (All)
            </button>
            
            {{-- ប៊ូតុង Category ផ្សេងៗ --}}
            <template x-for="cat in categories" :key="cat.id">
                <button @click="selectCategory(cat.id)" 
                        class="px-5 py-2 rounded-full text-[13px] font-bold whitespace-nowrap transition-all duration-200"
                        :class="activeCategory === cat.id ? 'bg-primary text-white shadow-md shadow-primary/30' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200'">
                    <span x-text="cat.name"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- PRODUCT LIST & INFINITE SCROLL --}}
    {{-- ========================================================== --}}
    <div class="flex-1 overflow-y-auto overflow-x-hidden p-3 pb-28 hide-scroll-bar" 
         id="scrollable-product-container"
         @scroll="handleScroll($event)">
        
        {{-- វិលៗ Loading ពេលទាញទិន្នន័យលើកដំបូង --}}
        <div x-show="isLoading" class="flex flex-col items-center justify-center py-20">
            <i class="ri-loader-4-line animate-spin text-primary text-4xl mb-3"></i>
            <span class="text-gray-400 text-sm font-bold">កំពុងរៀបចំមុខម្ហូប...</span>
        </div>

        {{-- Products Grid --}}
        <div x-show="!isLoading" style="display: none;">
            {{-- ប្តូរគម្លាត gap ឲ្យតូចបន្តិច ដើម្បីសមនឹងទូរស័ព្ទ --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                <template x-for="(product, index) in displayProducts" :key="product.id + '-' + index">
                    
                    {{-- Product Card - App Style Design --}}
                    <div @click="product.type === 'addon_item' ? addStandaloneAddon(product) : (product.is_active ? openProductModal(product) : null)" 
                         class="bg-white dark:bg-gray-800 rounded-[20px] p-2 flex flex-col relative transition-all duration-200"
                         :class="product.is_active ? 'shadow-[0_2px_12px_-4px_rgba(0,0,0,0.08)] active:scale-[0.96] border border-transparent' : 'opacity-75 grayscale-[50%] bg-gray-50 border border-gray-200'">
                        
                        {{-- Image Area --}}
                        <div class="aspect-square rounded-[16px] overflow-hidden bg-gray-100 dark:bg-gray-700 relative">
                            {{-- Out of stock badge --}}
                            <div x-show="!product.is_active" class="absolute inset-0 z-20 flex items-center justify-center bg-black/40 backdrop-blur-[1px]">
                                 <div class="bg-red-500 text-white text-[10px] uppercase font-black px-2 py-1 rounded-md shadow-lg transform -rotate-12 border border-white/50">អស់ស្តុក</div>
                            </div>
                            
                            <template x-if="product.image">
                                <img :src="'/storage/' + product.image" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!product.image">
                                <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
                                    <i class="ri-restaurant-line text-3xl"></i>
                                </div>
                            </template>
                        </div>

                        {{-- Text & Info Area --}}
                        <div class="pt-2 px-1 pb-1 flex flex-col flex-1 justify-between gap-1">
                            {{-- Product Name (តូចជាងមុនបន្តិច តែច្បាស់) --}}
                            <h3 class="text-[13px] leading-[1.3] font-bold text-gray-800 dark:text-gray-100 line-clamp-2 h-[34px]" x-text="product.name"></h3>
                            
                            {{-- Price & Add Icon Row --}}
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-primary font-black text-[14px]" x-text="formatNumber(product.price) + ' ៛'"></span>
                                
                                {{-- ប៊ូតុង (+) តូចស្ទីល App លោតចេញពេល Active --}}
                                <div x-show="product.is_active" class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <i class="ri-add-line font-black text-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Loading បន្ថែមនៅពេលអូសចុះក្រោមដល់បាត --}}
            <div x-show="isLoadingMore" class="w-full flex justify-center py-6 mt-2">
                <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-full shadow-sm border border-gray-100 flex items-center gap-2">
                    <i class="ri-loader-4-line animate-spin text-primary text-lg"></i>
                    <span class="text-[12px] font-bold text-gray-500">កំពុងទាញបន្ថែម...</span>
                </div>
            </div>

            {{-- អស់ទិន្នន័យ --}}
            <div x-show="!isLoadingMore && !hasMorePages && displayProducts.length > 0 && viewMode === 'menu'" class="w-full text-center py-6 text-gray-400 text-[12px] font-medium opacity-60">
                • អស់ទិន្នន័យត្រឹមនេះ •
            </div>

            <div x-show="isLoadingMore && viewMode === 'menu'" class="w-full flex justify-center py-6 mt-2">
                <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-full shadow-sm border border-gray-100 flex items-center gap-2">
                    <i class="ri-loader-4-line animate-spin text-primary text-lg"></i>
                    <span class="text-[12px] font-bold text-gray-500">កំពុងទាញបន្ថែម...</span>
                </div>
            </div>

            {{-- Empty State (គ្មានទិន្នន័យទាល់តែសោះ) --}}
            <div x-show="displayProducts.length === 0 && !isLoading" class="col-span-full py-16 text-center flex flex-col items-center justify-center text-gray-400 mt-4">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-3">
                    <i class="ri-search-eye-line text-2xl"></i>
                </div>
                <p class="font-bold text-sm">មិនមានទិន្នន័យ</p>
                <p class="text-[12px] mt-1 opacity-70">សូមសាកល្បងស្វែងរកម្ដងទៀត</p>
            </div>
        </div>
    </div>
</div>