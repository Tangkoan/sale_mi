<div class="bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
    <h3 class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
        <span class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
            <i class="ri-flag-fill"></i>
        </span>
        {{ __('messages.brand_identity') }}
    </h3>

    @include('components.color-input', ['label' => __('messages.col_primary_bg'), 'key' => 'primary'])
    @include('components.color-input', ['label' => __('messages.col_primary_text'), 'key' => 'primaryText'])
    @include('components.color-input', ['label' => __('messages.col_general_text'), 'key' => 'textColor'])
    @include('components.color-input', ['label' => __('messages.col_secondary'), 'key' => 'secondary'])
</div>