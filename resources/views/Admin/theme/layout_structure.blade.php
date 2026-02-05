<div class="bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
    <h3 class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
        <span class="p-2 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
            <i class="ri-layout-masonry-fill"></i>
        </span>
        {{ __('messages.layout_structure') }}
    </h3>
    
    @include('components.color-input', ['label' => __('messages.col_sidebar_bg'), 'key' => 'sidebarBg'])
    @include('components.color-input', ['label' => __('messages.col_sidebar_text'), 'key' => 'sidebarText'])

    <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
        <p class="text-xs font-bold text-gray-400 uppercase mb-3">{{ __('messages.sidebar_hover_state') }}</p>
        @include('components.color-input', ['label' => __('messages.col_hover_bg'), 'key' => 'sidebarHoverBg'])
        @include('components.color-input', ['label' => __('messages.col_hover_text'), 'key' => 'sidebarHoverText'])
    </div>

    @include('components.color-input', ['label' => __('messages.col_header_bg'), 'key' => 'headerBg'])
</div>