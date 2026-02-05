<div id="actionBar"
    class="fixed bottom-6 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 p-4 rounded-2xl shadow-2xl z-40 flex items-center justify-between transform hover:scale-[1.005] transition-transform duration-300">

    {{-- Status Text --}}
    <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <i class="ri-information-line text-lg text-blue-500"></i>
            <span x-show="!$store.theme.isSaving">{{ __('messages.status_ready') }}</span>
            <span x-show="$store.theme.isSaving" class="text-blue-500 font-medium">{{ __('messages.status_processing') }}</span>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex gap-3 w-full sm:w-auto justify-end">

        <button type="button" @click="$store.theme.reset()"
            class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 group">
            <i class="ri-restart-line group-hover:-rotate-180 transition-transform duration-500 text-gray-500 dark:text-gray-400"></i>
            <span>{{ __('messages.btn_reset') }}</span>
        </button>

        <button type="button" @click="$store.theme.save()" :disabled="$store.theme.isSaving"
            class="px-6 py-2.5 rounded-xl bg-gradient-to-r btn-primary text-white font-bold shadow-lg shadow-blue-500/30 flex items-center gap-2 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed"
            :class="$store.theme.isSaving ? 'scale-100' : 'hover:scale-105 hover:shadow-blue-500/50'">

            <i x-show="!$store.theme.isSaving" class="ri-save-3-fill text-lg"></i>

            <svg x-show="$store.theme.isSaving" class="animate-spin h-5 w-5 text-white"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>

            <span x-text="$store.theme.isSaving ? '{{ __('messages.btn_saving') }}' : '{{ __('messages.btn_save_changes') }}'"></span>
        </button>
    </div>
</div>