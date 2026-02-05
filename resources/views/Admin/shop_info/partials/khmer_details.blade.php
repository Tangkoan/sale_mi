<div class="bg-card-bg rounded-3xl p-6 border border-bor-color shadow-custom">
     <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-bor-color pb-4">
        {{ __('messages.khmer_details') }}
    </h3>
     <div class="space-y-6">
        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.shop_name_kh') }}</label>
            <input type="text" name="shop_kh" value="{{ old('shop_kh', $shop->shop_kh ?? '') }}"
                class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-khmer placeholder-gray-400">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.address_kh') }}</label>
            <textarea name="address_kh" rows="2" 
                class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-khmer placeholder-gray-400">{{ old('address_kh', $shop->address_kh ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.description_kh') }}</label>
            <textarea name="description_kh" rows="3" 
                class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-khmer placeholder-gray-400">{{ old('description_kh', $shop->description_kh ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.note_kh') }}</label>
            <textarea name="note_kh" rows="3" 
                class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all font-khmer placeholder-gray-400">{{ old('note_kh', $shop->note_kh ?? '') }}</textarea>
        </div>
     </div>
</div>