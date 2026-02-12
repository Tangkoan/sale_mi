<div class="bg-transparent md:bg-white rounded-none md:rounded-2xl shadow-none md:shadow-sm border-none md:border border-gray-200 overflow-hidden">
    
    {{-- Header Title & Limit Selector --}}
    <div class="flex p-4 border-b border-gray-200 justify-between items-center bg-white rounded-t-2xl md:rounded-none">
        <h3 class="font-bold text-lg text-gray-800">
            {{ __('messages.transaction_history') }}
        </h3>

        {{-- Limit Dropdown --}}
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 hidden sm:inline">{{ __('messages.show') }}:</span>
            <select id="row-limit" onchange="updateLimit(this.value)" class="bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-1.5 w-20 cursor-pointer font-bold outline-none text-center">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="all">{{ __('messages.all') }}</option>
            </select>
        </div>
    </div>

    {{-- 1. DESKTOP TABLE VIEW --}}
    <div class="hidden md:block overflow-x-auto bg-white">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                <tr>
                    <th class="px-6 py-4">{{ __('messages.invoice') }}</th>
                    <th class="px-6 py-4">{{ __('messages.date') }}</th>
                    <th class="px-6 py-4">{{ __('messages.payment') }}</th>
                    <th class="px-6 py-4 text-center">{{ __('messages.status') }}</th>
                    <th class="px-6 py-4 text-right">{{ __('messages.amount') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-700" id="reportTableBody">
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">{{ __('messages.loading_data') }}</td></tr>
            </tbody>
        </table>
    </div>

    {{-- 2. MOBILE CARD VIEW --}}
    <div class="block md:hidden space-y-3 pb-20 pt-3" id="reportMobileBody"> 
        <div class="text-center py-8 text-gray-400">{{ __('messages.loading_data') }}</div>
    </div>
    
    {{-- Showing Info Footer --}}
    <div class="bg-white p-3 border-t border-gray-100 text-xs text-gray-400 text-center md:text-right">
        {{ __('messages.showing') }} <span id="showing-count" class="font-bold text-gray-600">0</span> {{ __('messages.of') }} ... {{ __('messages.transactions') }}
    </div>
</div>