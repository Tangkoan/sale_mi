<div class="px-6 py-4 border-t border-border-color flex flex-col sm:flex-row justify-between items-center gap-4" 
     x-show="pagination.total > 0">
    
    {{-- ផ្នែកទី ១: Dropdown Show Per Page --}}
    <div class="flex items-center gap-2">
        <span class="text-sm text-secondary whitespace-nowrap">Show:</span>
        <select x-model="perPage" 
                @change="gotoPage(1)" 
                class="w-24 bg-page-bg border border-input-border text-text-color text-sm rounded-lg focus:ring-primary focus:border-primary block p-2 outline-none cursor-pointer">
            <option value="1">1</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="all">All</option>
        </select>
    </div>

    {{-- ផ្នែកទី ២: ប៊ូតុងលេខទំព័រ --}}
    <div class="flex items-center gap-1">
        {{-- ប៊ូតុង Previous --}}
        {{-- <button 
            @click="gotoPage(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-text-color border-input-border">
            &laquo;
        </button> --}}

        {{-- លេខទំព័រ (Loop ប្រើ pagination.last_page ពី Server) --}}
        <template x-for="page in pagination.last_page" :key="page">
            {{-- បង្ហាញលេខទំព័រតែប្រហែល 5 លេខដើម្បីកុំឱ្យវែងពេក (Optional Logic) --}}
            <button 
                x-show="page === 1 || page === pagination.last_page || (page >= currentPage - 2 && page <= currentPage + 2)"
                @click="gotoPage(page)" 
                x-text="page"
                :class="currentPage === page ? 'bg-primary text-white border-primary' : 'text-text-color hover:bg-gray-100 border-input-border'"
                class="px-3 py-1 text-sm border rounded transition-colors duration-200">
            </button>
        </template>

        {{-- ប៊ូតុង Next --}}
        {{-- <button 
            @click="gotoPage(currentPage + 1)" 
            :disabled="currentPage === pagination.last_page"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-text-color border-input-border">
            &raquo;
        </button> --}}
    </div>
</div>