{{-- =========================================== --}}
{{-- CONFIRM MODAL (Custom)                      --}}
{{-- =========================================== --}}
<div x-show="modal.show" 
        style="display: none;"
        class="fixed inset-0 z-[50] flex items-center justify-center px-4" x-cloak>
    
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity" 
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="closeModal()"></div>

    {{-- Modal Content --}}
    <div class="relative bg-gray-800 rounded-xl shadow-2xl border border-gray-600 max-w-sm w-full p-6 text-center transform transition-all"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4">
        
        <div class="w-16 h-16 bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-question-mark text-3xl text-blue-400"></i>
        </div>
        
        <h3 class="text-xl font-bold text-white mb-2">Are you sure?</h3>
        <p class="text-gray-400 text-sm mb-6" x-text="modal.message"></p>

        <div class="flex gap-3 justify-center">
            <button @click="closeModal()" 
                    class="px-5 py-2.5 rounded-lg border border-gray-600 text-gray-300 hover:bg-gray-700 transition font-bold text-sm">
                Cancel
            </button>
            <button @click="confirmAction()" 
                    class="px-5 py-2.5 rounded-lg bg-primary text-white shadow-lg shadow-blue-900/50 transition font-bold text-sm flex items-center gap-2">
                <i class="ri-check-line"></i> Confirm
            </button>
        </div>
    </div>
</div>