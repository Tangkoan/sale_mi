<div class="mt-4 bg-card-bg rounded-xl shadow-sm border border-border-color px-4 py-3 flex flex-col sm:flex-row justify-between items-center gap-4" 
    x-show="pagination.total > 0" x-cloak>
    
    <div class="flex items-center gap-2">
        <span class="text-sm text-secondary whitespace-nowrap">{{ __('messages.show') }}:</span>
        <select x-model="perPage" @change="gotoPage(1)" class="w-20 bg-page-bg border border-input-border text-text-color text-sm rounded-lg focus:ring-primary focus:border-primary block p-2 outline-none cursor-pointer">
            <option value="1">1</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="flex items-center gap-1">
        <button @click="gotoPage(currentPage - 1)" :disabled="currentPage === 1" class="h-8 w-8 flex items-center justify-center text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-text-color border-input-border transition-colors">&laquo;</button>
        
        <template x-for="(page, index) in visiblePages" :key="index">
            <div class="contents">
                <button x-show="page !== '...'" @click="gotoPage(page)" x-text="page" :class="currentPage === page ? 'bg-primary text-white border-primary' : 'text-text-color hover:bg-gray-100 border-input-border'" class="h-8 min-w-[32px] px-2 flex items-center justify-center text-sm border rounded transition-colors duration-200"></button>
                <span x-show="page === '...'" class="h-8 w-8 flex items-center justify-center text-secondary select-none">...</span>
            </div>
        </template>

        <button @click="gotoPage(currentPage + 1)" :disabled="currentPage === pagination.last_page" class="h-8 w-8 flex items-center justify-center text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-text-color border-input-border transition-colors">&raquo;</button>
    </div>
</div>