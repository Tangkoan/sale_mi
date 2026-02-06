{{-- =========================================== --}}
{{-- TOAST NOTIFICATION (Custom)                 --}}
{{-- =========================================== --}}
<div class="fixed top-5 right-5 z-[60] flex flex-col gap-2 pointer-events-none">
    <div x-show="toast.show" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-10"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-10"
            class="pointer-events-auto min-w-[250px] px-4 py-3 rounded-lg shadow-2xl flex items-center gap-3 border"
            :class="toast.type === 'success' ? 'bg-gray-800 border-green-500 text-green-400' : 'bg-gray-800 border-red-500 text-red-400'"
            style="display: none;">
        
        <i class="text-xl" :class="toast.type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'"></i>
        <div>
            <h4 class="font-bold text-sm" x-text="toast.type === 'success' ? 'Success' : 'Error'"></h4>
            <p class="text-xs text-gray-300" x-text="toast.message"></p>
        </div>
    </div>
</div>