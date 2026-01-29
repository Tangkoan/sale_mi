<div x-show="cart.length > 0" x-transition class="absolute bottom-4 left-3 right-3 sm:bottom-6 sm:left-4 sm:right-4 md:left-1/2 md:-translate-x-1/2 md:w-96 z-40">
    <button @click="isCartOpen = true" class="w-full bg-gray-900/95 dark:bg-primary/95 backdrop-blur-md text-white p-1.5 sm:p-2 pr-3 sm:pr-4 pl-1.5 sm:pl-2 rounded-[16px] sm:rounded-[20px] shadow-2xl border border-white/10 flex items-center justify-between group hover:scale-[1.01] sm:hover:scale-[1.02] transition-transform duration-200">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="bg-white text-gray-900 w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center font-black text-base sm:text-lg shadow-sm" x-text="cartTotalQty"></div>
            <div class="flex flex-col items-start">
                <span class="text-[10px] sm:text-xs text-gray-300 uppercase tracking-wider font-semibold">Total</span>
                <span class="font-bold text-lg sm:text-xl" x-text="'$' + cartTotalPrice.toFixed(2)"></span>
            </div>
        </div>
        <div class="flex items-center gap-1 sm:gap-2 pr-1 sm:pr-2">
            <span class="font-bold text-xs sm:text-sm">View Cart</span>
            <i class="ri-arrow-right-line bg-white/20 rounded-full p-1 transition-transform group-hover:translate-x-1 text-xs sm:text-base"></i>
        </div>
    </button>
</div>