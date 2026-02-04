<div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

    <div class="relative w-full max-w-md bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
            x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
            x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
            <div>
                <h3 class="text-lg font-bold text-text-color" x-text="editMode ? '{{ __('messages.edit') }} Category' : '{{ __('messages.create') }} Category'"></h3>
                <template x-if="isSequenceMode">
                    <p class="text-xs text-primary font-bold mt-1">
                        {{ __('messages.edit') }} <span x-text="currentSeqIndex + 1"></span> {{ __('messages.of') }} <span x-text="sequenceQueue.length"></span>
                    </p>
                </template>
            </div>
            <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
        </div>
        
        <form @submit.prevent="submitForm" class="p-6 space-y-5">
            {{-- Name --}}
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.category_name') }}</label>
                <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
            </div>

            {{-- Destination --}}
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">Destination</label>
                <div class="relative">
                    <select x-model="form.kitchen_destination_id" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none appearance-none">
                        <option value="">Select Destination</option>
                        <template x-for="dest in destinations" :key="dest.id">
                            <option :value="dest.id" x-text="dest.name"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <i class="ri-arrow-down-s-line text-secondary"></i>
                    </div>
                </div>
                <p x-show="errors.kitchen_destination_id" x-text="errors.kitchen_destination_id" class="text-red-500 text-xs mt-1"></p>
            </div>

            {{-- Image --}}
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.image') }}</label>
                <div class="flex items-center gap-4 p-3 border border-dashed border-input-border rounded-xl bg-page-bg/30">
                    <div class="h-16 w-16 rounded-lg bg-gray-100 border border-border-color overflow-hidden flex-shrink-0 relative group">
                        <template x-if="imagePreview">
                            <img :src="imagePreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!imagePreview">
                            <div class="w-full h-full flex items-center justify-center text-secondary bg-white"><i class="ri-image-add-line text-2xl"></i></div>
                        </template>
                    </div>
                    <div class="flex-1">
                        <input type="file" @change="handleFileUpload" accept="image/*" class="block w-full text-sm text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer">
                        <p class="text-[10px] text-secondary mt-1">Supported: JPEG, PNG, JPG (Max 2MB)</p>
                    </div>
                </div>
                <p x-show="errors.image" x-text="errors.image" class="text-red-500 text-xs mt-1"></p>
            </div>

            {{-- Footer --}}
            <div class="pt-4 flex justify-between items-center border-t border-border-color mt-2">
                <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">
                    {{ __('messages.skip_this_user') }} <i class="ri-arrow-right-line align-middle"></i>
                </button>
                <div x-show="!isSequenceMode"></div> 

                <div class="flex gap-3">
                    <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2 shadow-lg shadow-primary/30" :disabled="isLoading">
                        <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                        <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? '{{ __('messages.finish') }}' : '{{ __('messages.save_and_next') }}') : (editMode ? '{{ __('messages.update') }}' : '{{ __('messages.save') }}')"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>