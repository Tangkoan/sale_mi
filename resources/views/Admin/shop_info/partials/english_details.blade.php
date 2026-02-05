<div class="bg-card-bg rounded-3xl p-6 border border-bor-color shadow-custom">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-bor-color pb-4">
        {{ __('messages.english_details') }}
    </h3>
    
    <div class="space-y-6">
        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.shop_name') }} <span class="text-red-500">*</span></label>
            <input type="text" name="shop_en" value="{{ old('shop_en', $shop->shop_en ?? '') }}"
                class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all placeholder-gray-400">
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.address') }}</label>
            <textarea name="address_en" rows="2" 
                class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all placeholder-gray-400">{{ old('address_en', $shop->address_en ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.description') }}</label>
            <textarea name="description_en" rows="3" 
                class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all placeholder-gray-400">{{ old('description_en', $shop->description_en ?? '') }}</textarea>
        </div>
    </div>
</div>