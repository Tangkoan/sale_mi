@extends('admin.dashboard')

@section('content')

{{-- 
    Update: បានបន្ថែម dark:bg-gray-900 សម្រាប់ផ្ទៃខាងក្រោយទូទៅ
--}}
<div class="min-h-screen py-8 " x-data="shopForm({{ json_encode($shop) }})">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                {{-- Update: បានបន្ថែម dark:text-white --}}
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl btn-primary text-white flex items-center justify-center shadow-lg shadow-blue-600/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                        </svg>
                    </span>
                    {{ __('messages.shop_configuration') }}
                </h1>
            </div>

            <button @click="submitForm" 
                class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 shadow-lg shadow-blue-600/30 transition-all duration-300 hover:-translate-y-0.5"
                :disabled="isLoading">
                <i x-show="isLoading" class="ri-loader-4-line animate-spin mr-2 text-lg"></i>
                <i x-show="!isLoading" class="ri-save-line mr-2 text-lg"></i>
                <span x-text="isLoading ? '{{ __('messages.saving') }}' : '{{ __('messages.save_changes') }}'"></span>
            </button>
        </div>

        <form @submit.prevent="submitForm" class="relative">
            
            {{-- Loading Overlay --}}
            <div x-show="isLoading" 
                 x-transition.opacity
                 class="absolute inset-0 z-50 bg-white/50 dark:bg-gray-900/50 backdrop-blur-[2px] rounded-3xl flex items-center justify-center"
                 style="display: none;">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-full shadow-xl">
                    <i class="ri-loader-2-line text-3xl text-blue-600 animate-spin"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                
                {{-- Left Column (Details) --}}
                <div class="xl:col-span-8 space-y-8">
                    
                    {{-- English Details Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden group hover:shadow-md transition-all duration-300">
                        
                        {{-- Card Header --}}
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-700/30 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <img src="https://flagcdn.com/w40/us.png" class="w-6 h-6 rounded-full shadow-sm object-cover">
                                {{ __('messages.english_details') }}
                            </h3>
                            <span class="text-xs font-medium px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-500 dark:text-gray-300">{{ __('messages.default_label') }}</span>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            {{-- Shop Name Input --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.shop_name') }} <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                        <i class="ri-store-2-line"></i>
                                    </div>
                                    <input type="text" x-model="form.shop_en" 
                                           class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 dark:text-gray-200 bg-gray-50/50 dark:bg-gray-900 focus:bg-white dark:focus:bg-gray-800 placeholder-gray-400 dark:placeholder-gray-500"
                                           placeholder="{{ __('messages.placeholder_shop_name') }}">
                                </div>
                                <p x-show="errors.shop_en" x-text="errors.shop_en" class="text-red-500 text-xs mt-1.5 ml-1"></p>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                {{-- Address Input --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.address') }}</label>
                                    <div class="relative">
                                        <div class="absolute top-3 left-4 pointer-events-none text-gray-400">
                                            <i class="ri-map-pin-line"></i>
                                        </div>
                                        <textarea x-model="form.address_en" rows="2" 
                                                  class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 dark:text-gray-200 bg-gray-50/50 dark:bg-gray-900 focus:bg-white dark:focus:bg-gray-800 resize-none placeholder-gray-400 dark:placeholder-gray-500"
                                                  placeholder="{{ __('messages.placeholder_address') }}"></textarea>
                                    </div>
                                </div>
                                {{-- Description Input --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.description') }}</label>
                                    <div class="relative">
                                        <div class="absolute top-3 left-4 pointer-events-none text-gray-400">
                                            <i class="ri-file-text-line"></i>
                                        </div>
                                        <textarea x-model="form.description_en" rows="3" 
                                                  class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 dark:text-gray-200 bg-gray-50/50 dark:bg-gray-900 focus:bg-white dark:focus:bg-gray-800 resize-none placeholder-gray-400 dark:placeholder-gray-500"
                                                  placeholder="{{ __('messages.placeholder_description') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Khmer Details Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden group hover:shadow-md transition-all duration-300">
                         <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-700/30 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <img src="https://flagcdn.com/w40/kh.png" class="w-6 h-6 rounded-full shadow-sm object-cover">
                                {{ __('messages.khmer_details') }}
                            </h3>
                            <span class="text-xs font-medium px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded">{{ __('messages.local_label') }}</span>
                        </div>

                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.shop_name_kh') }}</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                        <i class="ri-store-2-line"></i>
                                    </div>
                                    <input type="text" x-model="form.shop_kh" 
                                           class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 dark:text-gray-200 bg-gray-50/50 dark:bg-gray-900 focus:bg-white dark:focus:bg-gray-800 font-khmer placeholder-gray-400 dark:placeholder-gray-500"
                                           placeholder="{{ __('messages.placeholder_shop_name_kh') }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.address_kh') }}</label>
                                    <textarea x-model="form.address_kh" rows="2" 
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 dark:text-gray-200 bg-gray-50/50 dark:bg-gray-900 focus:bg-white dark:focus:bg-gray-800 resize-none font-khmer placeholder-gray-400 dark:placeholder-gray-500"
                                              placeholder="{{ __('messages.placeholder_address_kh') }}"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.description_kh') }}</label>
                                    <textarea x-model="form.description_kh" rows="3" 
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 dark:text-gray-200 bg-gray-50/50 dark:bg-gray-900 focus:bg-white dark:focus:bg-gray-800 resize-none font-khmer placeholder-gray-400 dark:placeholder-gray-500"
                                              placeholder="{{ __('messages.placeholder_description_kh') }}"></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.note_kh') }}</label>
                                    <div class="relative h-full">
                                         <textarea x-model="form.note_kh" rows="3" 
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 dark:text-gray-200 bg-gray-50/50 dark:bg-gray-900 focus:bg-white dark:focus:bg-gray-800 resize-none font-khmer placeholder-gray-400 dark:placeholder-gray-500"
                                              placeholder="{{ __('messages.placeholder_note_kh') }}"></textarea>
                                        <p class="text-xs text-gray-400 mt-1 absolute bottom-2 right-2">{{ __('messages.receipt_footer_hint') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column (Settings) --}}
                <div class="xl:col-span-4 space-y-8">
                    
                    {{-- General Settings Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-md transition-all duration-300">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-5 flex items-center gap-2">
                            <i class="ri-settings-4-fill text-blue-600"></i> {{ __('messages.general_settings') }}
                        </h3>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.phone_number') }}</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                        <i class="ri-phone-fill"></i>
                                    </div>
                                    <input type="text" x-model="form.phone_number" 
                                           class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 dark:text-gray-200 bg-gray-50/50 dark:bg-gray-900 focus:bg-white dark:focus:bg-gray-800 font-mono placeholder-gray-400 dark:placeholder-gray-500"
                                           placeholder="012 345 678">
                                </div>
                            </div>

                            {{-- Status Box --}}
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-600">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ __('messages.shop_status') }}</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="form.status" class="sr-only peer" :true-value="1" :false-value="0">
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="form.status == 1 ? '{{ __('messages.status_visible_msg') }}' : '{{ __('messages.status_hidden_msg') }}'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Branding Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-md transition-all duration-300">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-5 flex items-center gap-2">
                            <i class="ri-palette-fill text-purple-500"></i> {{ __('messages.branding') }}
                        </h3>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.main_logo') }}</label>
                            <div class="relative group w-full aspect-video bg-gray-50 dark:bg-gray-900 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-500 hover:bg-blue-50/50 dark:hover:bg-blue-900/20 transition-all flex flex-col items-center justify-center cursor-pointer overflow-hidden">
                                
                                <template x-if="form.logo_preview">
                                    <img :src="form.logo_preview" class="w-full h-full object-contain p-4">
                                </template>
                                
                                <template x-if="!form.logo_preview">
                                    <div class="flex flex-col items-center text-gray-400 dark:text-gray-500 group-hover:text-blue-500 transition-colors">
                                        <i class="ri-image-add-line text-4xl mb-2"></i>
                                        <span class="text-xs font-semibold">{{ __('messages.click_to_upload') }}</span>
                                    </div>
                                </template>

                                <input type="file" @change="handleFileUpload($event, 'logo')" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                
                                <div x-show="form.logo_preview" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20 pointer-events-none">
                                    <span class="text-white text-xs font-bold bg-white/20 px-3 py-1.5 rounded-full backdrop-blur-md border border-white/30">{{ __('messages.change_logo') }}</span>
                                </div>
                            </div>
                            <p class="text-xs text-center text-gray-400 dark:text-gray-500 mt-2">{{ __('messages.logo_recommendation') }}</p>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                            <div class="flex items-start gap-4">
                                {{-- Favicon Box --}}
                                <div class="relative group w-16 h-16 bg-gray-50 dark:bg-gray-900 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-500 transition-all flex items-center justify-center cursor-pointer overflow-hidden flex-shrink-0">
                                    <template x-if="form.fav_preview">
                                        <img :src="form.fav_preview" class="w-8 h-8 object-contain">
                                    </template>
                                    <template x-if="!form.fav_preview">
                                        <i class="ri-upload-cloud-2-line text-gray-400 dark:text-gray-500 text-xl"></i>
                                    </template>
                                    <input type="file" @change="handleFileUpload($event, 'fav')" class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">{{ __('messages.favicon') }}</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ __('messages.favicon_desc') }}</p>
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
    function shopForm(existingData) {
        return {
            isLoading: false,
            errors: {},
            
            form: {
                shop_en: '', shop_kh: '',
                description_en: '', description_kh: '',
                address_en: '', address_kh: '',
                phone_number: '', note_kh: '',
                status: 1,
                logo: null, fav: null,
                logo_preview: null, fav_preview: null
            },

            init() {
                if (existingData) {
                    // Map existing data to form
                    this.form.shop_en = existingData.shop_en;
                    this.form.shop_kh = existingData.shop_kh;
                    this.form.description_en = existingData.description_en;
                    this.form.description_kh = existingData.description_kh;
                    this.form.address_en = existingData.address_en;
                    this.form.address_kh = existingData.address_kh;
                    this.form.phone_number = existingData.phone_number;
                    this.form.note_kh = existingData.note_kh;
                    this.form.status = existingData.status;

                    // Handle Image Paths properly
                    if(existingData.logo) this.form.logo_preview = '/storage/' + existingData.logo;
                    if(existingData.fav) this.form.fav_preview = '/storage/' + existingData.fav;
                }
            },

            handleFileUpload(event, field) {
                const file = event.target.files[0];
                if (!file) return;

                // Simple validation (Optional)
                if (file.size > 2 * 1024 * 1024) { // 2MB
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: '{{ __('messages.error_file_size') }}' } }));
                    return;
                }

                this.form[field] = file;
                const reader = new FileReader();
                reader.onload = (e) => { this.form[field + '_preview'] = e.target.result; };
                reader.readAsDataURL(file);
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};

                let formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    // Don't send preview strings, only actual files or text
                    if (key !== 'logo_preview' && key !== 'fav_preview') {
                        if (this.form[key] !== null && this.form[key] !== undefined) {
                            formData.append(key, this.form[key]);
                        }
                    }
                });

                try {
                    const response = await fetch("{{ route('admin.shop_info.save') }}", {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json' 
                        },
                        body: formData
                    });
                    
                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.errors;
                            // Scroll to top to see errors
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: '{{ __('messages.error_check_input') }}' } }));
                        } else {
                            throw new Error(data.message || 'Something went wrong');
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    }
                } catch (error) { 
                    console.error(error); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: error.message || 'Connection Failed' } }));
                } 
                finally { this.isLoading = false; }
            }
        }
    }
</script>
@endsection