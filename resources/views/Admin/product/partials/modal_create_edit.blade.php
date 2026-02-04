<div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

    <div class="relative w-full max-w-2xl bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden flex flex-col max-h-[90vh]"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30">
            <div>
                <h3 class="text-lg font-bold text-text-color" x-text="editMode ? '{{ __('messages.edit') }} Product' : '{{ __('messages.create') }} Product'"></h3>
                <template x-if="isSequenceMode"><p class="text-xs text-primary font-bold mt-1">{{ __('messages.edit') }} <span x-text="currentSeqIndex + 1"></span> {{ __('messages.of') }} <span x-text="sequenceQueue.length"></span></p></template>
            </div>
            <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
        </div>
        
        {{-- Modal Body (Form) --}}
        <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Product Name --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.product_name') }}</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- Category: Live Search Dropdown --}}
                <div class="relative" x-data="categoryDropdown()" @click.outside="closeDropdown()">
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.category') }}</label>
                    <div class="relative">
                        <input type="text" x-model="searchQuery" @focus="openDropdown()" @input.debounce.300ms="fetchCategories(1)" placeholder="Select or search category..." class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" autocomplete="off">
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-secondary">
                            <i class="ri-arrow-down-s-line transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''"></i>
                        </div>
                    </div>
                    <input type="hidden" x-model="form.category_id">
                    
                    {{-- Dropdown Menu --}}
                    <div x-show="isOpen" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-border-color rounded-lg shadow-xl max-h-60 overflow-hidden flex flex-col">
                        <ul class="overflow-y-auto custom-scrollbar flex-1 p-1">
                            <template x-if="isLoading && page === 1"><li class="px-4 py-3 text-center text-sm text-secondary"><i class="ri-loader-4-line animate-spin"></i> Loading...</li></template>
                            <template x-if="!isLoading && categoriesList.length === 0"><li class="px-4 py-3 text-center text-sm text-secondary">No categories found.</li></template>
                            <template x-for="cat in categoriesList" :key="cat.id">
                                <li @click="selectCategory(cat)" class="px-3 py-2 cursor-pointer hover:bg-primary/5 rounded-md flex justify-between items-center group transition-colors" :class="form.category_id == cat.id ? 'bg-primary/5 text-primary font-bold' : 'text-text-color'">
                                    <div>
                                        <span x-text="cat.name"></span>
                                        <span class="text-[10px] ml-2 px-1.5 py-0.5 rounded border" :class="cat.destination ? 'bg-gray-100 dark:bg-gray-700 text-secondary' : 'hidden'">
                                            <i class="ri-printer-line mr-1"></i><span x-text="cat.destination ? cat.destination.name : ''"></span>
                                        </span>
                                    </div>
                                    <i class="ri-check-line text-primary" x-show="form.category_id == cat.id"></i>
                                </li>
                            </template>
                        </ul>
                        <div x-show="hasMorePages" class="border-t border-border-color p-2 bg-gray-50 dark:bg-gray-900/50 text-center shrink-0">
                            <button type="button" @click="loadMore()" class="text-xs font-bold text-primary hover:underline flex items-center justify-center gap-1 w-full py-1">
                                <span x-show="!isLoading">Load 10 More <i class="ri-arrow-down-s-line"></i></span>
                                <span x-show="isLoading"><i class="ri-loader-4-line animate-spin"></i> Loading...</span>
                            </button>
                        </div>
                    </div>
                    <p x-show="errors.category_id" x-text="errors.category_id" class="text-red-500 text-xs mt-1"></p>
                </div>
            </div>

            {{-- Price & Image --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.price') }} ($)</label>
                    <input type="number" step="0.01" x-model="form.price" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <p x-show="errors.price" x-text="errors.price" class="text-red-500 text-xs mt-1"></p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.image') }}</label>
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-lg bg-gray-100 border border-border-color overflow-hidden flex-shrink-0">
                            <template x-if="imagePreview"><img :src="imagePreview" class="w-full h-full object-cover"></template>
                            <template x-if="!imagePreview"><div class="w-full h-full flex items-center justify-center text-secondary"><i class="ri-image-add-line"></i></div></template>
                        </div>
                        <input type="file" @change="handleFileUpload" accept="image/*" class="text-sm text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    </div>
                </div>
            </div>

            {{-- ADDONS SELECTION --}}
            <div class="border-t border-border-color pt-4" x-show="visibleAddons.length > 0">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-bold text-text-color">Available Addons</label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-primary font-bold hover:text-primary/80">
                        <input type="checkbox" x-model="selectAllAddons" @change="toggleSelectAllAddons()" class="rounded border-input-border text-primary focus:ring-primary h-3.5 w-3.5"> Select All
                    </label>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-40 overflow-y-auto p-2 bg-page-bg/50 rounded-lg border border-input-border custom-scrollbar">
                    <template x-for="addon in visibleAddons" :key="addon.id">
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-white dark:hover:bg-white/5 p-2 rounded transition-colors border border-transparent hover:border-input-border">
                            <input type="checkbox" :value="addon.id" x-model="form.addons" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                            <div class="text-xs">
                                <span class="font-bold text-text-color block" x-text="addon.name"></span>
                                <span class="text-secondary" x-text="'+$' + parseFloat(addon.price).toFixed(2)"></span>
                            </div>
                        </label>
                    </template>
                </div>
                <p class="text-xs text-secondary mt-1">Filtering addons based on category destination.</p>
            </div>
            
            <div class="border-t border-border-color pt-4 text-center text-sm text-secondary italic" x-show="!form.category_id">Please select a category to see available addons.</div>
            <div class="border-t border-border-color pt-4 text-center text-sm text-secondary italic" x-show="form.category_id && visibleAddons.length === 0">No addons available for this category type.</div>
        </form>

        {{-- Modal Footer --}}
        <div class="p-6 pt-0 flex justify-between items-center border-t border-border-color mt-auto bg-card-bg z-10 pt-4">
            <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">{{ __('messages.skip_this_user') }} <i class="ri-arrow-right-line align-middle"></i></button>
            <div x-show="!isSequenceMode"></div> 
            <div class="flex gap-3">
                <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">{{ __('messages.cancel') }}</button>
                <button type="button" @click="submitForm" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                    <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                    <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? '{{ __('messages.finish') }}' : '{{ __('messages.save') }}') : (editMode ? '{{ __('messages.update') }}' : '{{ __('messages.save') }}')"></span>
                </button>
            </div>
        </div>
    </div>
</div>