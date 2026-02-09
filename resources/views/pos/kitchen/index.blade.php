@extends('layouts.blank')

@section('title', __('messages.kitchen_system'))

@section('content')
<div x-data="kitchenDisplay()" x-init="init()" class="h-dvh w-full bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white font-sans flex flex-col overflow-hidden relative transition-colors duration-300">
    
    @include('pos.kitchen.partials.toast')
    @include('pos.kitchen.partials.modal')

    <div class="shrink-0 z-20">
        @include('pos.kitchen.partials.header')
    </div>

    <div class="flex-1 overflow-hidden relative z-10">
        @include('pos.kitchen.partials.order-list')
    </div>

</div>

<script>
    // ✅ បោះតម្លៃភាសាពី Laravel ចូលទៅក្នុង JS
    const TRANS = {
        success: "{{ __('messages.success') }}",
        error: "{{ __('messages.error') }}",
        confirm_message: "{{ __('messages.confirm_message') }}",
        item_marked_ready: "{{ __('messages.item_marked_ready') }}",
        failed_update_item: "{{ __('messages.failed_update_item') }}",
        order_completed: "{{ __('messages.order_completed') }}",
        error_completing_order: "{{ __('messages.error_completing_order') }}",
        connection_lost: "{{ __('messages.connection_lost') }}"
    };

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
            toast: { show: false, message: '', type: 'success' },
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

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 3000);
            },

            openConfirmModal(orderId) {
                this.modal.orderIdToConfirm = orderId;
                // ✅ ប្រើភាសាពី Variable TRANS
                this.modal.message = TRANS.confirm_message;
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

            async fetchOrders() {
                if(this.currentDestinationId === 0) return;
                if(this.orders.length === 0) this.isLoading = true;
                
                try {
                    const response = await fetch(`{{ route('pos.kitchen.fetch') }}?kitchen_destination_id=${this.currentDestinationId}`);
                    const data = await response.json();
                    this.orders = data;
                } catch (error) {
                    console.error(TRANS.connection_lost, error);
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
                        // ✅ ប្រើភាសាពី Variable TRANS
                        this.showToast(TRANS.item_marked_ready, 'success'); 
                    }
                } catch (e) { 
                    this.showToast(TRANS.failed_update_item, 'error'); 
                }
            },

            async processOrderDone(orderId) {
                try {
                    const response = await fetch("{{ route('pos.kitchen.done_all') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ order_id: orderId, kitchen_destination_id: this.currentDestinationId })
                    });
                    if (response.ok) {
                        this.orders = this.orders.filter(o => o.id !== orderId);
                        // ✅ ប្រើភាសាពី Variable TRANS
                        this.showToast(TRANS.order_completed, 'success'); 
                    }
                } catch (e) { 
                    this.showToast(TRANS.error_completing_order, 'error');
                }
            }
        }
    }
</script>
@endsection