<div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

    <div class="relative w-full max-w-md bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
            x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
            x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
            <div>
                <h3 class="text-lg font-bold text-text-color" x-text="editMode ? '{{ __('messages.edit') }} Addon' : '{{ __('messages.create') }} Addon'"></h3>
                <template x-if="isSequenceMode">
                    <p class="text-xs text-primary font-bold mt-1">
                        {{ __('messages.edit') }} <span x-text="currentSeqIndex + 1"></span> {{ __('messages.of') }} <span x-text="sequenceQueue.length"></span>
                    </p>
                </template>
            </div>
            <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
        </div>
        
        <form @submit.prevent="submitForm" class="p-6 space-y-4">
            
            {{-- Name --}}
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.addon_name') }}</label>
                <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="Ex: Extra Shot">
                <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
            </div>

            {{-- Price --}}
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.price') }} ($)</label>
                <input type="number" step="0.01" x-model="form.price" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="0.00">
                <p x-show="errors.price" x-text="errors.price" class="text-red-500 text-xs mt-1"></p>
            </div>

            {{-- Destination Dropdown --}}
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