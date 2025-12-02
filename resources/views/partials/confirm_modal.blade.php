<div x-data="{ 
        open: false, 
        callback: null,
        title: 'Do you want to delete this data?',
        message: 'This Action can not Recover!!!',
        
        init() {
            window.askConfirm = (actionCallback) => {
                this.callback = actionCallback;
                this.open = true;
            }
        },

        confirm() {
            if (this.callback) {
                this.callback();
            }
            this.close();
        },

        close() {
            this.open = false;
            setTimeout(() => { this.callback = null }, 300);
        }
    }"
    @keydown.escape.window="close()"
    x-show="open"
    style="display: none;"
    class="relative z-[200]" 
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true">

    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            <div x-show="open"
                 @click.away="close()"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>

                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-bold leading-6 text-text-color" x-text="title"></h3>
                            <div class="mt-2">
                                <p class="text-sm text-secondary" x-text="message"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                    
                    <button type="button" 
                            @click="confirm()" 
                            class="inline-flex w-full justify-center rounded-xl bg-primary px-3 py-2 text-sm font-bold text-white shadow-lg shadow-primary/30 hover:opacity-90 sm:w-auto transition-all">
                        Yes, Confirm!
                    </button>
                    
                    <button type="button" 
                            @click="close()" 
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-red-500 text-white px-3 py-2 text-sm font-bold  shadow-sm ring-1 ring-inset ring-input-border  sm:mt-0 sm:w-auto transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>