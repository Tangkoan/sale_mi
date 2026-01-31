@extends('layouts.blank')

@section('content')
<div x-data="kitchenDisplay()" x-init="init()" class="min-h-screen bg-gray-900 text-white font-sans flex flex-col overflow-hidden">
    
    {{-- =========================================== --}}
    {{-- 1. HEADER & CONTROLS                        --}}
    {{-- =========================================== --}}
    <div class="h-14 sm:h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-3 sm:px-6 shrink-0 z-20 shadow-md gap-2">
        
        {{-- Title & Clock --}}
        <div class="flex items-center gap-3 sm:gap-6 flex-1 min-w-0">
            <a href="{{ route('pos.tables') }}" class="text-gray-400 hover:text-white transition shrink-0">
                <i class="ri-arrow-left-line text-lg sm:text-xl"></i>
            </a>
            <h1 class="text-base sm:text-xl font-bold tracking-wide uppercase flex items-center gap-2 truncate">
                <i class="ri-fire-line text-orange-500"></i> <span class="hidden xs:inline">KDS System</span>
            </h1>
            <div class="h-4 sm:h-6 w-px bg-gray-600 hidden xs:block"></div>
            <span class="text-lg sm:text-2xl font-mono font-bold text-blue-400" x-text="currentTime"></span>
        </div>

        {{-- ✅ Destination Toggle (Dynamic Loop) --}}
        <div class="flex bg-gray-900 p-0.5 sm:p-1 rounded-lg border border-gray-700 shrink-0 overflow-x-auto custom-scrollbar max-w-[200px] sm:max-w-md">
            @foreach($destinations as $dest)
                <button @click="changeMode({{ $dest->id }})" 
                        class="px-3 sm:px-6 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-bold transition-all flex items-center gap-1 sm:gap-2 whitespace-nowrap"
                        :class="currentDestinationId == {{ $dest->id }} ? 'bg-primary text-white shadow-lg' : 'text-gray-400 hover:text-white'">
                    
                    {{-- Icon Logic based on name (Optional) --}}
                    @if(stripos($dest->name, 'bar') !== false || stripos($dest->name, 'drink') !== false)
                        <i class="ri-goblet-line"></i>
                    @else
                        <i class="ri-restaurant-line"></i>
                    @endif
                    
                    <span class="hidden sm:inline">{{ $dest->name }}</span>
                </button>
            @endforeach
        </div>

        {{-- Status --}}
        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
            <span class="relative flex h-2.5 w-2.5 sm:h-3 sm:w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" 
                    :class="isLoading ? 'bg-blue-400' : 'bg-green-400'"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 sm:h-3 sm:w-3" 
                    :class="isLoading ? 'bg-blue-500' : 'bg-green-500'"></span>
            </span>
            <span class="text-[10px] sm:text-xs text-gray-400 font-mono hidden sm:inline" x-text="isLoading ? 'Fetching...' : 'Live'"></span>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 2. ORDERS GRID AREA                         --}}
    {{-- =========================================== --}}
    <div class="flex-1 overflow-y-auto p-2 sm:p-4 custom-scrollbar">
        
        {{-- Loading State --}}
        <div x-show="isLoading && orders.length === 0" class="flex h-full items-center justify-center">
            <i class="ri-loader-4-line animate-spin text-3xl sm:text-4xl text-gray-600"></i>
        </div>

        {{-- Empty State --}}
        <div x-show="!isLoading && orders.length === 0" class="flex flex-col h-full items-center justify-center text-gray-600 opacity-50" x-cloak>
            <i class="ri-checkbox-circle-line text-5xl sm:text-6xl mb-3 sm:mb-4"></i>
            <p class="text-xl sm:text-2xl font-bold">All Orders Completed</p>
            <p class="text-xs sm:text-sm">Waiting for new orders...</p>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4" x-show="orders.length > 0" x-cloak>
            <template x-for="order in orders" :key="order.id">
                
                {{-- ORDER TICKET CARD --}}
                <div class="flex flex-col bg-gray-800 border border-gray-700 rounded-lg sm:rounded-xl shadow-lg overflow-hidden h-fit transition-all duration-300 hover:border-gray-500 relative group">
                    
                    {{-- Ticket Header --}}
                    <div class="p-2 sm:p-3 flex justify-between items-start border-b border-gray-700"
                         :class="getOrderDuration(order.created_at) > 20 ? 'bg-red-900/30' : (getOrderDuration(order.created_at) > 10 ? 'bg-yellow-900/20' : 'bg-gray-800')">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-white leading-none" x-text="order.table ? order.table.name : 'Unknown'"></h2>
                            <p class="text-[10px] sm:text-xs text-gray-400 mt-1" x-text="'#' + order.invoice_number"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg sm:text-xl font-mono font-bold" 
                                  :class="getOrderDuration(order.created_at) > 20 ? 'text-red-400 animate-pulse' : 'text-green-400'"
                                  x-text="formatTimeAgo(order.created_at)"></span>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div class="p-2 space-y-1.5 sm:space-y-2 flex-1">
                        <template x-for="item in order.items" :key="item.id">
                            
                            {{-- Single Item --}}
                            <div class="flex flex-col p-1.5 sm:p-2 rounded-md sm:rounded-lg transition-colors border border-transparent"
                                 :class="item.status === 'ready' ? 'bg-green-900/20 border-green-900/50 opacity-50' : 'bg-gray-700/50 hover:bg-gray-700'">
                                
                                <div class="flex justify-between items-start gap-2">
                                    {{-- Qty --}}
                                    <span class="bg-gray-200 text-gray-900 font-black text-base sm:text-lg px-1.5 sm:px-2 rounded min-w-[1.8rem] sm:min-w-[2rem] text-center shrink-0 h-6 sm:h-7 leading-6 sm:leading-7" x-text="item.quantity"></span>
                                    
                                    {{-- Name & Details --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base sm:text-lg font-bold leading-tight" 
                                           :class="item.status === 'ready' ? 'line-through text-gray-500' : 'text-white'"
                                           x-text="item.product ? item.product.name : 'Product Deleted'"></p>
                                    
                                        {{-- Addons --}}
                                        <template x-if="item.addons && item.addons.length > 0">
                                            <div class="mt-0.5 sm:mt-1 pl-2 border-l-2 border-gray-600">
                                                <template x-for="ad in item.addons">
                                                    <p class="text-xs sm:text-sm text-gray-300">
                                                        + <span x-text="ad.addon ? ad.addon.name : 'Unknown'"></span> 
                                                        <span class="text-[10px] sm:text-xs font-mono text-gray-500" x-text="'x' + (ad.quantity || 1)"></span>
                                                    </p>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Note (Important) --}}
                                        <template x-if="item.note">
                                            <div class="mt-1 sm:mt-2 bg-red-900/40 text-red-200 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded text-xs sm:text-sm font-bold border border-red-900/50 animate-pulse w-fit">
                                                <i class="ri-message-2-fill"></i> <span x-text="item.note"></span>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Item Action (Done) --}}
                                    <button @click="markItemReady(item.id)" 
                                            x-show="item.status !== 'ready'"
                                            class="h-8 w-8 sm:h-10 sm:w-10 bg-gray-600 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-colors shrink-0">
                                        <i class="ri-check-line text-lg sm:text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Card Footer --}}
                    <div class="p-2 sm:p-3 border-t border-gray-700 bg-gray-800/50">
                        <button @click="markOrderReady(order.id)" 
                                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 sm:py-3 rounded-lg shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2 text-sm sm:text-base">
                            <span>Done All</span>
                            <i class="ri-check-double-line"></i>
                        </button>
                    </div>

                </div>
            </template>
        </div>
    </div>
</div>

{{-- SCRIPT --}}
<script>
    function kitchenDisplay() {
        return {
            orders: [],
            
            // ✅ កែ៖ កំណត់ ID ដោយឆែកមើលក្នុង LocalStorage មុន
            // បើមានក្នុង LocalStorage យកអាហ្នឹង, បើអត់មានចាំយកពី PHP ($destinations->first())
            currentDestinationId: localStorage.getItem('kitchen_active_tab') 
                ? parseInt(localStorage.getItem('kitchen_active_tab')) 
                : {{ $destinations->first()->id ?? 0 }}, 

            isLoading: false,
            currentTime: '',
            timerInterval: null,
            pollingInterval: null,

            init() {
                this.updateClock();
                this.timerInterval = setInterval(() => this.updateClock(), 1000);
                
                // Fetch ភ្លាមៗបើមាន ID
                if(this.currentDestinationId !== 0) {
                    this.fetchOrders();
                    // Poll រៀងរាល់ 5 វិនាទី
                    this.pollingInterval = setInterval(() => this.fetchOrders(), 5000);
                }
            },

            updateClock() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            changeMode(newId) {
                this.currentDestinationId = newId;
                
                // ✅ បន្ថែម៖ ពេលចុចប្តូរ Tab ត្រូវ Save ID ទុកក្នុង LocalStorage ភ្លាម
                localStorage.setItem('kitchen_active_tab', newId);

                this.orders = []; 
                this.fetchOrders();
            },

            async fetchOrders() {
                // បើគ្មាន Destination ID មិនបាច់ Fetch
                if(this.currentDestinationId === 0) return;

                // កុំបង្ហាញ Loading ញឹកពេកបើមាន Data ហើយ (Optional improvement)
                if(this.orders.length === 0) this.isLoading = true;
                
                try {
                    const response = await fetch(`{{ route('pos.kitchen.fetch') }}?kitchen_destination_id=${this.currentDestinationId}`);
                    const data = await response.json();
                    this.orders = data;
                } catch (error) {
                    console.error("Connection lost:", error);
                } finally {
                    this.isLoading = false;
                }
            },

            async markItemReady(itemId) {
                try {
                    const response = await fetch("{{ route('pos.kitchen.update_item') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ item_id: itemId, status: 'ready' })
                    });
                    if (response.ok) {
                        // Optimistic Update
                        this.orders.forEach(order => {
                            const item = order.items.find(i => i.id === itemId);
                            if(item) item.status = 'ready';
                        });
                        this.fetchOrders(); 
                    }
                } catch (e) { console.error(e); }
            },

            async markOrderReady(orderId) {
                if(!confirm('Mark entire order as ready?')) return;
                try {
                    const response = await fetch("{{ route('pos.kitchen.done_all') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ order_id: orderId, kitchen_destination_id: this.currentDestinationId })
                    });
                    if (response.ok) {
                        this.orders = this.orders.filter(o => o.id !== orderId);
                        this.fetchOrders();
                    }
                } catch (e) { console.error(e); }
            },

            getOrderDuration(createdAt) {
                // 1. បម្លែងម៉ោងពី Database (String) ទៅជា Date Object
                // Laravel តែងតែផ្ញើ format មកត្រឹមត្រូវ ប៉ុន្តែយើងត្រូវប្រាកដថា JS ស្គាល់
                const start = new Date(createdAt);
                const now = new Date();

                // 2. រកគម្លាត (Difference) គិតជា Milliseconds
                let diffMs = now - start;

                // 3. បើសិនគម្លាតអវិជ្ជមាន (ដោយសារម៉ោង Server លឿនជាង Client បន្តិចបន្តួច)
                // យើងកំណត់វាអោយស្មើ 0 ដើម្បីកុំអោយចេញលេខ -1m
                if (diffMs < 0) diffMs = 0;

                // 4. បម្លែងទៅជានាទី (1 នាទី = 60000ms)
                return Math.floor(diffMs / 60000); 
            },

            formatTimeAgo(createdAt) {
                const mins = this.getOrderDuration(createdAt);

                // បើតិចជាង 1 នាទី បង្ហាញ "Just now"
                if (mins < 1) return 'Just now';

                // បើលើសពី 60 នាទី បង្ហាញជា ម៉ោង និង នាទី (ឧ. 1h 30m)
                if (mins >= 60) {
                    const h = Math.floor(mins / 60);
                    const m = mins % 60;
                    return h + 'h ' + m + 'm';
                }

                // ធម្មតា បង្ហាញតែនាទី (ឧ. 10m)
                return mins + 'm';
            }
        }
    }
</script>
@endsection