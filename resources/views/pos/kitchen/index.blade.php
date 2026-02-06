@extends('layouts.blank')

@section('content')
<div x-data="kitchenDisplay()" x-init="init()" class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white font-sans flex flex-col overflow-hidden relative transition-colors duration-300">
    
    {{-- 1. INCLUDE TOAST --}}
    @include('pos.kitchen.partials.toast')

    {{-- 2. INCLUDE MODAL --}}
    @include('pos.kitchen.partials.modal')

    {{-- 3. INCLUDE HEADER --}}
    @include('pos.kitchen.partials.header')

    {{-- 4. INCLUDE ORDERS LIST --}}
    @include('pos.kitchen.partials.order-list')

</div>

{{-- JAVASCRIPT LOGIC (រក្សា Logic ទាំងស្រុងដដែល) --}}
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
                        this.showToast('Item marked as ready!', 'success'); 
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
                        this.showToast('Order completed successfully!', 'success'); 
                    }
                } catch (e) { 
                    this.showToast('Error completing order', 'error');
                }
            }
        }
    }
</script>
@endsection