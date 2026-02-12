<div class="bg-transparent md:bg-card-bg rounded-none md:rounded-2xl shadow-none md:shadow-custom border-none md:border border-bor-color overflow-hidden">
    
    {{-- Header Title & Limit Selector --}}
    <div class="flex p-4 border-b border-bor-color justify-between items-center bg-card-bg rounded-t-2xl md:rounded-none">
        <h3 class="font-bold text-lg text-sidebar-text">
            {{ __('messages.transaction_history') }}
        </h3>

        {{-- Limit Dropdown --}}
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 hidden sm:inline">{{ __('messages.show') }}:</span>
            <select id="row-limit" onchange="updateLimit(this.value)" class="bg-input-bg border border-input-border text-sidebar-text text-xs rounded-lg focus:ring-primary focus:border-primary block p-1.5 w-20 cursor-pointer font-bold outline-none text-center">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="all">{{ __('messages.all') }}</option>
            </select>
        </div>
    </div>

    {{-- 1. DESKTOP TABLE VIEW --}}
    <div class="hidden md:block overflow-x-auto bg-card-bg">
        <table class="w-full text-left border-collapse">
            <thead class="bg-page-bg text-gray-500 text-xs uppercase font-bold tracking-wider border-b border-bor-color">
                <tr>
                    <th class="px-6 py-4">{{ __('messages.invoice') }}</th>
                    <th class="px-6 py-4">{{ __('messages.date') }}</th>
                    <th class="px-6 py-4">{{ __('messages.payment') }}</th>
                    <th class="px-6 py-4 text-center">{{ __('messages.status') }}</th>
                    <th class="px-6 py-4 text-right">{{ __('messages.amount') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bor-color text-sm text-sidebar-text" id="reportTableBody">
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">{{ __('messages.loading_data') }}</td></tr>
            </tbody>
        </table>
    </div>

    {{-- 2. MOBILE CARD VIEW --}}
    {{-- រក្សាទុក pb-2 ដើម្បីកុំអោយឃ្លាតពី Footer ពេក --}}
    <div class="block md:hidden space-y-3 pb-2 pt-3" id="reportMobileBody"> 
        <div class="text-center py-8 text-gray-400">{{ __('messages.loading_data') }}</div>
    </div>
    
    {{-- Showing Info Footer --}}
    <div class="bg-card-bg p-3 border-t border-bor-color text-xs text-gray-400 text-center md:text-right rounded-b-xl md:rounded-none">
        {{ __('messages.showing') }} <span id="showing-count" class="font-bold text-sidebar-text">0</span> {{ __('messages.of') }} ... {{ __('messages.transactions') }}
    </div>
</div>