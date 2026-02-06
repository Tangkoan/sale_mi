{{-- ALPINE DATA FOR HEADER --}}
<div x-data="headerController()" x-init="init()" class="contents">

    {{-- MAIN HEADER BAR (Sticky Top) --}}
    <div class="z-30 shrink-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 transition-colors duration-300">
        
        {{-- =============================================== --}}
        {{-- ROW 1: IDENTITY & SYSTEM (Table Info + Profile/Lang/Theme) --}}
        {{-- =============================================== --}}
        <div class="px-3 py-2 sm:px-4 sm:py-3 flex items-center justify-between gap-3">
            
            {{-- 1. LEFT: Table Info --}}
            <div class="flex items-center gap-2 sm:gap-3 overflow-hidden flex-1 min-w-0">
                <a href="{{ route('pos.tables') }}" class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold hover:bg-gray-200 dark:hover:bg-gray-700 transition flex items-center justify-center">
                    <i class="ri-arrow-left-line text-lg sm:text-xl"></i>
                </a>
                <div class="flex flex-col min-w-0 border-l pl-2 sm:pl-3 border-gray-300 dark:border-gray-600">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white truncate leading-tight">{{ $table->name ?? 'Unknown' }}</h2>
                    <div class="flex items-center gap-2 text-[10px] sm:text-xs font-medium truncate">
                        <span class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-md truncate">#{{ $currentOrder ? $currentOrder->invoice_number : 'New' }}</span>
                        <span class="flex items-center gap-1 text-gray-400" x-show="isFetchingRate" style="display: none;">
                            <span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. RIGHT: System Actions --}}
            <div class="flex items-center gap-2">
                
                {{-- DESKTOP ONLY BUTTONS (Show here only on large screens) --}}
                {{-- កន្លែងនេះកែដាក់កូដផ្ទាល់ មិនប្រើ @include ទៀតទេ --}}
                <div class="hidden md:flex items-center gap-2">
                    {{-- Exchange --}}
                    <button @click="openExchangeModal()" class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800 flex items-center justify-center transition relative group">
                        <i class="ri-exchange-dollar-line text-lg"></i>
                        {{-- Tooltip --}}
                        <div class="absolute top-full mt-2 right-0 bg-gray-900 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none z-50 whitespace-nowrap">
                            1$ = <span x-text="formatNumber(exchangeRate)"></span>៛
                        </div>
                    </button>

                    {{-- Addon --}}
                    <button @click="toggleAddonMode()" 
                            class="h-9 px-3 rounded-full font-bold transition flex items-center justify-center gap-2 text-xs"
                            :class="isAddonMode ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 border border-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800'">
                        <i class="ri-apps-2-line text-lg"></i> <span>Addon</span>
                    </button>

                    {{-- Kitchen --}}
                    <a href="{{ route('pos.kitchen.view') }}" target="_blank" class="w-9 h-9 rounded-full bg-orange-50 text-orange-600 hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400 border border-orange-100 dark:border-orange-800 flex items-center justify-center">
                        <i class="ri-fire-line text-lg"></i>
                    </a>

                    {{-- Search --}}
                    <button @click="toggleSearch()" class="w-9 h-9 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700" :class="isSearchOpen ? 'bg-primary text-white border-primary' : ''">
                        <i class="ri-search-line text-lg"></i>
                    </button>

                    {{-- Divider --}}
                    <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>
                </div>
                {{-- END DESKTOP BUTTONS --}}

                {{-- Language Switcher --}}
                <div x-data="{ languageOpen: false }" class="relative">
                    <button @click="languageOpen = !languageOpen" class="w-9 h-9 rounded-full flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border border-transparent dark:border-gray-700">
                        @if(App::getLocale() == 'km')
                            <img src="https://flagcdn.com/w40/kh.png" alt="Khmer" class="w-5 h-auto rounded-sm shadow-sm object-cover">
                        @else
                            <img src="https://flagcdn.com/w40/us.png" alt="English" class="w-5 h-auto rounded-sm shadow-sm object-cover">
                        @endif
                    </button>
                    {{-- Dropdown Language --}}
                    <div x-show="languageOpen" @click.outside="languageOpen = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-xl py-1 border border-gray-100 dark:border-gray-700 z-50">
                        <a href="{{ route('switch.language', 'km') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ App::getLocale() == 'km' ? 'bg-gray-50 dark:bg-gray-700/50 text-blue-600 font-semibold' : '' }}">
                            <img src="https://flagcdn.com/w40/kh.png" class="w-5 h-auto rounded-sm"> <span>ភាសាខ្មែរ</span>
                        </a>
                        <a href="{{ route('switch.language', 'en') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ App::getLocale() == 'en' ? 'bg-gray-50 dark:bg-gray-700/50 text-blue-600 font-semibold' : '' }}">
                            <img src="https://flagcdn.com/w40/us.png" class="w-5 h-auto rounded-sm"> <span>English</span>
                        </a>
                    </div>
                </div>

                {{-- Theme Toggle --}}
                <button x-data="{ 
                            darkMode: localStorage.getItem('theme_mode') === 'dark',
                            toggle() {
                                this.darkMode = !this.darkMode;
                                localStorage.setItem('theme_mode', this.darkMode ? 'dark' : 'light');
                                if (this.darkMode) document.documentElement.classList.add('dark');
                                else document.documentElement.classList.remove('dark');
                            },
                            init() { if (this.darkMode) document.documentElement.classList.add('dark'); }
                        }" 
                        @click="toggle()" 
                        class="w-9 h-9 rounded-full flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <i class="text-lg" :class="darkMode ? 'ri-moon-fill text-yellow-400' : 'ri-sun-fill text-gray-500'"></i>
                </button>

                {{-- Profile --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="w-9 h-9 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700">
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Staff' }}&background=0D8ABC&color=fff&size=64" class="w-full h-full object-cover">
                    </button>
                    {{-- Dropdown Profile --}}
                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-50">
                        @can('dashboard')
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                            <i class="ri-dashboard-3-line text-primary"></i> Dashboard
                        </a>
                        @endcan
                        <div class="h-px bg-gray-100 dark:bg-gray-700 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                <i class="ri-logout-box-r-line"></i> {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- =============================================== --}}
        {{-- ROW 2: OPERATIONAL BUTTONS (MOBILE ONLY) --}}
        {{-- =============================================== --}}
        <div class="md:hidden px-3 pb-2 flex items-center justify-between gap-2">
            
            {{-- Group Left --}}
            <div class="flex items-center gap-2">
                {{-- Exchange Rate --}}
                <button @click="openExchangeModal()" class="flex items-center justify-center w-10 h-9 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800 relative">
                    <i class="ri-exchange-dollar-line text-lg"></i>
                    <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                </button>

                {{-- Addon Toggle --}}
                <button @click="toggleAddonMode()" 
                        class="h-9 px-3 rounded-lg font-bold transition flex items-center justify-center gap-1.5 text-xs border"
                        :class="isAddonMode ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800'">
                    <i class="ri-apps-2-line text-lg"></i> 
                    <span>Addon</span>
                </button>

                 {{-- Kitchen View --}}
                 <a href="{{ route('pos.kitchen.view') }}" target="_blank" 
                    class="flex items-center justify-center w-10 h-9 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400 border border-orange-100 dark:border-orange-800">
                     <i class="ri-fire-line text-lg"></i>
                 </a>
            </div>

            {{-- Group Right (Search) --}}
            <button @click="toggleSearch()" class="flex-1 max-w-[120px] h-9 rounded-lg border flex items-center justify-center gap-2 transition-all" 
                    :class="isSearchOpen ? 'bg-primary text-white border-primary shadow-md' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700'">
                <i class="ri-search-line text-lg"></i>
                <span class="text-xs font-bold">Search</span>
            </button>
        </div>

        {{-- =============================================== --}}
        {{-- SEARCH BAR (Shown when toggled) --}}
        {{-- =============================================== --}}
        <div x-show="isSearchOpen" x-transition class="px-3 sm:px-4 pb-2 sm:pb-3">
            <div class="relative">
                <i class="ri-search-line absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="search" 
                       :placeholder="isAddonMode ? 'Search add-ons...' : 'Search menu...'" 
                       class="w-full pl-9 pr-4 py-2 rounded-lg border-0 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary outline-none text-sm shadow-inner transition-all">
            </div>
        </div>

        {{-- CATEGORY TABS --}}
        @if(isset($categories))
        <div class="px-3 sm:px-4 pb-2 sm:pb-3 overflow-x-auto no-scrollbar flex gap-2 sm:gap-3 snap-x">
            <button @click="setCategory('all')" 
                    class="snap-start flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-bold transition-all border shadow-sm select-none" 
                    :class="activeCategory === 'all' ? 'bg-primary border-primary text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50'">
                All
            </button>
            @foreach($categories as $cat)
                <button @click="setCategory({{ $cat->id }})" 
                        class="snap-start flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-bold transition-all border shadow-sm select-none" 
                        :class="activeCategory === {{ $cat->id }} ? 'bg-primary border-primary text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50'">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
        @endif
        
        {{-- Addon Mode Indicator --}}
        <div x-show="isAddonMode" x-transition class="bg-purple-600 text-white text-[10px] text-center font-bold py-1 tracking-widest uppercase shadow-inner">
            <i class="ri-flashlight-fill text-yellow-300"></i> ADD-ON MODE ACTIVE
        </div>
    </div>

    {{-- MODAL: Exchange Rate --}}
    <div x-show="isExchangeModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak x-transition.opacity>
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all" @click.away="isExchangeModalOpen = false" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2"><i class="ri-coins-line text-emerald-500"></i> Exchange Rate</h3>
                <button @click="isExchangeModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"><i class="ri-close-line text-lg"></i></button>
            </div>
            <div class="p-6 space-y-5">
                <div class="text-center p-5 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase mb-2">Current System Rate</p>
                    <div class="text-3xl sm:text-4xl font-black text-gray-800 dark:text-white"><span class="text-lg text-gray-400 font-medium align-top mt-1 inline-block">$1 =</span> <span x-text="formatNumber(exchangeRate)"></span> <span class="text-base text-gray-500 font-bold">KHR</span></div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Set New Rate</label>
                    <div class="relative"><div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><span class="text-gray-400 font-bold">៛</span></div><input type="number" x-model="tempExchangeRate" class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-bold text-xl outline-none focus:ring-4 focus:ring-emerald-500/20"></div>
                </div>
                <button @click="fetchRateFromApi()" class="w-full py-3 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 hover:text-primary transition flex items-center justify-center gap-2 text-sm font-semibold" :disabled="isFetchingRate">
                    <i class="ri-download-cloud-2-line text-lg" :class="isFetchingRate ? 'animate-spin' : ''"></i> <span x-text="isFetchingRate ? 'Fetching...' : 'Auto Fetch from NBC API'"></span>
                </button>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 flex gap-3">
                <button @click="isExchangeModalOpen = false" class="flex-1 py-3 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-100 transition text-sm">Cancel</button>
                <button @click="saveExchangeRate()" class="flex-1 py-3 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-500 shadow-lg active:scale-95 transition text-sm">Save Change</button>
            </div>
        </div>
    </div>
</div>