<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
            <i class="ri-settings-4-line text-primary"></i> 
            {{ __('messages.configure_rules') }}: <span class="text-primary underline decoration-2 underline-offset-4">{{ $role->name }}</span>
        </h1>
        <p class="text-sm text-secondary mt-1">
            {!! __('messages.rule_configure_desc', ['name' => '<strong>' . $role->name . '</strong>']) !!}
        </p>
    </div>
    <a href="{{ route('admin.rules.index') }}" class="text-secondary hover:text-text-color flex items-center gap-1 font-medium group transition-colors">
        <div class="h-8 w-8 rounded-full bg-white dark:bg-gray-800 border border-border-color flex items-center justify-center group-hover:border-primary group-hover:text-primary transition-colors">
            <i class="ri-arrow-left-line"></i>
        </div>
        <span>{{ __('messages.btn_back') }}</span>
    </a>
</div>