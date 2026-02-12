<div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-4 md:mb-6">
    {{-- Total Sales --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-4">
        <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg md:text-xl"><i class="ri-money-dollar-circle-fill"></i></div>
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ __('messages.total_sales') }}</p>
            <h3 class="text-base md:text-xl font-black text-gray-800 break-words" id="summaryTotalSales">...</h3>
        </div>
    </div>
    
    {{-- Transactions --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-4">
        <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-lg md:text-xl"><i class="ri-file-list-3-fill"></i></div>
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ __('messages.transactions') }}</p>
            <h3 class="text-base md:text-xl font-black text-gray-800" id="summaryTotalOrders">0</h3>
        </div>
    </div>
    
    {{-- Cash --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-4">
        <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-lg md:text-xl"><i class="ri-wallet-3-fill"></i></div>
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ __('messages.cash') }}</p>
            <h3 class="text-base md:text-xl font-black text-gray-800 break-words" id="summaryCash">...</h3>
        </div>
    </div>
    
    {{-- QR --}}
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-4">
        <div class="w-8 h-8 md:w-12 md:h-12 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-lg md:text-xl"><i class="ri-qr-code-line"></i></div>
        <div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ __('messages.qr_bank') }}</p>
            <h3 class="text-base md:text-xl font-black text-gray-800 break-words" id="summaryQR">...</h3>
        </div>
    </div>
</div>