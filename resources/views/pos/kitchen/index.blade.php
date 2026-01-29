@extends('layouts.blank')

@section('content')
<div x-data="kitchenDisplay()" x-init="init()" class="min-h-screen bg-gray-900 text-white font-sans flex flex-col overflow-hidden">
    
    {{-- =========================================== --}}
    {{-- 1. HEADER & CONTROLS                        --}}
    {{-- =========================================== --}}
    <div class="h-16 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-6 shrink-0 z-20 shadow-md">
        
        {{-- Title & Clock --}}
        <div class="flex items-center gap-6">
            <a href="{{ route('pos.tables') }}" class="text-gray-400 hover:text-white transition">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <h1 class="text-xl font-bold tracking-wide uppercase flex items-center gap-2">
                <i class="ri-fire-line text-orange-500"></i> KDS System
            </h1>
            <div class="h-6 w-px bg-gray-600"></div>
            <span class="text-2xl font-mono font-bold text-blue-400" x-text="currentTime"></span>
        </div>

        {{-- Destination Toggle (Kitchen vs Bar) --}}
        <div class="flex bg-gray-900 p-1 rounded-lg border border-gray-700">
            <button @click="changeMode('kitchen')" 
                    class="px-6 py-2 rounded-md text-sm font-bold transition-all flex items-center gap-2"
                    :class="mode === 'kitchen' ? 'bg-orange-600 text-white shadow-lg' : 'text-gray-400 hover:text-white'">
                <i class="ri-restaurant-line"></i> Kitchen
            </button>
            <button @click="changeMode('bar')" 
                    class="px-6 py-2 rounded-md text-sm font-bold transition-all flex items-center gap-2"
                    :class="mode === 'bar' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-white'">
                <i class="ri-goblet-line"></i> Bar
            </button>
        </div>

        {{-- Status --}}
        <div class="flex items-center gap-2">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" 
                    :class="isLoading ? 'bg-blue-400' : 'bg-green-400'"></span>
              <span class="relative inline-flex rounded-full h-3 w-3" 
                    :class="isLoading ? 'bg-blue-500' : 'bg-green-500'"></span>
            </span>
            <span class="text-xs text-gray-400 font-mono" x-text="isLoading ? 'Fetching...' : 'Live'"></span>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 2. ORDERS GRID AREA                         --}}
    {{-- =========================================== --}}
    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
        
        {{-- Loading State --}}
        <div x-show="isLoading && orders.length === 0" class="flex h-full items-center justify-center">
            <i class="ri-loader-4-line animate-spin text-4xl text-gray-600"></i>
        </div>

        {{-- Empty State --}}
        <div x-show="!isLoading && orders.length === 0" class="flex flex-col h-full items-center justify-center text-gray-600 opacity-50" x-cloak>
            <i class="ri-checkbox-circle-line text-6xl mb-4"></i>
            <p class="text-2xl font-bold">All Orders Completed</p>
            <p class="text-sm">Waiting for new orders...</p>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4" x-show="orders.length > 0" x-cloak>
            <template x-for="order in orders" :key="order.id">
                
                {{-- ORDER TICKET CARD --}}
                <div class="flex flex-col bg-gray-800 border border-gray-700 rounded-xl shadow-lg overflow-hidden h-fit transition-all duration-300 hover:border-gray-500 relative group">
                    
                    {{-- Ticket Header --}}
                    <div class="p-3 flex justify-between items-start border-b border-gray-700"
                         :class="getOrderDuration(order.created_at) > 20 ? 'bg-red-900/30' : (getOrderDuration(order.created_at) > 10 ? 'bg-yellow-900/20' : 'bg-gray-800')">
                        <div>
                            <h2 class="text-2xl font-black text-white leading-none" x-text="order.table.name"></h2>
                            <p class="text-xs text-gray-400 mt-1" x-text="'#' + order.invoice_number"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-mono font-bold" 
                                  :class="getOrderDuration(order.created_at) > 20 ? 'text-red-400 animate-pulse' : 'text-green-400'"
                                  x-text="formatTimeAgo(order.created_at)"></span>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div class="p-2 space-y-2 flex-1">
                        <template x-for="item in order.items" :key="item.id">
                            
                            {{-- Single Item --}}
                            <div class="flex flex-col p-2 rounded-lg transition-colors border border-transparent"
                                 :class="item.status === 'ready' ? 'bg-green-900/20 border-green-900/50 opacity-50' : 'bg-gray-700/50 hover:bg-gray-700'">
                                
                                <div class="flex justify-between items-start gap-2">
                                    {{-- Qty --}}
                                    <span class="bg-gray-200 text-gray-900 font-black text-lg px-2 rounded min-w-[2rem] text-center shrink-0" x-text="item.quantity"></span>
                                    
                                    {{-- Name & Details --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-lg font-bold leading-tight" 
                                           :class="item.status === 'ready' ? 'line-through text-gray-500' : 'text-white'"
                                           x-text="item.product.name"></p>
                                        
                                        {{-- Addons --}}
                                        <template x-if="item.addons && item.addons.length > 0">
                                            <div class="mt-1 pl-2 border-l-2 border-gray-600">
                                                <template x-for="ad in item.addons">
                                                    <p class="text-sm text-gray-300">
                                                        + <span x-text="ad.addon ? ad.addon.name : 'Unknown'"></span> 
                                                        <span class="text-xs font-mono text-gray-500" x-text="'x' + (ad.quantity || 1)"></span>
                                                    </p>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Note (Important) --}}
                                        <template x-if="item.note">
                                            <div class="mt-2 bg-red-900/40 text-red-200 px-2 py-1 rounded text-sm font-bold border border-red-900/50 animate-pulse">
                                                <i class="ri-message-2-fill"></i> <span x-text="item.note"></span>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Item Action (Done) --}}
                                    <button @click="markItemReady(item.id)" 
                                            x-show="item.status !== 'ready'"
                                            class="h-10 w-10 bg-gray-600 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-colors shrink-0">
                                        <i class="ri-check-line text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Card Footer --}}
                    <div class="p-3 border-t border-gray-700 bg-gray-800/50">
                        <button @click="markOrderReady(order.id)" 
                                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-lg shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
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
            mode: 'kitchen', // 'kitchen' or 'bar'
            isLoading: false,
            currentTime: '',
            timerInterval: null,
            pollingInterval: null,

            init() {
                this.updateClock();
                this.timerInterval = setInterval(() => this.updateClock(), 1000);
                
                // Fetch immediately
                this.fetchOrders();
                
                // Poll every 5 seconds
                this.pollingInterval = setInterval(() => this.fetchOrders(), 5000);
            },

            updateClock() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            changeMode(newMode) {
                this.mode = newMode;
                this.orders = []; // Clear current list visually
                this.fetchOrders();
            },

            async fetchOrders() {
                // Don't show loading spinner on background refresh to avoid flickering
                // Only show if orders is empty
                if(this.orders.length === 0) this.isLoading = true;

                try {
                    const response = await fetch(`{{ route('pos.kitchen.fetch') }}?destination=${this.mode}`);
                    const data = await response.json();
                    this.orders = data;
                } catch (error) {
                    console.error("Connection lost:", error);
                } finally {
                    this.isLoading = false;
                }
            },

            // 1. Mark Single Item as Done
            async markItemReady(itemId) {
                try {
                    const response = await fetch("{{ route('pos.kitchen.update_item') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ item_id: itemId, status: 'ready' })
                    });
                    
                    if (response.ok) {
                        // Optimistic Update: Update UI immediately before next poll
                        this.orders.forEach(order => {
                            const item = order.items.find(i => i.id === itemId);
                            if(item) item.status = 'ready';
                        });
                        // Remove empty orders if needed logic here...
                        this.fetchOrders(); // Refresh to be sure
                    }
                } catch (e) { console.error(e); }
            },

            // 2. Mark Whole Order as Done
            async markOrderReady(orderId) {
                if(!confirm('Mark entire order as ready?')) return;

                try {
                    const response = await fetch("{{ route('pos.kitchen.done_all') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ 
                            order_id: orderId,
                            destination: this.mode 
                        })
                    });

                    if (response.ok) {
                        // Remove Order from UI immediately
                        this.orders = this.orders.filter(o => o.id !== orderId);
                        this.fetchOrders();
                    }
                } catch (e) { console.error(e); }
            },

            // --- Helpers ---
            
            // Calculate minutes passed
            getOrderDuration(createdAt) {
                const start = new Date(createdAt).getTime();
                const now = new Date().getTime();
                return Math.floor((now - start) / 60000); // return minutes
            },

            // Format time display (e.g., "5m")
            formatTimeAgo(createdAt) {
                const mins = this.getOrderDuration(createdAt);
                if (mins < 1) return 'Just now';
                if (mins > 60) return Math.floor(mins/60) + 'h ' + (mins%60) + 'm';
                return mins + 'm';
            }
        }
    }
</script>
@endsection