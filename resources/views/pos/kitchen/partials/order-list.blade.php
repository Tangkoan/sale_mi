{{-- =========================================== --}}
{{-- ORDERS GRID AREA                            --}}
{{-- =========================================== --}}
<div class="flex-1 overflow-y-auto p-2 sm:p-4 custom-scrollbar">
    <div x-show="isLoading && orders.length === 0" class="flex h-full items-center justify-center">
        <i class="ri-loader-4-line animate-spin text-3xl sm:text-4xl text-gray-600"></i>
    </div>

    <div x-show="!isLoading && orders.length === 0" class="flex flex-col h-full items-center justify-center text-gray-600 opacity-50" x-cloak>
        <i class="ri-checkbox-circle-line text-5xl sm:text-6xl mb-3 sm:mb-4"></i>
        <p class="text-xl sm:text-2xl font-bold">All Orders Completed</p>
        <p class="text-xs sm:text-sm">Waiting for new orders...</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4" x-show="orders.length > 0" x-cloak>
        <template x-for="order in orders" :key="order.id">
            <div class="flex flex-col rounded-lg sm:rounded-xl shadow-lg overflow-hidden h-fit transition-all duration-300 hover:scale-[1.01] border relative group"
                    :class="getBgClass(order.created_at, currentTimeTrigger)"> 
                
                {{-- Header --}}
                <div class="p-2 sm:p-3 flex justify-between items-start border-b border-white/10">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-white leading-none" x-text="order.table ? order.table.name : 'Unknown'"></h2>
                        <p class="text-[10px] sm:text-xs text-white/70 mt-1 font-mono" x-text="'#' + order.invoice_number"></p>
                    </div>
                    <div class="text-right">
                        <span class="text-lg sm:text-xl font-mono font-bold" 
                                :class="getBgClass(order.created_at, currentTimeTrigger).includes('red') ? 'text-white animate-pulse' : 'text-green-300'"
                                x-text="formatTimeAgo(order.created_at, currentTimeTrigger)"></span>
                    </div>
                </div>

                {{-- Items --}}
                <div class="p-2 space-y-1.5 sm:space-y-2 flex-1">
                    <template x-for="item in order.items" :key="item.id">
                        <div class="flex flex-col p-1.5 sm:p-2 rounded-md sm:rounded-lg transition-colors border border-transparent"
                                :class="item.status === 'ready' ? 'bg-green-900/30 border-green-500/30 opacity-60' : 'bg-black/20 hover:bg-black/30'">
                            <div class="flex justify-between items-start gap-2">
                                <span class="bg-white text-gray-900 font-black text-base sm:text-lg px-1.5 sm:px-2 rounded min-w-[1.8rem] sm:min-w-[2rem] text-center shrink-0 h-6 sm:h-7 leading-6 sm:leading-7" x-text="item.quantity"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-base sm:text-lg font-bold leading-tight" 
                                        :class="item.status === 'ready' ? 'line-through text-gray-400' : 'text-white'"
                                        x-text="item.product ? item.product.name : 'Deleted Item'"></p>
                                    <template x-if="item.addons && item.addons.length > 0">
                                        <div class="mt-0.5 sm:mt-1 pl-2 border-l-2 border-gray-500/50">
                                            <template x-for="ad in item.addons">
                                                <p class="text-xs sm:text-sm text-gray-300">
                                                    + <span x-text="ad.addon ? ad.addon.name : 'Unknown'"></span> 
                                                    <span class="text-[10px] sm:text-xs font-mono text-gray-400" x-text="'x' + (ad.quantity || 1)"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="item.note">
                                        <div class="mt-1 sm:mt-2 bg-red-500/20 text-red-200 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded text-xs sm:text-sm font-bold border border-red-500/30 w-fit">
                                            <i class="ri-message-2-fill"></i> <span x-text="item.note"></span>
                                        </div>
                                    </template>
                                </div>
                                <button @click="markItemReady(item.id)" x-show="item.status !== 'ready'" class="h-8 w-8 sm:h-10 sm:w-10 bg-gray-600 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-colors shrink-0 shadow-sm">
                                    <i class="ri-check-line text-lg sm:text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer with Custom Modal Trigger --}}
                <div class="p-2 sm:p-3 border-t border-white/10 bg-black/10">
                    <button @click="openConfirmModal(order.id)" 
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 sm:py-3 rounded-lg shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2 text-sm sm:text-base">
                        <span>Done All</span>
                        <i class="ri-check-double-line"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>