<div x-show="activeCategory === null" class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar" x-transition.opacity x-cloak>
    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
        <i class="ri-layout-grid-fill text-primary"></i> 
        <span>សូមជ្រើសរើសប្រភេទមុខម្ហូប</span>
    </h2>
    
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        
        {{-- ប៊ូតុង ទាំងអស់ (All) --}}
        <div @click="activeCategory = 'all'; $dispatch('pos-category-changed', 'all')" 
             class="group bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:-translate-y-1 hover:border-primary/50 transition-all duration-300 cursor-pointer flex flex-col items-center justify-center aspect-square">
            <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <i class="ri-restaurant-line text-3xl"></i>
            </div>
            <h3 class="font-bold text-gray-800 dark:text-white text-center text-sm sm:text-base">ទាំងអស់ (All)</h3>
        </div>

        {{-- រាយ Categories ផ្សេងៗ --}}
        <template x-for="cat in categories" :key="cat.id">
            <div @click="activeCategory = cat.id; $dispatch('pos-category-changed', cat.id)" 
                 class="group bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-lg hover:-translate-y-1 hover:border-primary/50 transition-all duration-300 cursor-pointer flex flex-col items-center justify-center aspect-square">
                <div class="w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <i class="ri-cup-line text-3xl"></i> 
                </div>
                <h3 class="font-bold text-gray-800 dark:text-white text-center text-sm sm:text-base line-clamp-2" x-text="cat.name"></h3>
            </div>
        </template>

    </div>
</div>