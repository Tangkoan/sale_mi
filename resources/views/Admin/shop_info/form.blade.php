@extends('admin.dashboard')

@section('content')

@if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
        <strong class="font-bold"><i class="ri-error-warning-line mr-1"></i> Error!</strong>
        <ul class="mt-1 list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- 
    ✅ Update: ប្រើ bg-page-bg សម្រាប់ background ទូទៅ 
--}}
<div class="min-h-screen py-8 bg-page-bg" x-data="shopForm()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <form action="{{ route('admin.shop_info.save') }}" method="POST" enctype="multipart/form-data" class="relative">
            @csrf

            {{-- Header Section --}}
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

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                
                {{-- Left Column --}}
                <div class="xl:col-span-8 space-y-8">
                    
                    {{-- English Details --}}
                    {{-- 
                        ✅ Update: 
                        - ប្រើ bg-card-bg ជំនួស bg-white/dark:bg-gray-800
                        - ប្រើ border-bor-color ជំនួស border-gray...
                    --}}
                    <div class="bg-card-bg rounded-3xl p-6 border border-bor-color shadow-custom">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-bor-color pb-4">
                            {{ __('messages.english_details') }}
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.shop_name') }} <span class="text-red-500">*</span></label>
                                {{-- 
                                    ✅ Update Input Style:
                                    - bg-input-bg, border-input-border
                                    - focus:border-primary, focus:ring-primary
                                --}}
                                <input type="text" name="shop_en" value="{{ old('shop_en', $shop->shop_en ?? '') }}"
                                    class="w-full px-4 py-3 rounded-xl border border-input-border bg-input-bg text-gray-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all placeholder-gray-400">
                            </div>

                            {{-- Address & Description --}}
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

                    {{-- Khmer Details --}}
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
                </div>

                {{-- Right Column --}}
                <div class="xl:col-span-4 space-y-8">
                    {{-- General Settings --}}
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

                    {{-- Branding (Logo & Favicon) --}}
                    <div class="bg-card-bg rounded-3xl p-6 border border-bor-color shadow-custom">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 border-b border-bor-color pb-4">
                            {{ __('messages.branding') }}
                        </h3>
                        
                        {{-- LOGO --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">{{ __('messages.main_logo') }}</label>
                            {{-- 
                                ✅ Update: កន្លែង Upload ប្រើ bg-page-bg និង border-bor-color
                            --}}
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
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function shopForm() {
        return {
            logoPreview: '{{ $shop->logo ? asset("storage/".$shop->logo) : null }}',
            favPreview: '{{ $shop->fav ? asset("storage/".$shop->fav) : null }}',

            updatePreview(event, previewName) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => { this[previewName] = e.target.result; };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endsection