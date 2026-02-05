<div class="bg-card-bg rounded-3xl p-6 border border-bor-color shadow-custom">
     <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-bor-color pb-4">
        {{ __('messages.general_settings') }}
    </h3>
     <div class="mb-6">
        <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.phone_number') }}</label>
        <input type="text" name="phone_number" value="{{ old('phone_number', $shop->phone_number ?? '') }}"
            class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
    </div>
    
    {{-- Status Checkbox --}}
    <div class="bg-page-bg rounded-2xl p-4 flex items-center justify-between border border-input-border">
        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ __('messages.shop_status') }}</span>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" name="status" value="1" class="sr-only peer" {{ ($shop->status ?? 1) ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
        </label>
    </div>
</div>