<div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

    <div class="relative w-full max-w-lg bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden flex flex-col max-h-[90vh]"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30">
            <div>
                <h3 class="text-lg font-bold text-text-color" x-text="editMode ? 'កែប្រែទិន្នន័យ' : 'បង្កើតក្រុមជម្រើសថ្មី'"></h3>
                <template x-if="isSequenceMode"><p class="text-xs text-primary font-bold mt-1">កែប្រែទី <span x-text="currentSeqIndex + 1"></span> ក្នុងចំណោម <span x-text="sequenceQueue.length"></span></p></template>
            </div>
            <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
        </div>
        
        <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar">
            
            <div>
                <label class="block text-sm font-bold text-text-color mb-1">ឈ្មោះក្រុម <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.name" placeholder="ឧ. កម្រិតជាតិស្ករ, កម្រិតទឹកកក" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
            </div>

            <div>
                <label class="block text-sm font-bold text-text-color mb-1">ប្រភេទជម្រើស <span class="text-red-500">*</span></label>
                <select x-model="form.type" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <option value="single">រើសបានតែមួយគត់ (Radio Button)</option>
                    <option value="multiple">រើសបានច្រើន (Checkbox)</option>
                </select>
                <p x-show="errors.type" x-text="errors.type" class="text-red-500 text-xs mt-1"></p>
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer p-3 border border-border-color rounded-lg bg-page-bg/50 hover:bg-page-bg transition">
                    <input type="checkbox" x-model="form.is_required" class="w-5 h-5 rounded border-input-border text-primary focus:ring-primary">
                    <div>
                        <span class="block text-sm font-bold text-text-color">តម្រូវឲ្យជ្រើសរើស (Required)</span>
                        <span class="block text-xs text-secondary mt-0.5">បើធីក ភ្ញៀវមិនអាចបន្តបានទេបើមិនទាន់រើសជម្រើសក្នុងក្រុមនេះ</span>
                    </div>
                </label>
            </div>
            
        </form>

        <div class="p-6 pt-0 flex justify-between items-center border-t border-border-color mt-auto bg-card-bg z-10 pt-4">
            <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">រំលង <i class="ri-arrow-right-line align-middle"></i></button>
            <div x-show="!isSequenceMode"></div> 
            <div class="flex gap-3">
                <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition font-bold">បោះបង់</button>
                <button type="button" @click="submitForm" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2 font-bold shadow-md shadow-primary/30" :disabled="isLoading">
                    <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                    <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? 'បញ្ចប់' : 'រក្សាទុក & បន្ត') : (editMode ? 'កែប្រែ' : 'បង្កើតថ្មី')"></span>
                </button>
            </div>
        </div>
    </div>
</div>