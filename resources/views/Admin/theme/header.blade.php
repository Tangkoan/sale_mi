<div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ __('messages.theme_customizer') }}</h1>
        <p class="text-gray-500 mt-2">{{ __('messages.theme_desc') }}</p>
    </div>

    <div class="bg-gray-100 dark:bg-gray-800 p-1.5 rounded-xl flex shadow-inner">
        <button @click="$store.theme.setMode('light')"
            :class="activeTab === 'light' ? 'bg-white text-blue-600 shadow-sm' :
                'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
            class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
            <i class="ri-sun-line text-lg"></i> {{ __('messages.mode_light') }}
        </button>

        <button @click="$store.theme.setMode('dark')"
            :class="activeTab === 'dark' ? 'bg-gray-700 text-blue-400 shadow-sm' :
                'text-gray-500 hover:text-gray-300'"
            class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
            <i class="ri-moon-line text-lg"></i> {{ __('messages.mode_dark') }}
        </button>
    </div>
</div>