<div class="z-30 shrink-0 bg-white/80 dark:bg-gray-800/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0">
    <div class="px-3 py-2 sm:px-4 sm:py-3 flex items-center justify-between gap-3">
        
        {{-- LEFT: Table Info & Back --}}
        <div class="flex items-center gap-2 sm:gap-3 overflow-hidden flex-1 min-w-0">
            {{-- កែសម្រួល៖ ដក sm:px-4 និង sm:justify-start ចេញ ដើម្បីអោយ Icon នៅកណ្ដាលជានិច្ច --}}
            <a href="{{ route('pos.tables') }}" class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-200 transition flex items-center justify-center">
                <i class="ri-arrow-left-line text-lg sm:text-xl"></i>
            </a>
            
            <div class="flex flex-col min-w-0 border-l pl-2 sm:pl-3 border-gray-300 dark:border-gray-600">
                <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white truncate leading-tight">{{ $table->name }}</h2>
                <div class="flex items-center gap-2 text-[10px] sm:text-xs font-medium truncate">
                    @if($currentOrder) 
                        <span class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-md truncate">#{{ $currentOrder->invoice_number }}</span>
                    @else
                        <span class="bg-green-100 text-green-600 px-1.5 py-0.5 rounded-md truncate">{{ __('messages.new_order') }}</span>
                    @endif
                    
                    {{-- Polling Status Indicator --}}
                    <span class="flex items-center gap-1 text-gray-400" x-show="isPolling">
                        <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-green-500"></span>
                        </span>
                        <span class="hidden xs:inline">Live</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- RIGHT: Actions --}}
        <div class="flex items-center gap-2">
            {{-- Quick Addon Button --}}
            <button @click="openQuickAddon()" class="bg-purple-100 text-purple-700 hover:bg-purple-200 w-8 h-8 sm:w-auto sm:h-auto sm:px-4 sm:py-2 rounded-lg sm:rounded-xl font-bold transition flex items-center justify-center gap-2">
                <i class="ri-add-circle-fill text-lg sm:text-xl"></i> 
                <span class="hidden sm:inline">Addon</span>
            </button>

            {{-- Kitchen Link --}}
            {{-- កែសម្រួល៖ ប្តូរពី hidden sm:flex មកជា flex វិញ និងដាក់ Size ឱ្យសមរម្យសម្រាប់ Mobile --}}
            <a href="{{ route('pos.kitchen.view') }}" target="_blank" class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-orange-100 text-orange-600 hover:bg-orange-200 transition flex-shrink-0" title="Kitchen Screen">
                <i class="ri-fire-line text-lg sm:text-xl"></i>
            </a>

            {{-- Search Trigger --}}
            <button @click="isSearchOpen = !isSearchOpen" 
                    class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                    :class="isSearchOpen ? 'bg-primary text-white border-primary ring-2 ring-primary/30' : 'bg-white dark:bg-gray-800'">
                <i class="ri-search-line text-lg sm:text-xl" :class="isSearchOpen ? 'text-white' : ''"></i>
            </button>
        </div>
    </div>

    {{-- Search Bar --}}
    <div x-show="isSearchOpen" x-transition class="px-3 sm:px-4 pb-2 sm:pb-3">
        <div class="relative">
            <i class="ri-search-line absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" x-model="search" placeholder="Search menu..." class="w-full pl-9 sm:pl-11 pr-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl border-0 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary outline-none transition-all shadow-inner text-sm">
        </div>
    </div>

    {{-- Category Tabs --}}
    <div class="px-3 sm:px-4 pb-2 sm:pb-3 overflow-x-auto no-scrollbar flex gap-2 sm:gap-3 snap-x">
        <button @click="activeCategory = 'all'" class="snap-start flex-shrink-0 px-3 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-bold transition-all duration-300 border shadow-sm select-none" :class="activeCategory === 'all' ? 'bg-primary border-primary text-white shadow-primary/30 scale-105' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'">All</button>
        @foreach($categories as $cat)
            <button @click="activeCategory = {{ $cat->id }}" class="snap-start flex-shrink-0 px-3 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-bold transition-all duration-300 border shadow-sm select-none" :class="activeCategory === {{ $cat->id }} ? 'bg-primary border-primary text-white shadow-primary/30 scale-105' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'">{{ $cat->name }}</button>
        @endforeach
    </div>
</div>