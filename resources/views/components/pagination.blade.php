@props(['total' => 0])

<div class="px-6 py-4 border-t border-border-color flex justify-between items-center" 
     x-show="pagination.total > 0 && perPage !== 'all'">
    
    <div class="flex items-center gap-2">
        <span class="text-sm text-secondary whitespace-nowrap">Show:</span>
        {{-- x-model="perPage" ត្រូវតែមាននៅក្នុង parent x-data --}}
        <select {{ $attributes->merge(['class' => 'w-24 bg-page-bg border border-input-border text-text-color text-sm rounded-lg focus:ring-primary focus:border-primary block p-2 outline-none cursor-pointer']) }}>
            <option value="1">1</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="all">All</option>
        </select>
    </div>

    <div class="flex gap-2">
        <button @click="changePage(pagination.prev_page_url)" :disabled="!pagination.prev_page_url" class="px-3 py-1 rounded border border-input-border text-text-color disabled:opacity-50 hover:bg-input-bg transition">Prev</button>
        <button @click="changePage(pagination.next_page_url)" :disabled="!pagination.next_page_url" class="px-3 py-1 rounded border border-input-border text-text-color disabled:opacity-50 hover:bg-input-bg transition">Next</button>
    </div>
</div>