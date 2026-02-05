<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <span class="p-2 rounded-lg bg-primary/10 text-primary">
                <i class="ri-store-3-line text-2xl"></i>
            </span>
            {{ __('messages.shop_configuration') }}
        </h1>
    </div>

    {{-- Submit Button --}}
    <button type="submit" 
        class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-primary hover:opacity-90 shadow-lg shadow-primary/30 transition-all hover:-translate-y-0.5">
        <i class="ri-save-line mr-2 text-lg"></i>
        <span>{{ __('messages.save_changes') }}</span>
    </button>
</div>