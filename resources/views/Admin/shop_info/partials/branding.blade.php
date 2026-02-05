<div class="bg-card-bg rounded-3xl p-6 border border-bor-color shadow-custom">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-bor-color pb-4">
        {{ __('messages.branding') }}
    </h3>
    
    {{-- LOGO --}}
    <div class="mb-6">
        <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.main_logo') }}</label>
        <div class="relative group w-full aspect-video bg-page-bg rounded-2xl border-2 border-dashed border-bor-color hover:border-primary hover:bg-primary/5 transition-all flex flex-col items-center justify-center cursor-pointer overflow-hidden">
            
            <template x-if="logoPreview">
                <img :src="logoPreview" class="w-full h-full object-contain p-4">
            </template>
            <template x-if="!logoPreview">
                <div class="flex flex-col items-center text-gray-400 group-hover:text-primary transition-colors">
                    <i class="ri-image-add-line text-4xl mb-2"></i>
                    <span class="text-xs font-semibold">{{ __('messages.click_to_upload') }}</span>
                </div>
            </template>

            <input type="file" name="logo" @change="updatePreview($event, 'logoPreview')" class="absolute inset-0 opacity-0 cursor-pointer z-10">
        </div>
    </div>

    {{-- FAVICON --}}
    <div class="border-t border-bor-color pt-5">
        <div class="flex items-start gap-4">
            <div class="relative group w-16 h-16 bg-page-bg rounded-xl border-2 border-dashed border-bor-color hover:border-primary hover:bg-primary/5 transition-all flex items-center justify-center overflow-hidden flex-shrink-0">
                <template x-if="favPreview">
                    <img :src="favPreview" class="w-8 h-8 object-contain">
                </template>
                <template x-if="!favPreview">
                    <i class="ri-upload-cloud-2-line text-gray-400 text-xl group-hover:text-primary transition-colors"></i>
                </template>
                
                <input type="file" name="fav" @change="updatePreview($event, 'favPreview')" class="absolute inset-0 opacity-0 cursor-pointer">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">{{ __('messages.favicon') }}</label>
                <p class="text-xs text-gray-500 mt-1">{{ __('messages.favicon_desc') }}</p>
            </div>
        </div>
    </div>
</div>