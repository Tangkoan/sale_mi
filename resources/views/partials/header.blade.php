<header 
    x-data 
    x-effect="
    if ($store.theme.darkMode) document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
    "
    class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 
           h-16 flex items-center justify-between px-6 shadow-sm z-10 sticky top-0 
           transition-colors duration-300">

    <button id="sidebarToggle" 
        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 
               text-gray-600 dark:text-gray-300 transition-colors">
        <i class="ri-menu-2-line text-xl"></i>
    </button>

    <div class="flex items-center gap-4">

        <button 
            type="button"
            
            /* កន្លែងកែ៖ ប្រើ setMode ដើម្បីអោយវាដំណើរការ Logic ប្តូរពណ៌ពេញលេញ */
            @click="$store.theme.setMode($store.theme.darkMode ? 'light' : 'dark')"
            
            class="relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900"
            :class="!$store.theme.darkMode ? 'bg-gray-200' : ''"
            :style="$store.theme.darkMode ? 'background-color: var(--primary, #308D71)' : ''"
        >
            <span class="sr-only">Toggle Dark Mode</span>
            
            <span 
                class="inline-block h-6 w-6 transform rounded-full bg-white shadow-md transition duration-300 ease-in-out flex items-center justify-center"
                :class="$store.theme.darkMode ? 'translate-x-7' : 'translate-x-1'"
            >
                <i x-show="!$store.theme.darkMode" class="ri-sun-fill text-yellow-500 text-sm"></i>
                
                <i x-show="$store.theme.darkMode" 
                   class="ri-moon-fill text-sm"
                   :style="'color: var(--primary, #308D71)'"
                ></i>
            </span>
        </button>
        <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 relative">
            <i class="ri-notification-3-line text-xl"></i>
            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full 
                        border border-white dark:border-gray-900"></span>
        </button>

        <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-blue-500 to-cyan-500 
                    flex items-center justify-center text-white font-bold text-sm shadow-md cursor-pointer">
            {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
        </div>

    </div>
</header>