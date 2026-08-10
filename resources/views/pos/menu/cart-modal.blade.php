<div x-show="isCartOpen" class="fixed inset-0 z-[100] flex items-end justify-center" style="display: none;" x-cloak>
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity duration-300" @click="isCartOpen = false"></div>
    
    {{-- Responsive Cart Modal --}}
    <div class="relative w-full bg-white dark:bg-gray-800 rounded-t-2xl sm:rounded-t-[32px] shadow-2xl flex flex-col max-h-[90vh] sm:max-h-[85vh] transition-transform duration-300" 
            x-show="isCartOpen" 
            x-transition:enter="translate-y-full" x-transition:enter-end="translate-y-0" 
            x-transition:leave="translate-y-full">
        
        {{-- Handle Bar --}}
        <div class="w-full flex justify-center pt-3 sm:pt-4 pb-1 sm:pb-2" @click="isCartOpen = false">
            <div class="w-12 h-1 sm:w-16 sm:h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full opacity-50"></div>
        </div>
        
        {{-- Header --}}
        <div class="px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-xl sm:text-2xl font-black text-gray-800 dark:text-white">Your Cart</h2>
            <button @click="cart = []; isCartOpen = false" class="text-red-500 font-bold bg-red-50 px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg sm:rounded-xl text-xs sm:text-sm hover:bg-red-100 transition">Clear</button>
        </div>

        {{-- Cart Items List --}}
        <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-3 sm:space-y-4 custom-scrollbar bg-gray-50 dark:bg-gray-900">
            <template x-for="(item, index) in cart" :key="index">
                <div class="bg-white dark:bg-gray-800 p-3 sm:p-4 rounded-xl sm:rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col gap-3 group relative overflow-hidden">
                    
                    {{-- ផ្នែកខាងលើ៖ Main Product Info & Controls --}}
                    <div class="flex gap-3 sm:gap-4">
                        {{-- QTY Controller for Main Item --}}
                        <div class="flex flex-col items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg sm:rounded-xl w-8 sm:w-10 py-1 shrink-0 h-20 sm:h-24">
                            <button @click="updateCartQty(index, 1)" class="w-full flex-1 text-gray-600 hover:text-primary"><i class="ri-add-line text-xs sm:text-sm"></i></button>
                            <span class="font-bold text-xs sm:text-sm text-gray-800 dark:text-white" x-text="item.qty"></span>
                            <button @click="updateCartQty(index, -1)" class="w-full flex-1 text-gray-600 hover:text-red-500"><i class="ri-subtract-line text-xs sm:text-sm"></i></button>
                        </div>

                        {{-- Item Details --}}
                        <div class="flex-1 min-w-0 py-0.5 sm:py-1">
                            <div class="flex justify-between items-start gap-2">
                                <h4 class="font-bold text-gray-800 dark:text-white leading-tight text-sm sm:text-base" x-text="item.name"></h4>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-primary whitespace-nowrap text-sm sm:text-base" x-text="'$' + (item.base_price * item.qty).toFixed(2)"></span>
                                    
                                    {{-- ប៊ូតុងលុប Product ទាំងមូល --}}
                                    <button @click="removeFromCart(index)" class="text-gray-400 hover:text-red-500 transition-colors" title="Remove Item">
                                        <i class="ri-delete-bin-line text-lg"></i>
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Note Section --}}
                            <template x-if="item.note">
                                <div class="flex items-start gap-1 mt-1.5 sm:mt-2 bg-orange-50 p-1 rounded-lg w-fit">
                                    <i class="ri-sticky-note-line text-orange-400 text-[10px] mt-0.5"></i>
                                    <p class="text-[10px] text-gray-500 italic line-clamp-1" x-text="item.note"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ផ្នែកខាងក្រោម៖ Addons List (UI ថ្មី៖ បង្ហាញជាបញ្ជី និងមានប៊ូតុងគ្រប់គ្រង) --}}
                    <template x-if="item.addons && item.addons.length > 0">
                        <div class="border-t border-dashed border-gray-200 dark:border-gray-700 pt-2 mt-1 space-y-2">
                            <template x-for="(ad, adIndex) in item.addons" :key="ad.id">
                                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2 text-sm">
                                    
                                    {{-- Addon Name & Price --}}
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        <i class="ri-add-circle-fill text-blue-500 text-xs shrink-0"></i>
                                        <span class="text-gray-700 dark:text-gray-300 font-medium truncate" x-text="ad.name"></span>
                                        <span class="text-xs text-gray-500 shrink-0" x-text="'(+$' + parseFloat(ad.price).toFixed(2) + ')'"></span>
                                    </div>

                                    {{-- Addon Controls (+ - Delete) --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        {{-- Qty Control --}}
                                        <div class="flex items-center bg-white dark:bg-gray-600 rounded-md border border-gray-200 dark:border-gray-600 h-6 sm:h-7">
                                            <button @click="updateCartAddonQty(index, adIndex, -1)" class="px-1.5 sm:px-2 text-gray-500 hover:text-red-500 border-r border-gray-200 dark:border-gray-600 h-full flex items-center">-</button>
                                            <span class="px-1.5 sm:px-2 font-bold text-xs text-gray-800 dark:text-white" x-text="ad.qty"></span>
                                            <button @click="updateCartAddonQty(index, adIndex, 1)" class="px-1.5 sm:px-2 text-gray-500 hover:text-blue-500 border-l border-gray-200 dark:border-gray-600 h-full flex items-center">+</button>
                                        </div>
                                        
                                        {{-- Delete Individual Addon --}}
                                        <button @click="removeAddonFromCart(index, adIndex)" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 p-1 rounded transition-colors" title="Remove Addon">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Subtotal for this Row --}}
                    <div class="flex justify-end border-t border-gray-100 dark:border-gray-700 pt-2 mt-1">
                        <span class="text-xs text-gray-500 mr-2">Subtotal:</span>
                        <span class="text-sm font-black text-gray-900 dark:text-white" x-text="'$' + item.total_price_calculated.toFixed(2)"></span>
                    </div>

                </div>
            </template>
            
            {{-- Empty State --}}
            <div x-show="cart.length === 0" class="h-32 sm:h-40 flex flex-col items-center justify-center text-gray-400 opacity-60">
                <i class="ri-shopping-cart-2-line text-4xl sm:text-5xl mb-2 sm:mb-3"></i>
                <p class="text-xs sm:text-sm font-medium">Cart is empty</p>
            </div>
        </div>

        {{-- Footer Total & Button --}}
        <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 pb-6 sm:pb-8 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] z-20">
            <div class="flex justify-between items-center mb-4 sm:mb-6">
                <span class="text-gray-500 font-bold text-base sm:text-lg">Total Amount</span>
                <span class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white" x-text="'$' + cartTotalPrice.toFixed(2)"></span>
            </div>
            
            <button @click="submitOrder" 
                    :disabled="isSubmitting || cart.length === 0" 
                    class="w-full bg-gray-900 dark:bg-primary text-white py-3 sm:py-4 rounded-xl sm:rounded-2xl font-bold text-lg sm:text-xl shadow-lg flex justify-center items-center gap-2 sm:gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                <i x-show="isSubmitting" class="ri-loader-4-line animate-spin text-xl sm:text-2xl"></i>
                <span x-text="isSubmitting ? 'Processing...' : 'Confirm Order'"></span>
            </button>
        </div>
    </div>
</div>