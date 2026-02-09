<div class="min-h-[3.5rem] sm:h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex flex-wrap sm:flex-nowrap items-center justify-between px-3 py-2 sm:py-0 sm:px-6 shrink-0 z-20 shadow-md gap-y-2 transition-colors duration-300">
    
    {{-- =============================================== --}}
    {{-- 1. LEFT SIDE (Brand, Clock, Products)           --}}
    {{-- Order-1: នៅខាងឆ្វេងលើគេជានិច្ច                 --}}
    {{-- =============================================== --}}
    <div class="flex items-center gap-3 sm:gap-6 order-1">
        @unlessrole('Chef|Bartender')
            <a href="{{ route('pos.tables') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition shrink-0" title="{{ __('messages.back_to_tables') }}">
                <i class="ri-arrow-left-line text-lg sm:text-xl"></i>
            </a>
        @endunlessrole

        {{-- Title --}}
        <h1 class="text-base sm:text-xl font-bold tracking-wide uppercase flex items-center gap-2 truncate text-gray-800 dark:text-white">
            <i class="ri-fire-line text-orange-500"></i> <span class="hidden xs:inline">{{ __('messages.kitchen_system') }}</span>
        </h1>
        
        <div class="h-4 sm:h-6 w-px bg-gray-300 dark:bg-gray-600 hidden xs:block"></div>
        <span class="text-lg sm:text-2xl font-mono font-bold text-blue-600 dark:text-blue-400" x-text="clockString"></span>

        {{-- Product Button (បង្ហាញវិញហើយ តែដាក់ Icon សុទ្ធលើទូរស័ព្ទ) --}}
        @can('product-list')
            <div class="h-4 sm:h-6 w-px bg-gray-300 dark:bg-gray-600 hidden xs:block"></div>
            <a href="{{ url('/admin/products') }}" 
                class="flex items-center gap-2 px-2 sm:px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition text-xs sm:text-sm font-bold border border-gray-200 dark:border-gray-600">
                <i class="ri-box-3-line text-primary"></i>
                <span class="hidden sm:inline">{{ __('messages.products') }}</span> {{-- លាក់អក្សរលើទូរស័ព្ទ --}}
            </a>
        @endcan
    </div>

    {{-- =============================================== --}}
    {{-- 2. RIGHT SIDE (Settings, Profile)               --}}
    {{-- Order-2: នៅខាងស្តាំលើគេលើទូរស័ព្ទ (Order-3 on PC)--}}
    {{-- =============================================== --}}
    <div class="flex items-center gap-2 sm:gap-3 shrink-0 order-2 sm:order-3 ml-auto sm:ml-0">
        
        {{-- Live Indicator --}}
        <div class="flex items-center gap-1 sm:gap-2 mr-1">
            <span class="relative flex h-2.5 w-2.5 sm:h-3 sm:w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="isLoading ? 'bg-blue-400' : 'bg-green-400'"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 sm:h-3 sm:w-3" :class="isLoading ? 'bg-blue-500' : 'bg-green-500'"></span>
            </span>
        </div>

        {{-- Language --}}
        <div x-data="{ languageOpen: false }" class="relative">
            <button @click="languageOpen = !languageOpen" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors border border-transparent dark:border-gray-700">
                @if(App::getLocale() == 'km')
                    <img src="https://flagcdn.com/w40/kh.png" alt="Khmer" class="w-5 h-auto rounded-sm shadow-sm object-cover">
                @else
                    <img src="https://flagcdn.com/w40/us.png" alt="English" class="w-5 h-auto rounded-sm shadow-sm object-cover">
                @endif
            </button>
            <div x-show="languageOpen" @click.outside="languageOpen = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-xl py-1 border border-gray-100 dark:border-gray-700 z-50 origin-top-right">
                <a href="{{ route('switch.language', 'km') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ App::getLocale() == 'km' ? 'bg-gray-50 dark:bg-gray-700/50 text-blue-600 font-semibold' : '' }}">
                    <img src="https://flagcdn.com/w40/kh.png" class="w-5 h-auto rounded-sm"> <span>ភាសាខ្មែរ</span>
                </a>
                <a href="{{ route('switch.language', 'en') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ App::getLocale() == 'en' ? 'bg-gray-50 dark:bg-gray-700/50 text-blue-600 font-semibold' : '' }}">
                    <img src="https://flagcdn.com/w40/us.png" class="w-5 h-auto rounded-sm"> <span>English</span>
                </a>
            </div>
        </div>

        {{-- Theme --}}
        <button x-data="{ 
                    darkMode: localStorage.getItem('theme_mode') === 'dark' || localStorage.getItem('theme') === 'dark',
                    toggle() {
                        this.darkMode = !this.darkMode;
                        localStorage.setItem('theme_mode', this.darkMode ? 'dark' : 'light');
                        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                        if (this.darkMode) document.documentElement.classList.add('dark');
                        else document.documentElement.classList.remove('dark');
                    },
                    init() { if (this.darkMode) document.documentElement.classList.add('dark'); }
                }" 
                @click="toggle()" 
                class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <i class="text-lg" :class="darkMode ? 'ri-moon-fill text-yellow-400' : 'ri-sun-fill text-gray-500'"></i>
        </button>

        {{-- Profile --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false" 
                    class="flex items-center gap-2 bg-gray-100 dark:bg-gray-700 pl-1 pr-1 py-1 rounded-full border border-gray-200 dark:border-gray-600 hover:border-primary/50 transition-all shadow-sm">
                <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Chef' }}&background=E11D48&color=fff&size=64" 
                        class="w-7 h-7 rounded-full object-cover" alt="Avatar">
            </button>
            <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-50 origin-top-right">
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

    {{-- =============================================== --}}
    {{-- 3. CENTER / TABS (Kitchen / Bar)                --}}
    {{-- Order-3: នៅខាងក្រោមគេ (Row 2) លើទូរស័ព្ទ       --}}
    {{-- Order-2: នៅកណ្តាលលើ PC                          --}}
    {{-- =============================================== --}}
    <div class="order-3 sm:order-2 w-full sm:w-auto mt-1 sm:mt-0">
        <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto custom-scrollbar w-full sm:max-w-md">
            <div class="flex gap-1 w-full sm:w-auto">
                @foreach($destinations as $dest)
                    <button @click="changeMode({{ $dest->id }})" 
                            class="flex-1 sm:flex-none px-3 sm:px-6 py-2 rounded-md text-xs sm:text-sm font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap"
                            :class="currentDestinationId == {{ $dest->id }} ? 'bg-primary text-white shadow-md' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-800'">
                        @if(stripos($dest->name, 'bar') !== false || stripos($dest->name, 'drink') !== false)
                            <i class="ri-goblet-line"></i>
                        @else
                            <i class="ri-restaurant-line"></i>
                        @endif
                        <span class="inline">{{ $dest->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

</div>