<div class="md:col-span-2 bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
    <h3 class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
        <span class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
            <i class="ri-file-list-fill"></i>
        </span>
        {{ __('messages.content_forms') }}
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
        @include('components.color-input', ['label' => __('messages.col_page_bg'), 'key' => 'pageBg'])
        @include('components.color-input', ['label' => __('messages.col_card_bg'), 'key' => 'cardBg'])

        <div class="col-span-1 md:col-span-2 mt-2 mb-2 pt-2 border-t border-dashed border-gray-200 dark:border-gray-700">
            <p class="text-xs font-bold text-gray-400 uppercase mb-3">{{ __('messages.form_inputs') }}</p>
        </div>

        @include('components.color-input', ['label' => __('messages.col_input_bg'), 'key' => 'inputBg'])
        @include('components.color-input', ['label' => __('messages.col_input_border'), 'key' => 'inputBorder'])
        @include('components.color-input', ['label' => __('messages.col_layout_borders'), 'key' => 'border'])
    </div>
</div>