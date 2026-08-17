<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 mb-4 md:mb-6">
    
    {{-- Total Sales (ការលក់សរុប) --}}
    <div class="col-span-2 md:col-span-1 bg-card-bg p-3 md:p-4 rounded-xl shadow-custom border border-bor-color flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-4">
        <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xl md:text-2xl shrink-0">
            <i class="ri-bar-chart-box-fill"></i>
        </div>
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ __('messages.total_sales') }}</p>
            <h3 class="text-base md:text-xl font-black text-sidebar-text break-words" id="summaryTotalSales">...</h3>
        </div>
    </div>
    
    {{-- Transactions (ប្រតិបត្តិការ) --}}
    <div class="bg-card-bg p-3 md:p-4 rounded-xl shadow-custom border border-bor-color flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-4">
        <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xl md:text-2xl shrink-0">
            <i class="ri-file-list-3-fill"></i>
        </div>
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ __('messages.transactions') }}</p>
            <h3 class="text-base md:text-xl font-black text-sidebar-text" id="summaryTotalOrders">0</h3>
        </div>
    </div>
    
    {{-- Cash (សាច់ប្រាក់) --}}
    <div class="bg-card-bg p-3 md:p-4 rounded-xl shadow-custom border border-bor-color flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-4">
        <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xl md:text-2xl shrink-0">
            <i class="ri-wallet-3-fill"></i>
        </div>
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ __('messages.cash') }}</p>
            <h3 class="text-base md:text-xl font-black text-sidebar-text break-words" id="summaryCash">...</h3>
        </div>
    </div>

</div>