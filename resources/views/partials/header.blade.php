<header 
    x-data="{ userDropdownOpen: false, languageOpen: false }" 
    x-effect="if ($store.theme.darkMode) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark');"
    class="bg-header-bg border-b border-bor-color h-16 flex items-center justify-between px-6 shadow-sm z-10 sticky top-0 transition-colors duration-300">

    <button id="sidebarToggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 transition-colors">
        <i class="ri-menu-2-line text-xl"></i>
    </button>

    <div class="flex items-center gap-4">
        
        {{-- ========================================== --}}
        {{-- [START] បន្ថែម Button Sale នៅទីនេះ         --}}
        {{-- ========================================== --}}
        
        {{-- សូមកែ 'pos_access' ទៅតាមឈ្មោះ Permission របស់អ្នក ឧទាហរណ៍: 'create_sale' ឬ 'view_pos' --}}
        @can('pos') 
        <a href="{{ url('/pos/tables') }}" 
           class="hidden sm:flex items-center gap-2 btn-primary px-4 py-1.5 rounded-lg shadow-sm transition-all duration-200 transform hover:scale-105 mr-1">
            <i class="ri-computer-line"></i> {{-- ឬប្រើ icon ri-shopping-cart-line --}}
            <span class="font-medium text-sm">Sale</span>
        </a>
        
        {{-- ប៊ូតុងសម្រាប់ទូរស័ព្ទ (បង្ហាញតែ Icon) --}}
        <a href="{{ url('/pos/tables') }}" 
           class="sm:hidden flex items-center justify-center btn-primary h-8 w-8 rounded-full shadow-sm mr-1">
            <i class="ri-computer-line"></i>
        </a>
        @endcan

        {{-- ========================================== --}}
        {{-- [END] បញ្ចប់ការបន្ថែម Button Sale            --}}
        {{-- ========================================== --}}

        {{-- Theme Toggle --}}
        <button type="button" @click="$store.theme.setMode($store.theme.darkMode ? 'light' : 'dark')" class="relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900" :class="!$store.theme.darkMode ? 'bg-gray-200' : ''" :style="$store.theme.darkMode ? 'background-color: var(--primary, #308D71)' : ''">
            <span class="sr-only">Toggle Dark Mode</span>
            <span class="inline-block h-6 w-6 transform rounded-full bg-white shadow-md transition duration-300 ease-in-out flex items-center justify-center" :class="$store.theme.darkMode ? 'translate-x-7' : 'translate-x-1'">
                <i x-show="!$store.theme.darkMode" class="ri-sun-fill text-yellow-500 text-sm"></i>
                <i x-show="$store.theme.darkMode" class="ri-moon-fill text-sm" :style="'color: var(--primary, #308D71)'"></i>
            </span>
        </button>

        {{-- Language Switcher --}}
        <div class="relative">
            <button @click="languageOpen = !languageOpen" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 transition-colors">
                @if(App::getLocale() == 'km')
                    <img src="https://flagcdn.com/w40/kh.png" alt="Khmer" class="w-6 h-auto rounded-sm shadow-sm object-cover">
                    <span class="text-sm font-medium hidden sm:block">KH</span>
                @else
                    <img src="https://flagcdn.com/w40/us.png" alt="English" class="w-6 h-auto rounded-sm shadow-sm object-cover">
                    <span class="text-sm font-medium hidden sm:block">EN</span>
                @endif
                <i class="ri-arrow-down-s-line transition-transform duration-200" :class="{'rotate-180': languageOpen}"></i>
            </button>

            <div x-show="languageOpen" 
                 @click.outside="languageOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-1 border border-gray-100 dark:border-gray-700 z-50 origin-top-right"
                 style="display: none;">
                
                <a href="{{ route('switch.language', 'km') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ App::getLocale() == 'km' ? 'bg-gray-50 dark:bg-gray-700/50 text-blue-600 font-semibold' : '' }}">
                    <img src="https://flagcdn.com/w40/kh.png" alt="Khmer" class="w-5 h-auto rounded-sm shadow-sm">
                    <span>ភាសាខ្មែរ</span>
                    @if(App::getLocale() == 'km') <i class="ri-check-line ml-auto text-blue-600"></i> @endif
                </a>

                <a href="{{ route('switch.language', 'en') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ App::getLocale() == 'en' ? 'bg-gray-50 dark:bg-gray-700/50 text-blue-600 font-semibold' : '' }}">
                    <img src="https://flagcdn.com/w40/us.png" alt="English" class="w-5 h-auto rounded-sm shadow-sm">
                    <span>English</span>
                    @if(App::getLocale() == 'en') <i class="ri-check-line ml-auto text-blue-600"></i> @endif
                </a>
            </div>
        </div>

        

        {{-- User Dropdown --}}
        <div class="relative">
           <button @click="userDropdownOpen = !userDropdownOpen" 
                   class="h-8 w-8 rounded-full flex items-center justify-center text-primary font-bold text-sm shadow-md cursor-pointer focus:outline-none ring-2 ring-secondary focus:ring-primary transition-all p-0 overflow-hidden
                   {{ Auth::user()->avatar ? 'bg-transparent' : 'bg-gradient-to-tr' }}">
                
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                        alt="{{ Auth::user()->name }}" 
                        class="h-full w-full object-cover">
                @else
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                @endif

            </button>

            <div x-show="userDropdownOpen" 
                 @click.outside="userDropdownOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-1 border border-gray-100 dark:border-gray-700 z-50 origin-top-right"
                 style="display: none;">
                
                <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                </div>

                <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="ri-user-line mr-2"></i> {{ __('messages.profile') }}
                </a>

                <a href="{{ route('admin.password') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="ri-lock-password-line mr-2"></i> {{ __('messages.change_password') }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                        <i class="ri-logout-box-line mr-2"></i> {{ __('messages.logout') }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>