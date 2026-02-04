<div class="px-6 py-3 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 z-30 shrink-0 sticky top-0 shadow-sm">
    
    {{-- MAIN CONTAINER: JUSTIFY-BETWEEN (ដូចកូដចាស់ Title ឆ្វេង, Action ស្តាំ) --}}
    <div class="flex flex-row justify-between items-center gap-4">
        
        {{-- 1. LEFT SIDE: TITLE & SUBTITLE --}}
        <div class="flex flex-col items-start">
            <h1 class="text-xl sm:text-2xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-primary/10 text-primary">
                    <i class="ri-store-2-fill"></i>
                </span>
                <span>{{ __('messages.select_table') }}</span>
            </h1>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 hidden sm:block pl-10">
                {{ __('messages.please_select_table_to_order') }}
            </p>
        </div>

        {{-- 2. RIGHT SIDE: COMPACT ROW ICONS (ដូចកូដថ្មី) --}}
        <div class="flex items-center gap-3">
            
            {{-- A. Status Legends (Available / Busy) --}}
            <div class="hidden sm:flex items-center gap-2 bg-gray-50 dark:bg-gray-800 p-1 rounded-lg border border-gray-100 dark:border-gray-700">
                {{-- Available --}}
                <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-white dark:bg-gray-700 shadow-sm">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wide">{{ __('Available') }}</span>
                </div>
                
                {{-- Busy --}}
                <div class="flex items-center gap-1.5 px-2 py-1">
                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('Busy') }}</span>
                </div>
            </div>

            {{-- Divider (Show only on Desktop) --}}
            <div class="hidden sm:block h-6 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>

            {{-- B. Theme Toggle --}}
            <button x-data="{ dark: localStorage.getItem('theme') === 'dark' }" 
                    @click="dark = !dark; localStorage.setItem('theme', dark ? 'dark' : 'light'); 
                            if(dark) { document.documentElement.classList.add('dark') } else { document.documentElement.classList.remove('dark') }"
                    class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-yellow-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all active:scale-95 border border-transparent dark:border-gray-700">
                <i class="text-lg" :class="dark ? 'ri-sun-fill' : 'ri-moon-fill'"></i>
            </button>

            {{-- C. User Profile Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" 
                        class="flex items-center gap-2 bg-white dark:bg-gray-800 pl-1 pr-3 py-1 rounded-full border border-gray-200 dark:border-gray-700 hover:border-primary/50 transition-all shadow-sm">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Cashier' }}&background=0D8ABC&color=fff&size=64" 
                         class="w-7 h-7 rounded-full object-cover" alt="Avatar">
                    <div class="hidden md:block text-left">
                        <p class="text-xs font-bold text-gray-800 dark:text-white leading-none truncate max-w-[80px]">
                            {{ Auth::user()->name ?? 'Cashier' }}
                        </p>
                    </div>
                    <i class="ri-arrow-down-s-line text-xs text-gray-400"></i>
                </button>

                {{-- Dropdown Menu (Overlay on Top) --}}
                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-50 origin-top-right">
                    
                    {{-- Dashboard Link --}}
                    @can('dashboard')
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                            <i class="ri-dashboard-3-line text-primary"></i> Dashboard
                        </a>
                    @endcan
                    <div class="h-px bg-gray-100 dark:bg-gray-700 my-1"></div>

                    {{-- Logout Button --}}
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
</div>