@extends('admin.dashboard')

@section('content')

<div class="min-h-screen bg-gray-50/50 py-8" x-data="shopForm({{ json_encode($shop) }})">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/20">
                        <i class="ri-store-3-fill text-xl"></i>
                    </span>
                    Shop Configuration
                </h1>
                {{-- <p class="mt-1 text-sm text-gray-500 ml-14">គ្រប់គ្រងព័ត៌មានហាង និងការកំណត់ផ្សេងៗ</p> --}}
            </div>

            <button @click="submitForm" 
                class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 shadow-lg shadow-blue-600/30 transition-all duration-300 hover:-translate-y-0.5"
                :disabled="isLoading">
                <i x-show="isLoading" class="ri-loader-4-line animate-spin mr-2 text-lg"></i>
                <i x-show="!isLoading" class="ri-save-line mr-2 text-lg"></i>
                <span x-text="isLoading ? 'Saving...' : 'Save Changes'"></span>
            </button>
        </div>

        <form @submit.prevent="submitForm" class="relative">
            
            <div x-show="isLoading" 
                 x-transition.opacity
                 class="absolute inset-0 z-50 bg-white/50 backdrop-blur-[2px] rounded-3xl flex items-center justify-center"
                 style="display: none;">
                <div class="bg-white p-4 rounded-full shadow-xl">
                    <i class="ri-loader-2-line text-3xl text-blue-600 animate-spin"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                
                <div class="xl:col-span-8 space-y-8">
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-md transition-all duration-300">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <img src="https://flagcdn.com/w40/us.png" class="w-6 h-6 rounded-full shadow-sm object-cover">
                                English Details
                            </h3>
                            <span class="text-xs font-medium px-2 py-1 bg-gray-100 rounded text-gray-500">Default</span>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Shop Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                        <i class="ri-store-2-line"></i>
                                    </div>
                                    <input type="text" x-model="form.shop_en" 
                                           class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 bg-gray-50/50 focus:bg-white placeholder-gray-400"
                                           placeholder="Enter shop name">
                                </div>
                                <p x-show="errors.shop_en" x-text="errors.shop_en" class="text-red-500 text-xs mt-1.5 ml-1"></p>
                            </div>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                                    <div class="relative">
                                        <div class="absolute top-3 left-4 pointer-events-none text-gray-400">
                                            <i class="ri-map-pin-line"></i>
                                        </div>
                                        <textarea x-model="form.address_en" rows="2" 
                                                  class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 bg-gray-50/50 focus:bg-white resize-none"
                                                  placeholder="Full address..."></textarea>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                    <div class="relative">
                                        <div class="absolute top-3 left-4 pointer-events-none text-gray-400">
                                            <i class="ri-file-text-line"></i>
                                        </div>
                                        <textarea x-model="form.description_en" rows="3" 
                                                  class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 bg-gray-50/50 focus:bg-white resize-none"
                                                  placeholder="Brief description about your business..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-md transition-all duration-300">
                         <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <img src="https://flagcdn.com/w40/kh.png" class="w-6 h-6 rounded-full shadow-sm object-cover">
                                Khmer Details
                            </h3>
                            <span class="text-xs font-medium px-2 py-1 bg-blue-50 text-blue-600 rounded">Local</span>
                        </div>

                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">ឈ្មោះហាង (KH)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                        <i class="ri-store-2-line"></i>
                                    </div>
                                    <input type="text" x-model="form.shop_kh" 
                                           class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 bg-gray-50/50 focus:bg-white font-khmer"
                                           placeholder="ឈ្មោះហាងជាភាសាខ្មែរ">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">អាសយដ្ឋាន (KH)</label>
                                    <textarea x-model="form.address_kh" rows="2" 
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 bg-gray-50/50 focus:bg-white resize-none font-khmer"
                                              placeholder="អាសយដ្ឋានហាង..."></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">អំពីហាង (KH)</label>
                                    <textarea x-model="form.description_kh" rows="3" 
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 bg-gray-50/50 focus:bg-white resize-none font-khmer"
                                              placeholder="សរសេរអំពីហាង..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">សម្គាល់ (KH)</label>
                                    <div class="relative h-full">
                                         <textarea x-model="form.note_kh" rows="3" 
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 bg-gray-50/50 focus:bg-white resize-none font-khmer"
                                              placeholder="ដាក់នៅខាងក្រោមវិក្កយបត្រ..."></textarea>
                                        <p class="text-xs text-gray-400 mt-1 absolute bottom-2 right-2">For Receipt Footer</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4 space-y-8">
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300">
                        <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
                            <i class="ri-settings-4-fill text-blue-600"></i> General Settings
                        </h3>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                        <i class="ri-phone-fill"></i>
                                    </div>
                                    <input type="text" x-model="form.phone_number" 
                                           class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-gray-700 font-mono"
                                           placeholder="012 345 678">
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-bold text-gray-700">Shop Status</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="form.status" class="sr-only peer" :true-value="1" :false-value="0">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500" x-text="form.status == 1 ? 'Your shop is currently Visible to public.' : 'Your shop is Hidden from public.'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300">
                        <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2">
                            <i class="ri-palette-fill text-purple-500"></i> Branding
                        </h3>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Main Logo</label>
                            <div class="relative group w-full aspect-video bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300 hover:border-blue-500 hover:bg-blue-50/50 transition-all flex flex-col items-center justify-center cursor-pointer overflow-hidden">
                                
                                <template x-if="form.logo_preview">
                                    <img :src="form.logo_preview" class="w-full h-full object-contain p-4">
                                </template>
                                
                                <template x-if="!form.logo_preview">
                                    <div class="flex flex-col items-center text-gray-400 group-hover:text-blue-500 transition-colors">
                                        <i class="ri-image-add-line text-4xl mb-2"></i>
                                        <span class="text-xs font-semibold">Click to upload</span>
                                    </div>
                                </template>

                                <input type="file" @change="handleFileUpload($event, 'logo')" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                
                                <div x-show="form.logo_preview" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20 pointer-events-none">
                                    <span class="text-white text-xs font-bold bg-white/20 px-3 py-1.5 rounded-full backdrop-blur-md border border-white/30">Change Logo</span>
                                </div>
                            </div>
                            <p class="text-xs text-center text-gray-400 mt-2">Recommended: 500x500px .PNG</p>
                        </div>

                        <div class="border-t border-gray-100 pt-5">
                            <div class="flex items-start gap-4">
                                <div class="relative group w-16 h-16 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 hover:border-blue-500 transition-all flex items-center justify-center cursor-pointer overflow-hidden flex-shrink-0">
                                    <template x-if="form.fav_preview">
                                        <img :src="form.fav_preview" class="w-8 h-8 object-contain">
                                    </template>
                                    <template x-if="!form.fav_preview">
                                        <i class="ri-upload-cloud-2-line text-gray-400 text-xl"></i>
                                    </template>
                                    <input type="file" @change="handleFileUpload($event, 'fav')" class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Favicon</label>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Small icon shown in browser tabs.</p>
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
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'File size too large (Max 2MB)' } }));
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
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Please check the required fields.' } }));
                        } else {
                            throw new Error(data.message || 'Something went wrong');
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Shop configuration saved successfully!' } }));
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