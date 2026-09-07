<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-4 gap-4">
    <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
        <i class="ri-history-line"></i>
        ប្រវត្តិការកម្ម៉ង់ (Order History)
    </h1>
    {{-- គ្មានប៊ូតុង Add នៅទីនេះទេ --}}
</div>

<div class="flex flex-col md:flex-row gap-3 mb-4 sm:mb-6">
    <div class="flex flex-wrap md:flex-nowrap items-center gap-2 w-full flex-1">
        
        {{-- Date Filter --}}
        <div class="w-[calc(50%-4px)] md:w-48 flex-grow-0">
            <input type="date" x-model="filterDate" @change="fetchOrders()" class="w-full px-4 py-2 rounded-xl border border-input-border bg-card-bg text-text-color text-xs sm:text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20">
        </div>

        {{-- Search (Invoice Number) --}}
        <div class="relative flex-1 w-full min-w-[200px]">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary"><i class="ri-search-line"></i></span>
            <input type="text" x-model="search" @keyup.debounce.500ms="fetchOrders()" class="w-full pl-9 pr-3 py-2 rounded-xl border border-input-border bg-card-bg text-text-color text-xs sm:text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20" placeholder="ស្វែងរកលេខវិក្កយបត្រ...">
        </div>

        {{-- Column Button --}}
        <div class="relative shrink-0" x-data="{ openCol: false }">
            <button @click="openCol = !openCol" @click.outside="openCol = false" class="h-[42px] w-full md:w-auto px-3 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm flex items-center justify-center">
                <i class="ri-layout-column-line text-lg"></i> 
                <span class="inline ml-2 md:inline">ជួរឈរ</span>
            </button>
            <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                <div class="space-y-1">
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                        <input type="checkbox" x-model="showCols.invoice" class="rounded text-primary border-input-border"> <span class="text-sm text-text-color">លេខវិក្កយបត្រ</span>
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                        <input type="checkbox" x-model="showCols.table" class="rounded text-primary border-input-border"> <span class="text-sm text-text-color">តុបញ្ជាទិញ</span>
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                        <input type="checkbox" x-model="showCols.amount" class="rounded text-primary border-input-border"> <span class="text-sm text-text-color">ទឹកប្រាក់សរុប</span>
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                        <input type="checkbox" x-model="showCols.method" class="rounded text-primary border-input-border"> <span class="text-sm text-text-color">បង់ប្រាក់តាម</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>