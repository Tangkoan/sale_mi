@extends('admin.dashboard')

@section('title', 'ប្រវត្តិការកម្ម៉ង់ (Order History)')

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="orderHistoryManagement()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('admin.order_history.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('admin.order_history.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('admin.order_history.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('admin.product.partials.pagination') {{-- ប្រើ Pagination របស់ Product ចាស់បាន --}}

    {{-- 5. MODAL (VIEW DETAILS) --}}
    @include('admin.order_history.partials.modal_view')

</div>

<script>
    function orderHistoryManagement() {
        return {
            orders: [],
            search: '',
            filterDate: '', // សម្រាប់រើសថ្ងៃ
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            
            isModalOpen: false,
            isLoading: false,
            isPrinting: false,

            showCols: JSON.parse(localStorage.getItem('order_table_cols')) || { invoice: true, table: true, amount: true, method: true, date: true },
            sortBy: 'created_at',
            sortDir: 'desc',

            selectedOrder: null, // ទុកទិន្នន័យសម្រាប់បង្ហាញក្នុង Modal

            init() { 
                this.$watch('showCols', (value) => { localStorage.setItem('order_table_cols', JSON.stringify(value)); });
                this.fetchOrders(); 
            },

            get visiblePages() {
                const total = this.pagination.last_page;
                const current = this.currentPage;
                const delta = 2;
                let pages = [];
                if (total <= 7) { for (let i = 1; i <= total; i++) pages.push(i); return pages; }
                pages.push(1);
                if (current > delta + 2) pages.push('...');
                let start = Math.max(2, current - delta);
                let end = Math.min(total - 1, current + delta);
                for (let i = start; i <= end; i++) pages.push(i);
                if (current < total - delta - 1) pages.push('...');
                if (total > 1) pages.push(total);
                return pages;
            },

            async fetchOrders() {
                let url = "{{ route('admin.orders.fetch') }}";
                const params = new URLSearchParams({
                    keyword: this.search,
                    date: this.filterDate,
                    per_page: this.perPage,
                    page: this.currentPage,
                    sort_by: this.sortBy,
                    sort_dir: this.sortDir
                });
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.orders = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            sort(col) { if (this.sortBy === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; else { this.sortBy = col; this.sortDir = 'desc'; } this.fetchOrders(); },
            gotoPage(page) { if(page === '...') return; this.currentPage = page; this.fetchOrders(); },

            // មើលលម្អិត
            async viewDetails(id) {
                this.isLoading = true;
                try {
                    const response = await fetch(`/admin/orders/${id}/details`);
                    const data = await response.json();
                    if (data.status === 'success') {
                        this.selectedOrder = data.order;
                        this.isModalOpen = true;
                    }
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'មានបញ្ហាក្នុងការទាញទិន្នន័យ' } }));
                } finally {
                    this.isLoading = false;
                }
            },

            closeModal() {
                this.isModalOpen = false;
                setTimeout(() => { this.selectedOrder = null; }, 300); // ទុកពេលអោយ Animation បិទសិន
            },

            // ព្រីនឡើងវិញ
            async reprint(id) {
                if(!confirm("តើអ្នកពិតជាចង់ព្រីនវិក្កយបត្រនេះឡើងវិញមែនទេ?")) return;
                
                this.isPrinting = true;
                try {
                    const response = await fetch(`/admin/orders/${id}/reprint`, {
                        method: 'POST',
                        headers: { 
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        }
                    });
                    const data = await response.json();
                    if(response.ok) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message } }));
                    }
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Server Error' } }));
                } finally {
                    this.isPrinting = false;
                }
            }
        }
    }
</script>
@endsection