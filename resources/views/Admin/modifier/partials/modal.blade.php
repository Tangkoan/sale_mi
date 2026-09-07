<div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

    <div class="relative w-full max-w-lg bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden flex flex-col max-h-[90vh]"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30">
            <div>
                <h3 class="text-lg font-bold text-text-color" x-text="editMode ? 'កែប្រែជម្រើស' : 'បន្ថែមជម្រើសថ្មី'"></h3>
                <template x-if="isSequenceMode">
                    <p class="text-xs text-primary font-bold mt-1">កែប្រែទី <span x-text="currentSeqIndex + 1"></span> ក្នុងចំណោម <span x-text="sequenceQueue.length"></span></p>
                </template>
            </div>
            <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
        </div>
        
        <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar">
            
            {{-- Select Group --}}
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">ជ្រើសរើសក្រុម <span class="text-red-500">*</span></label>
                <select x-model="form.modifier_group_id" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <option value="">-- សូមជ្រើសរើស --</option>
                    <template x-for="g in groups" :key="g.id">
                        <option :value="g.id" x-text="g.name"></option>
                    </template>
                </select>
                <p x-show="errors.modifier_group_id" x-text="errors.modifier_group_id" class="text-red-500 text-xs mt-1"></p>
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">ឈ្មោះជម្រើសលម្អិត (ឧ. ៥០%, កែវធំ L) <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
            </div>

            {{-- Price --}}
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">តម្លៃបូកថែម (៛) <span class="text-gray-400 font-normal">(ទុក 0 បើឥតគិតថ្លៃ)</span></label>
                <input type="number" step="100" min="0" x-model="form.price" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                <p x-show="errors.price" x-text="errors.price" class="text-red-500 text-xs mt-1"></p>
            </div>
            
        </form>

        <div class="p-6 pt-0 flex justify-between items-center border-t border-border-color mt-auto bg-card-bg z-10 pt-4">
            <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">រំលង <i class="ri-arrow-right-line align-middle"></i></button>
            <div x-show="!isSequenceMode"></div> 
            <div class="flex gap-3">
                <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition font-bold">បោះបង់</button>
                <button type="button" @click="submitForm" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2 font-bold shadow-md shadow-primary/30" :disabled="isLoading">
                    <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                    <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? 'បញ្ចប់' : 'រក្សាទុក & បន្ត') : (editMode ? 'កែប្រែ' : 'រក្សាទុក')"></span>
                </button>
            </div>
        </div>
    </div>
</div>