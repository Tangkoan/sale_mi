{{-- Order Detail Modal --}}
<div id="orderDetailModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

    {{-- Modal Panel --}}
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-0 md:p-4 text-center sm:items-center sm:p-0">
            
            <div class="relative transform overflow-hidden rounded-t-2xl md:rounded-2xl bg-card-bg text-left shadow-xl transition-all w-full md:max-w-lg translate-y-full md:translate-y-0 opacity-0" id="modalPanel">
                
                {{-- Header --}}
                <div class="bg-page-bg px-4 py-3 border-b border-bor-color flex justify-between items-center">
                    <h3 class="text-lg font-bold text-sidebar-text" id="modal-title">
                        {{ __('messages.order_details') }} <span id="modalInvoice" class="text-primary">#...</span>
                    </h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-4 py-5 space-y-4">
                    {{-- Info Grid --}}
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">{{ __('messages.date') }}</span>
                            <span class="font-bold text-sidebar-text" id="modalDate">...</span>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs text-gray-500 uppercase">{{ __('messages.status') }}</span>
                            <span id="modalStatus" class="font-bold">...</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">{{ __('messages.payment') }}</span>
                            <span class="font-bold text-sidebar-text capitalize" id="modalPayment">...</span>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs text-gray-500 uppercase">{{ __('messages.cashier') }}</span>
                            <span class="font-bold text-sidebar-text" id="modalCashier">Admin</span>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-dashed border-bor-color"></div>

                    {{-- Items List --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">{{ __('messages.items') }}</h4>
                        <div class="space-y-2 max-h-40 overflow-y-auto pr-1" id="modalItemsList">
                            <div class="text-center text-gray-400 text-xs py-4">No items detail available</div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-dashed border-bor-color"></div>

                    {{-- Totals --}}
                    <div class="space-y-1 pt-1">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">{{ __('messages.subtotal') }}</span>
                            <span class="font-bold text-sidebar-text" id="modalTotal">...</span>
                        </div>
                        <div class="flex justify-between text-lg font-black text-primary border-t border-bor-color pt-2 mt-2">
                            <span>{{ __('messages.grand_total') }}</span>
                            <span id="modalGrandTotal">...</span>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-page-bg px-4 py-3 sm:flex sm:flex-row-reverse border-t border-bor-color">
                    <button type="button" onclick="closeModal()" class="inline-flex w-full justify-center rounded-xl bg-card-bg px-3 py-3 text-sm font-bold text-sidebar-text shadow-sm border border-bor-color hover:bg-input-bg sm:mt-0 sm:w-auto">
                        {{ __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>