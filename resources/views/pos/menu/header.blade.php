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
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white truncate leading-tight">{{ $table->name ?? __('messages.unknown') }}</h2>
                    <div class="flex items-center gap-2 text-[10px] sm:text-xs font-medium truncate">
                        <span class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-md truncate">#{{ $currentOrder ? $currentOrder->invoice_number : __('messages.new_order') }}</span>
                        <span class="flex items-center gap-1 text-gray-400" x-show="isFetchingRate" style="display: none;">
                            <span class="relative flex h-1.5 w-1.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. RIGHT: System Actions --}}
            <div class="flex items-center gap-2">
                
                {{-- DESKTOP ONLY BUTTONS --}}
                <div class="hidden md:flex items-center gap-2">
                    

                    {{-- Addon --}}
                    <button @click="toggleAddonMode()" 
                            class="h-9 px-3 rounded-full font-bold transition flex items-center justify-center gap-2 text-xs"
                            :class="isAddonMode ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 border border-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800'">
                        <i class="ri-apps-2-line text-lg"></i> <span>{{ __('messages.addon') }}</span>
                    </button>

                    {{-- Kitchen --}}
                    @can('pos-kitchen')
                        <a href="{{ route('pos.kitchen.view') }}" target="_blank" class="w-9 h-9 rounded-full bg-orange-50 test-primary hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400 border border-orange-100 dark:border-orange-800 flex items-center justify-center">
                            <i class="ri-fire-line text-lg"></i>
                        </a>
                    @endcan

                    {{-- SEARCH INPUT (REPLACED BUTTON) --}}
                    <div class="relative w-48 lg:w-64 transition-all">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" x-model="search" 
                            :placeholder="isAddonMode ? '{{ __('messages.search_addons') }}' : '{{ __('messages.search_menu') }}'" 
                            class="w-full pl-9 pr-8 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary focus:bg-white dark:focus:bg-gray-900 outline-none text-sm transition-all">
                        
                        {{-- Clear Button --}}
                        <button x-show="search.length > 0" @click="search = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500" x-cloak>
                            <i class="ri-close-fill"></i>
                        </button>
                    </div>

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
                            <img src="https://flagcdn.com/w40/kh.png" class="w-5 h-auto rounded-sm"> <span>{{ __('messages.language_khmer') }}</span>
                        </a>
                        <a href="{{ route('switch.language', 'en') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ App::getLocale() == 'en' ? 'bg-gray-50 dark:bg-gray-700/50 text-blue-600 font-semibold' : '' }}">
                            <img src="https://flagcdn.com/w40/us.png" class="w-5 h-auto rounded-sm"> <span>{{ __('messages.language_english') }}</span>
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
                            <i class="ri-dashboard-3-line text-primary"></i> {{ __('messages.dashboard') }}
                        </a>
                        @endcan
                        <div class="h-px bg-gray-100 dark:bg-gray-700 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                <i class="ri-logout-box-r-line"></i> {{ __('messages.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- =============================================== --}}
        {{-- ROW 2: OPERATIONAL BUTTONS & SEARCH (MOBILE ONLY) --}}
        {{-- =============================================== --}}
        <div class="md:hidden px-3 pb-2 flex items-center justify-between gap-2">
            
            {{-- Group Left --}}
            <div class="flex items-center gap-2 shrink-0">
                {{-- Addon Toggle --}}
                <button @click="toggleAddonMode()" 
                        class="h-9 px-3 rounded-lg font-bold transition flex items-center justify-center gap-1.5 text-xs border"
                        :class="isAddonMode ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800'">
                    <i class="ri-apps-2-line text-lg"></i> 
                    <span>{{ __('messages.addon') }}</span>
                </button>

                 {{-- Kitchen View --}}
                 <a href="{{ route('pos.kitchen.view') }}" target="_blank" 
                    class="flex items-center justify-center w-10 h-9 rounded-lg bg-orange-50 test-primary hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400 border border-orange-100 dark:border-orange-800">
                     <i class="ri-fire-line text-lg"></i>
                 </a>
            </div>

            {{-- Group Right (Search Input for Mobile) --}}
            <div class="flex-1 relative">
                <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="search" 
                        :placeholder="isAddonMode ? '{{ __('messages.search_general') }}' : '{{ __('messages.search_general') }}'" 
                        class="w-full pl-9 pr-3 py-2 h-9 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-1 focus:ring-primary outline-none text-xs transition-all">
            </div>
        </div>

        {{-- ROW 3: CATEGORY TABS --}}
        @if(isset($categories))
        <div class="px-3 sm:px-4 pb-2 sm:pb-3 overflow-x-auto no-scrollbar flex gap-2 sm:gap-3 snap-x">
            <button @click="setCategory('all')" 
                    class="snap-start flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-bold transition-all border shadow-sm select-none" 
                    :class="activeCategory === 'all' ? 'bg-primary border-primary text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50'">
                {{ __('messages.all') }}
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
            <i class="ri-flashlight-fill text-yellow-300"></i> {{ __('messages.addon_mode_active') }}
        </div>
    </div>

    
</div>