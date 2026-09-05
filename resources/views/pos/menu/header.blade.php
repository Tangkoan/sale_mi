{{-- ALPINE DATA FOR HEADER --}}
<div x-data="headerController()" x-init="init()" class="contents">

    {{-- MAIN HEADER BAR (Sticky Top) --}}
    <div class="z-30 shrink-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg border-b border-gray-100 dark:border-gray-800 sticky top-0 transition-colors duration-300">
        
        {{-- =============================================== --}}
        {{-- ROW 1: IDENTITY & SYSTEM (Clean Mobile UI) --}}
        {{-- =============================================== --}}
        <div class="px-4 py-3 flex items-center justify-between gap-4">
            
            {{-- 1. LEFT: Back Button & Table Info --}}
            <div class="flex items-center gap-3 overflow-hidden flex-1 min-w-0">
                <a href="{{ route('pos.tables') }}" class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition flex items-center justify-center active:scale-95 border border-gray-100 dark:border-gray-700">
                    <i class="ri-arrow-left-s-line text-2xl"></i>
                </a>
                
                <div class="flex flex-col min-w-0">
                    <h2 class="text-[16px] font-black text-gray-900 dark:text-white truncate leading-tight">{{ $table->name ?? __('messages.unknown') }}</h2>
                    <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 mt-0.5 truncate">
                        #{{ $currentOrder ? $currentOrder->invoice_number : __('messages.new_order') }}
                    </span>
                </div>
            </div>

            {{-- 2. RIGHT: Unified Menu (Profile + Settings) --}}
            <div class="flex items-center shrink-0">
                
                {{-- Dropdown Menu (រួមបញ្ចូល Profile, Lang, Theme) --}}
                <div x-data="{ open: false }" class="relative">
                    {{-- Avatar Button --}}
                    <button @click="open = !open" @click.away="open = false" class="w-10 h-10 rounded-full overflow-hidden shadow-sm active:scale-95 transition-transform border border-gray-200 dark:border-gray-700">
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'VA' }}&background=0D8ABC&color=fff&size=64&bold=true" class="w-full h-full object-cover">
                    </button>
                    
                    {{-- Dropdown Content --}}
                    <div x-show="open" x-cloak 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 scale-95" 
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 py-2 z-50 transform origin-top-right">
                        
                        {{-- User Info --}}
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 mb-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">គណនី</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-white truncate">{{ Auth::user()->name ?? 'Staff' }}</p>
                        </div>

                        {{-- Theme Toggle --}}
                        <div class="px-4 py-2.5 flex items-center justify-between"
                             x-data="{ darkMode: localStorage.getItem('theme_mode') === 'dark',
                                       toggle() { this.darkMode = !this.darkMode; localStorage.setItem('theme_mode', this.darkMode ? 'dark' : 'light'); if (this.darkMode) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark'); },
                                       init() { if (this.darkMode) document.documentElement.classList.add('dark'); } }">
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 font-medium"><i class="ri-moon-line mr-2"></i> មុខងារងងឹត</span>
                            <button @click="toggle()" class="w-10 h-6 bg-gray-200 dark:bg-gray-700 rounded-full relative transition-colors duration-200" :class="darkMode ? 'bg-blue-500' : ''">
                                <span class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform duration-200" :class="darkMode ? 'translate-x-4' : ''"></span>
                            </button>
                        </div>

                        {{-- Language Selection --}}
                        <div class="px-4 py-2.5 flex items-center justify-between border-b border-gray-100 dark:border-gray-700 mb-1">
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 font-medium"><i class="ri-global-line mr-2"></i> ភាសា</span>
                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-900 p-1 rounded-lg">
                                <a href="{{ route('switch.language', 'km') }}" class="w-7 h-5 rounded overflow-hidden {{ App::getLocale() == 'km' ? 'ring-2 ring-primary' : 'opacity-50' }}"><img src="https://flagcdn.com/w40/kh.png" class="w-full h-full object-cover"></a>
                                <a href="{{ route('switch.language', 'en') }}" class="w-7 h-5 rounded overflow-hidden {{ App::getLocale() == 'en' ? 'ring-2 ring-primary' : 'opacity-50' }}"><img src="https://flagcdn.com/w40/us.png" class="w-full h-full object-cover"></a>
                            </div>
                        </div>

                        @can('dashboard')
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-[13px] text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium">
                            <i class="ri-dashboard-3-line text-primary mr-2 text-[15px]"></i> {{ __('messages.dashboard') }}
                        </a>
                        @endcan
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-[13px] text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 font-bold mt-1">
                                <i class="ri-logout-box-r-line mr-2 text-[15px]"></i> ចាកចេញ (Logout)
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        {{-- =============================================== --}}
        {{-- ROW 2: SEARCH & ADDON (Clean Style) --}}
        {{-- =============================================== --}}
        <div class="px-4 pb-3 flex items-center gap-3">
            
            {{-- Search Bar --}}
            <div class="flex-1 relative">
                <i class="ri-search-2-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[15px]"></i>
                <input type="text" x-model="search" 
                        :placeholder="isAddonMode ? 'ស្វែងរក Addon...' : 'ស្វែងរកមុខម្ហូប...'" 
                        class="w-full pl-10 pr-10 py-2 h-[42px] rounded-full border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-[13px] transition-all">
                
                <button x-show="search.length > 0" @click="search = ''" class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center bg-gray-200 dark:bg-gray-700 rounded-full text-gray-500 hover:text-red-500 transition-colors" x-cloak>
                    <i class="ri-close-line text-[15px]"></i>
                </button>
            </div>

            {{-- Addon Button --}}
            <button @click="toggleAddonMode()" 
                    class="h-[42px] px-4 rounded-full font-bold transition-all flex items-center justify-center gap-2 text-[13px] shrink-0"
                    :class="isAddonMode ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200'">
                <i class="ri-apps-2-line text-[18px]"></i>
                <span x-show="isAddonMode" class="hidden sm:inline">Addon</span>
            </button>
        </div>

    </div>
</div>