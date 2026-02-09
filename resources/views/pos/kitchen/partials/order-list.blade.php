<div class="h-full w-full overflow-y-auto p-2 sm:p-4 custom-scrollbar">
    
    <div x-show="isLoading && orders.length === 0" class="flex h-full items-center justify-center">
        <i class="ri-loader-4-line animate-spin text-4xl sm:text-5xl text-gray-500"></i>
    </div>

    <div x-show="!isLoading && orders.length === 0" class="flex flex-col h-full items-center justify-center text-gray-400 dark:text-gray-600 opacity-60" x-cloak>
        <i class="ri-checkbox-circle-line text-6xl sm:text-7xl mb-4"></i>
        <p class="text-2xl sm:text-3xl font-bold">{{ __('messages.all_orders_completed') }}</p>
        <p class="text-sm sm:text-base">{{ __('messages.waiting_new_orders') }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 pb-20" x-show="orders.length > 0" x-cloak>
        <template x-for="order in orders" :key="order.id">
            <div class="flex flex-col rounded-lg sm:rounded-xl shadow-lg overflow-hidden h-fit transition-all duration-300 border relative group"
                 :class="getBgClass(order.created_at, currentTimeTrigger)"> 
                
                {{-- Header --}}
                <div class="p-3 flex justify-between items-start border-b border-white/10 shrink-0 bg-black/10">
                    <div class="min-w-0">
                        <h2 class="text-xl sm:text-2xl font-black text-white leading-none truncate" x-text="order.table ? order.table.name : '{{ __('messages.unknown_table') }}'"></h2>
                        <p class="text-xs text-white/70 mt-1 font-mono truncate" x-text="'#' + order.invoice_number"></p>
                    </div>
                    <div class="text-right shrink-0 ml-2">
                        <span class="text-lg sm:text-xl font-mono font-bold block" 
                                :class="getBgClass(order.created_at, currentTimeTrigger).includes('red') ? 'text-white animate-pulse' : 'text-green-300'"
                                x-text="formatTimeAgo(order.created_at, currentTimeTrigger)"></span>
                    </div>
                </div>

                {{-- Items --}}
                <div class="p-2 space-y-2 overflow-y-auto custom-scrollbar max-h-[50vh] sm:max-h-[400px]">
                    <template x-for="item in order.items" :key="item.id">
                        <div class="flex flex-col p-2 rounded-md transition-colors border border-transparent"
                                :class="item.status === 'ready' ? 'bg-green-900/30 border-green-500/30 opacity-60' : 'bg-black/20 hover:bg-black/30'">
                            
                            <div class="flex justify-between items-start gap-2">
                                <span class="bg-white text-gray-900 font-black text-lg px-2 rounded min-w-[2rem] text-center shrink-0 h-7 leading-7" x-text="item.quantity"></span>
                                
                                <div class="flex-1 min-w-0 break-words">
                                    <p class="text-base sm:text-lg font-bold leading-tight" 
                                        :class="item.status === 'ready' ? 'line-through text-gray-400' : 'text-white'"
                                        x-text="item.product ? item.product.name : '{{ __('messages.deleted_item') }}'"></p>
                                    
                                    <template x-if="item.addons && item.addons.length > 0">
                                        <div class="mt-1 pl-2 border-l-2 border-gray-500/50">
                                            <template x-for="ad in item.addons">
                                                <p class="text-sm text-gray-300">
                                                    + <span x-text="ad.addon ? ad.addon.name : '{{ __('messages.unknown_addon') }}'"></span> 
                                                    <span class="text-xs font-mono text-gray-400" x-text="'x' + (ad.quantity || 1)"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="item.note">
                                        <div class="mt-2 bg-red-500/20 text-red-200 px-2 py-1 rounded text-xs sm:text-sm font-bold border border-red-500/30 w-fit max-w-full break-words">
                                            <i class="ri-message-2-fill"></i> <span x-text="item.note"></span>
                                        </div>
                                    </template>
                                </div>

                                <button @click="markItemReady(item.id)" x-show="item.status !== 'ready'" 
                                    class="h-10 w-10 bg-gray-600 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-colors shrink-0 shadow-sm active:scale-95">
                                    <i class="ri-check-line text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="p-2 sm:p-3 border-t border-white/10 bg-black/10 mt-auto">
                    <button @click="openConfirmModal(order.id)" 
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
                        <span>{{ __('messages.done_all') }}</span>
                        <i class="ri-check-double-line"></i>
                    </button>
                </div>

            </div>
        </template>
    </div>
</div>