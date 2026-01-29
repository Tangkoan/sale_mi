<div class="px-6 py-5 flex justify-between items-center bg-white/80 dark:bg-gray-800/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 z-20 shrink-0">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-gray-800 dark:text-white mb-1">{{ __('messages.select_table') }}</h1>
        <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('messages.please_select_table_to_order') }}</p>
    </div>
    <div class="flex gap-2 sm:gap-3">
        <div class="flex items-center gap-2 bg-white dark:bg-gray-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-full border border-gray-200 dark:border-gray-600 shadow-sm">
            <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[10px] sm:text-xs font-bold text-gray-700 dark:text-gray-200 uppercase">{{ __('messages.available') }}</span>
        </div>
        <div class="flex items-center gap-2 bg-white dark:bg-gray-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-full border border-gray-200 dark:border-gray-600 shadow-sm">
            <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-rose-500"></span>
            <span class="text-[10px] sm:text-xs font-bold text-gray-700 dark:text-gray-200 uppercase">{{ __('messages.busy') }}</span>
        </div>
    </div>
</div>