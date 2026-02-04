@extends('layouts.blank')

@section('content')
<div x-data="kitchenDisplay()" x-init="init()" class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white font-sans flex flex-col overflow-hidden relative transition-colors duration-300">
    
    {{-- =========================================== --}}
    {{-- 1. TOAST NOTIFICATION (Custom)              --}}
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

    {{-- =========================================== --}}
    {{-- 2. CONFIRM MODAL (Custom)                   --}}
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

    {{-- =========================================== --}}
    {{-- 3. HEADER & CONTROLS                        --}}
    {{-- =========================================== --}}
    <div class="h-14 sm:h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-3 sm:px-6 shrink-0 z-20 shadow-md gap-2 transition-colors duration-300">
        
        {{-- LEFT SIDE: Navigation & Title --}}
        <div class="flex items-center gap-3 sm:gap-6 flex-1 min-w-0">
            
            {{-- 1. BACK BUTTON (Hidden for Chef/Bartender) --}}
            @unlessrole('Chef|Bartender')
                <a href="{{ route('pos.tables') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition shrink-0" title="Back to Tables">
                    <i class="ri-arrow-left-line text-lg sm:text-xl"></i>
                </a>
            @endunlessrole

            {{-- Title --}}
            <h1 class="text-base sm:text-xl font-bold tracking-wide uppercase flex items-center gap-2 truncate text-gray-800 dark:text-white">
                <i class="ri-fire-line text-orange-500"></i> <span class="hidden xs:inline">KDS System</span>
            </h1>
            
            <div class="h-4 sm:h-6 w-px bg-gray-300 dark:bg-gray-600 hidden xs:block"></div>
            
            {{-- Clock --}}
            <span class="text-lg sm:text-2xl font-mono font-bold text-blue-600 dark:text-blue-400" x-text="clockString"></span>

            {{-- 2. PRODUCTS BUTTON (Permission Based) --}}
            @can('product-list')
                <div class="h-4 sm:h-6 w-px bg-gray-300 dark:bg-gray-600 hidden xs:block"></div>
                <a href="{{ url('/admin/products') }}" 
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition text-xs sm:text-sm font-bold border border-gray-200 dark:border-gray-600">
                    <i class="ri-box-3-line text-primary"></i>
                    <span class="hidden sm:inline">Products</span>
                </a>
            @endcan
        </div>

        {{-- CENTER: DESTINATION TABS --}}
        <div class="flex bg-gray-100 dark:bg-gray-900 p-0.5 sm:p-1 rounded-lg border border-gray-200 dark:border-gray-700 shrink-0 overflow-x-auto custom-scrollbar max-w-[200px] sm:max-w-md">
            @foreach($destinations as $dest)
                <button @click="changeMode({{ $dest->id }})" 
                        class="px-3 sm:px-6 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-bold transition-all flex items-center gap-1 sm:gap-2 whitespace-nowrap"
                        :class="currentDestinationId == {{ $dest->id }} ? 'bg-primary text-white shadow-md' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-800'">
                    @if(stripos($dest->name, 'bar') !== false || stripos($dest->name, 'drink') !== false)
                        <i class="ri-goblet-line"></i>
                    @else
                        <i class="ri-restaurant-line"></i>
                    @endif
                    <span class="hidden sm:inline">{{ $dest->name }}</span>
                </button>
            @endforeach
        </div>

        {{-- RIGHT SIDE: STATUS & ACTIONS --}}
        <div class="flex items-center gap-3 sm:gap-4 shrink-0">
            
            {{-- Live Indicator --}}
            <div class="flex items-center gap-1 sm:gap-2">
                <span class="relative flex h-2.5 w-2.5 sm:h-3 sm:w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="isLoading ? 'bg-blue-400' : 'bg-green-400'"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 sm:h-3 sm:w-3" :class="isLoading ? 'bg-blue-500' : 'bg-green-500'"></span>
                </span>
                <span class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 font-mono hidden sm:inline" x-text="isLoading ? 'Fetching...' : 'Live'"></span>
            </div>

            <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 hidden sm:block"></div>

            {{-- 3. THEME TOGGLE --}}
            <button x-data="{ dark: localStorage.getItem('theme') === 'dark' }" 
                    @click="dark = !dark; localStorage.setItem('theme', dark ? 'dark' : 'light'); 
                            if(dark) { document.documentElement.classList.add('dark') } else { document.documentElement.classList.remove('dark') }"
                    class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-yellow-400 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all active:scale-95 border border-transparent dark:border-gray-600">
                <i class="text-lg" :class="dark ? 'ri-sun-fill' : 'ri-moon-fill'"></i>
            </button>

            {{-- 4. PROFILE & LOGOUT --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" 
                        class="flex items-center gap-2 bg-gray-100 dark:bg-gray-700 pl-1 pr-3 py-1 rounded-full border border-gray-200 dark:border-gray-600 hover:border-primary/50 transition-all shadow-sm">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Chef' }}&background=E11D48&color=fff&size=64" 
                         class="w-7 h-7 rounded-full object-cover" alt="Avatar">
                    <div class="hidden md:block text-left">
                        <p class="text-xs font-bold text-gray-800 dark:text-white leading-none truncate max-w-[80px]">
                            {{ Auth::user()->name ?? 'Chef' }}
                        </p>
                    </div>
                    <i class="ri-arrow-down-s-line text-xs text-gray-500 dark:text-gray-400"></i>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-50 origin-top-right">
                    
                    {{-- Dashboard Link --}}
                    @can('dashboard')
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                        <i class="ri-dashboard-3-line text-primary"></i> Dashboard
                    </a>
                    @endcan

                    <div class="h-px bg-gray-100 dark:bg-gray-700 my-1"></div>

                    {{-- Logout Form --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                            <i class="ri-logout-box-r-line"></i> {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 4. ORDERS GRID AREA                         --}}
    {{-- =========================================== --}}
    <div class="flex-1 overflow-y-auto p-2 sm:p-4 custom-scrollbar">
        <div x-show="isLoading && orders.length === 0" class="flex h-full items-center justify-center">
            <i class="ri-loader-4-line animate-spin text-3xl sm:text-4xl text-gray-600"></i>
        </div>

        <div x-show="!isLoading && orders.length === 0" class="flex flex-col h-full items-center justify-center text-gray-600 opacity-50" x-cloak>
            <i class="ri-checkbox-circle-line text-5xl sm:text-6xl mb-3 sm:mb-4"></i>
            <p class="text-xl sm:text-2xl font-bold">All Orders Completed</p>
            <p class="text-xs sm:text-sm">Waiting for new orders...</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4" x-show="orders.length > 0" x-cloak>
            <template x-for="order in orders" :key="order.id">
                <div class="flex flex-col rounded-lg sm:rounded-xl shadow-lg overflow-hidden h-fit transition-all duration-300 hover:scale-[1.01] border relative group"
                     :class="getBgClass(order.created_at, currentTimeTrigger)"> 
                    
                    {{-- Header --}}
                    <div class="p-2 sm:p-3 flex justify-between items-start border-b border-white/10">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-white leading-none" x-text="order.table ? order.table.name : 'Unknown'"></h2>
                            <p class="text-[10px] sm:text-xs text-white/70 mt-1 font-mono" x-text="'#' + order.invoice_number"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg sm:text-xl font-mono font-bold" 
                                  :class="getBgClass(order.created_at, currentTimeTrigger).includes('red') ? 'text-white animate-pulse' : 'text-green-300'"
                                  x-text="formatTimeAgo(order.created_at, currentTimeTrigger)"></span>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="p-2 space-y-1.5 sm:space-y-2 flex-1">
                        <template x-for="item in order.items" :key="item.id">
                            <div class="flex flex-col p-1.5 sm:p-2 rounded-md sm:rounded-lg transition-colors border border-transparent"
                                 :class="item.status === 'ready' ? 'bg-green-900/30 border-green-500/30 opacity-60' : 'bg-black/20 hover:bg-black/30'">
                                <div class="flex justify-between items-start gap-2">
                                    <span class="bg-white text-gray-900 font-black text-base sm:text-lg px-1.5 sm:px-2 rounded min-w-[1.8rem] sm:min-w-[2rem] text-center shrink-0 h-6 sm:h-7 leading-6 sm:leading-7" x-text="item.quantity"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base sm:text-lg font-bold leading-tight" 
                                           :class="item.status === 'ready' ? 'line-through text-gray-400' : 'text-white'"
                                           x-text="item.product ? item.product.name : 'Deleted Item'"></p>
                                        <template x-if="item.addons && item.addons.length > 0">
                                            <div class="mt-0.5 sm:mt-1 pl-2 border-l-2 border-gray-500/50">
                                                <template x-for="ad in item.addons">
                                                    <p class="text-xs sm:text-sm text-gray-300">
                                                        + <span x-text="ad.addon ? ad.addon.name : 'Unknown'"></span> 
                                                        <span class="text-[10px] sm:text-xs font-mono text-gray-400" x-text="'x' + (ad.quantity || 1)"></span>
                                                    </p>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="item.note">
                                            <div class="mt-1 sm:mt-2 bg-red-500/20 text-red-200 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded text-xs sm:text-sm font-bold border border-red-500/30 w-fit">
                                                <i class="ri-message-2-fill"></i> <span x-text="item.note"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <button @click="markItemReady(item.id)" x-show="item.status !== 'ready'" class="h-8 w-8 sm:h-10 sm:w-10 bg-gray-600 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-colors shrink-0 shadow-sm">
                                        <i class="ri-check-line text-lg sm:text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Footer with Custom Modal Trigger --}}
                    <div class="p-2 sm:p-3 border-t border-white/10 bg-black/10">
                        <button @click="openConfirmModal(order.id)" 
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

{{-- JAVASCRIPT LOGIC --}}
<script>
    function kitchenDisplay() {
        return {
            orders: [],
            currentDestinationId: localStorage.getItem('kitchen_active_tab') 
                ? parseInt(localStorage.getItem('kitchen_active_tab')) 
                : {{ $destinations->first()->id ?? 0 }}, 

            isLoading: false,
            clockString: '',
            currentTimeTrigger: Date.now(), 
            timerInterval: null,
            pollingInterval: null,

            // ✅ STATE សម្រាប់ Toast
            toast: { show: false, message: '', type: 'success' },

            // ✅ STATE សម្រាប់ Modal
            modal: { show: false, message: '', orderIdToConfirm: null },

            init() {
                this.updateClock();
                this.timerInterval = setInterval(() => {
                    this.updateClock();
                    this.currentTimeTrigger = Date.now(); 
                }, 1000);
                
                if(this.currentDestinationId !== 0) {
                    this.fetchOrders();
                    this.pollingInterval = setInterval(() => this.fetchOrders(), 5000);
                }
            },

            updateClock() {
                this.clockString = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            },

            changeMode(newId) {
                this.currentDestinationId = newId;
                localStorage.setItem('kitchen_active_tab', newId);
                this.orders = []; 
                this.fetchOrders();
            },

            // --- NOTIFICATION HELPERS ---
            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 3000);
            },

            // --- MODAL HELPERS ---
            openConfirmModal(orderId) {
                this.modal.orderIdToConfirm = orderId;
                this.modal.message = 'Have you finished all items for this order?';
                this.modal.show = true;
            },

            closeModal() {
                this.modal.show = false;
                this.modal.orderIdToConfirm = null;
            },

            async confirmAction() {
                if(this.modal.orderIdToConfirm) {
                    await this.processOrderDone(this.modal.orderIdToConfirm);
                }
                this.closeModal();
            },

            // --- FETCH & API ---
            async fetchOrders() {
                if(this.currentDestinationId === 0) return;
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

            calculateSeconds(createdAt) {
                if (!createdAt) return 0;
                let dateStr = createdAt;
                if (dateStr.indexOf('Z') === -1) dateStr = dateStr.replace(' ', 'T') + 'Z';
                const startDate = new Date(dateStr);
                const now = new Date();
                let diffSeconds = Math.floor((now - startDate) / 1000);
                if (diffSeconds < -3600) diffSeconds += (7 * 3600); 
                if (diffSeconds < 0) diffSeconds = 0;
                return diffSeconds;
            },

            formatTimeAgo(createdAt, trigger) {
                const diffSeconds = this.calculateSeconds(createdAt);
                if (diffSeconds < 60) return diffSeconds + 's';
                const minutes = Math.floor(diffSeconds / 60);
                if (minutes < 60) return minutes + 'm';
                const hours = Math.floor(minutes / 60);
                const remainingMinutes = minutes % 60;
                return `${hours}h ${remainingMinutes}m`;
            },

            getBgClass(createdAt, trigger) {
                const diffSeconds = this.calculateSeconds(createdAt);
                const minutes = Math.floor(diffSeconds / 60);
                if (minutes >= 20) return 'bg-red-900 border-red-500 shadow-red-900/50';
                if (minutes >= 10) return 'bg-yellow-900 border-yellow-600 shadow-yellow-900/50';
                return 'bg-gray-800 border-gray-700';
            },

            async markItemReady(itemId) {
                try {
                    const response = await fetch("{{ route('pos.kitchen.update_item') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ item_id: itemId, status: 'ready' })
                    });
                    if (response.ok) {
                        this.orders.forEach(order => {
                            const item = order.items.find(i => i.id === itemId);
                            if(item) item.status = 'ready';
                        });
                        this.showToast('Item marked as ready!', 'success'); // ✅ Custom Toast
                    }
                } catch (e) { 
                    this.showToast('Failed to update item', 'error'); 
                }
            },

            // 🔥 Function នេះហៅដោយ Modal "Confirm"
            async processOrderDone(orderId) {
                try {
                    const response = await fetch("{{ route('pos.kitchen.done_all') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ order_id: orderId, kitchen_destination_id: this.currentDestinationId })
                    });
                    if (response.ok) {
                        this.orders = this.orders.filter(o => o.id !== orderId);
                        this.showToast('Order completed successfully!', 'success'); // ✅ Custom Toast
                    }
                } catch (e) { 
                    this.showToast('Error completing order', 'error');
                }
            }
        }
    }
</script>
@endsection