<div class="px-4 py-3 sm:px-6 bg-white/80 dark:bg-gray-900/90 backdrop-blur-xl border-b border-gray-200/50 dark:border-gray-700/50 z-30 shrink-0 sticky top-0 shadow-sm transition-all duration-300">
    
    {{-- 🔥 MAIN CONTAINER: 
         - Mobile: flex-col (២ ជួរ)
         - Desktop: flex-row (១ ជួរ - ដូចចាស់) 
    --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
        
        {{-- ========================================================= --}}
        {{-- ផ្នែកទី ១: ខាងឆ្វេង (Title & Back Button)                --}}
        {{-- ========================================================= --}}
        {{-- Mobile: w-full & justify-between (ដើម្បីដាក់ Status នៅចុងម្ខាងទៀត) --}}
        {{-- Desktop: w-auto & justify-start --}}
        <div class="flex justify-between md:justify-start items-center w-full md:w-auto">
            
            {{-- A. BACK BUTTON & TITLE --}}
            <div class="flex items-center gap-3 sm:gap-4">
                
                {{-- Back Button (Logic: បង្ហាញតែអ្នកមាន Permission) --}}
                @can('dashboard')
                    <a href="{{ route('admin.dashboard') }}" 
                       class="group relative flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-primary hover:text-white transition-all duration-300 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                       title="{{ __('messages.back_to_dashboard') }}">
                        <i class="ri-home-4-line text-lg group-hover:scale-0 transition-transform duration-300 absolute"></i>
                        <i class="ri-arrow-left-line text-lg scale-0 group-hover:scale-100 transition-transform duration-300 absolute"></i>
                    </a>
                    <div class="h-6 w-px bg-gray-300 dark:bg-gray-700 hidden sm:block"></div>
                @endcan

                {{-- Title & Subtitle --}}
                <div class="flex flex-col">
                    <h1 class="text-lg sm:text-xl font-black text-gray-800 dark:text-white flex items-center gap-2 tracking-tight leading-none">
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-primary to-emerald-600 text-white shadow-md shadow-primary/30">
                            <i class="ri-store-2-fill text-sm"></i>
                        </span>
                        <span>{{ __('messages.select_table') }}</span>
                    </h1>
                    <p class="text-[10px] sm:text-xs font-medium text-gray-400 dark:text-gray-500 mt-1 hidden sm:block">
                        {{ __('messages.please_select_table_to_order') }}
                    </p>
                </div>
            </div>

            {{-- 🔥 B. MOBILE STATUS (បង្ហាញតែលើ Mobile នៅជួរទី ១ ខាងស្តាំ) --}}
            {{-- Logic: Class 'md:hidden' --}}
            <div class="flex md:hidden items-center gap-2 bg-gray-50/50 dark:bg-gray-800/50 p-1 rounded-full border border-gray-100 dark:border-gray-700/50">
                {{-- Available --}}
                <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-white dark:bg-gray-700 shadow-sm border border-gray-100 dark:border-gray-600">
                    <span class="relative flex h-1.5 w-1.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[9px] font-bold text-gray-700 dark:text-gray-200 uppercase">{{ __('messages.available') }}</span>
                </div>
                {{-- Busy --}}
                <div class="flex items-center gap-1 px-2 py-1 opacity-60">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    <span class="text-[9px] font-bold text-gray-500 dark:text-gray-400 uppercase">{{ __('messages.busy') }}</span>
                </div>
            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- ផ្នែកទី ២: ខាងស្តាំ (Tools & Desktop Status)              --}}
        {{-- ========================================================= --}}
        {{-- Mobile: w-full & justify-end --}}
        {{-- Desktop: w-auto & flex-row --}}
        <div class="flex items-center justify-end gap-3 w-full md:w-auto">

            {{-- 🔥 C. DESKTOP STATUS (បង្ហាញតែលើ Desktop នៅជួរជាមួយ Tools) --}}
            {{-- Logic: Class 'hidden md:flex' --}}
            <div class="hidden md:flex items-center gap-2 bg-gray-50/50 dark:bg-gray-800/50 p-1 rounded-full border border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-white dark:bg-gray-700 shadow-sm border border-gray-100 dark:border-gray-600">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wide">{{ __('messages.available') }}</span>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 opacity-60">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.busy') }}</span>
                </div>
            </div>

            {{-- Divider --}}
            <div class="hidden md:block h-5 w-px bg-gray-300 dark:bg-gray-600"></div>
            
            {{-- D. TOOLBAR BUTTONS (Exchange, Lang, Theme, User) --}}
            <div class="flex items-center gap-2 sm:gap-3 bg-gray-50 dark:bg-gray-800/80 p-1.5 rounded-2xl border border-gray-100 dark:border-gray-700 w-full md:w-auto justify-between md:justify-end">
                
                {{-- Label (Mobile Only) --}}
                <span class="text-[10px] font-bold text-gray-400 uppercase px-2 md:hidden">{{ __('messages.menu') }}</span>

                <div class="flex items-center gap-2 sm:gap-3">
                    
                    {{-- 1. Exchange Rate --}}
                    <button @click="openExchangeModal()" class="flex items-center justify-center w-10 h-9 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800 relative">
                            <i class="ri-exchange-dollar-line text-lg"></i>
                            <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                    </button>

                    {{-- 2. Language Switcher --}}
                    <div x-data="{ languageOpen: false }" class="relative">
                        <button @click="languageOpen = !languageOpen" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 flex items-center justify-center shadow-sm">
                            @if(App::getLocale() == 'km')
                                <img src="https://flagcdn.com/w40/kh.png" class="w-5 h-5 rounded-full object-cover border border-gray-100">
                            @else
                                <img src="https://flagcdn.com/w40/us.png" class="w-5 h-5 rounded-full object-cover border border-gray-100">
                            @endif
                        </button>
                        <div x-show="languageOpen" @click.outside="languageOpen = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-xl py-1.5 border border-gray-100 dark:border-gray-700 z-50 origin-top-right ring-1 ring-black/5">
                            <a href="{{ route('switch.language', 'km') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ App::getLocale() == 'km' ? 'bg-primary/5 text-primary' : '' }}">
                                <img src="https://flagcdn.com/w40/kh.png" class="w-4 h-4 rounded-full shadow-sm">
                                <span>ភាសាខ្មែរ</span>
                                @if(App::getLocale() == 'km') <i class="ri-check-line ml-auto text-primary"></i> @endif
                            </a>
                            <a href="{{ route('switch.language', 'en') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ App::getLocale() == 'en' ? 'bg-primary/5 text-primary' : '' }}">
                                <img src="https://flagcdn.com/w40/us.png" class="w-4 h-4 rounded-full shadow-sm">
                                <span>English</span>
                                @if(App::getLocale() == 'en') <i class="ri-check-line ml-auto text-primary"></i> @endif
                            </a>
                        </div>
                    </div>

                    {{-- 3. Theme Toggle --}}
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
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 flex items-center justify-center shadow-sm group">
                        <i class="text-lg transition-transform duration-500 rotate-0 dark:-rotate-180" 
                           :class="darkMode ? 'ri-moon-fill text-yellow-400' : 'ri-sun-fill text-orange-500'"></i>
                    </button>

                    <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>

                    {{-- 4. User Profile --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" 
                                class="flex items-center gap-2 bg-white dark:bg-gray-700 pl-1 pr-2 py-1 rounded-full border border-gray-200 dark:border-gray-600 hover:border-primary/50 transition-all shadow-sm">
                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Cashier' }}&background=0D8ABC&color=fff&size=64" 
                                 class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-600" alt="Avatar">
                            <div class="hidden sm:flex flex-col items-start leading-none">
                                <span class="text-[10px] font-bold text-gray-700 dark:text-gray-200">{{ Auth::user()->name ?? 'Cashier' }}</span>
                            </div>
                            <i class="ri-arrow-down-s-line text-xs text-gray-400"></i>
                        </button>

                        {{-- Profile Dropdown --}}
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1.5 z-50 origin-top-right ring-1 ring-black/5">
                            
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700/50 mb-1">
                                <p class="text-xs font-bold text-gray-800 dark:text-white truncate">{{ Auth::user()->name ?? 'Cashier' }}</p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                            </div>

                            @can('dashboard')
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                                    <i class="ri-dashboard-3-line text-primary"></i> {{ __('messages.dashboard') }}
                                </a>
                            @endcan

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10 flex items-center gap-2 transition-colors">
                                    <i class="ri-logout-box-r-line"></i> {{ __('messages.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL: Exchange Rate --}}
<div x-show="isExchangeModalOpen" 
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
     x-cloak 
     x-transition.opacity>
    
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all" 
         @click.away="isExchangeModalOpen = false" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 scale-90 translate-y-4" 
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="ri-coins-line text-emerald-500"></i> {{ __('messages.exchange_rate_title') }}
            </h3>
            <button @click="isExchangeModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i class="ri-close-line text-lg"></i>
            </button>
        </div>

        <div class="p-6 space-y-5">
            <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800">
                <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold uppercase mb-1">{{ __('messages.current_system_rate') }}</p>
                <div class="text-3xl font-black text-gray-800 dark:text-white">
                    <span class="text-base text-gray-400 font-medium align-middle mr-1">$1 =</span>
                    <span x-text="formatNumber(exchangeRate)"></span> 
                    <span class="text-sm text-gray-500 font-bold align-middle">KHR</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">{{ __('messages.set_new_rate') }}</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-400 font-bold">៛</span>
                    </div>
                    <input type="number" x-model="tempExchangeRate" class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white font-bold text-xl outline-none focus:ring-2 focus:ring-emerald-500/50 transition-all">
                </div>
            </div>

            <button @click="fetchRateFromApi()" class="w-full py-2.5 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 hover:text-primary hover:border-primary transition flex items-center justify-center gap-2 text-sm font-semibold" :disabled="isFetchingRate">
                <i class="ri-download-cloud-2-line text-lg" :class="isFetchingRate ? 'animate-spin' : ''"></i> 
                <span x-text="isFetchingRate ? '{{ __('messages.fetching') }}' : '{{ __('messages.auto_fetch_nbc') }}'"></span>
            </button>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 flex gap-3">
            <button @click="isExchangeModalOpen = false" class="flex-1 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-100 transition text-sm">{{ __('messages.cancel') }}</button>
            <button @click="saveExchangeRate()" class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-500 shadow-lg active:scale-95 transition text-sm">{{ __('messages.save_change') }}</button>
        </div>
    </div>
</div>