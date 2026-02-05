@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="activityLogs()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('Admin.activity_log.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('Admin.activity_log.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('Admin.activity_log.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('Admin.activity_log.partials.pagination')

</div>

<script>
    function activityLogs() {
        return {
            logs: [],
            search: '',
            isLoading: false,
            
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            
            selectedIds: [],
            selectAll: false,
            
            // Column Config
            showCols: JSON.parse(localStorage.getItem('log_table_cols')) || { 
                causer: true, 
                subject: true, 
                changes: true, 
                date: true 
            },

            init() {
                this.$watch('showCols', (value) => {
                    localStorage.setItem('log_table_cols', JSON.stringify(value));
                });
                this.fetchLogs();
            },
            
            // Smart Pagination Logic
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

            async fetchLogs() {
                this.isLoading = true;
                const params = new URLSearchParams({
                    keyword: this.search,
                    per_page: this.perPage,
                    page: this.currentPage
                });
                
                let url = "{{ route('admin.activity_logs.fetch') }}";
                
                try {
                    const res = await fetch(`${url}?${params}`);
                    const data = await res.json();
                    
                    this.logs = data.data;
                    this.pagination = data;
                    this.currentPage = data.current_page;
                    this.selectedIds = [];
                    this.selectAll = false;
                } catch (e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.failed_load_data') }}" } }));
                } finally { 
                    this.isLoading = false; 
                }
            },

            gotoPage(page) {
                if (page < 1 || (this.pagination.last_page && page > this.pagination.last_page)) return;
                this.currentPage = page;
                this.fetchLogs();
            },

            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.logs.map(log => log.id) : [];
            },

            async confirmDelete(id) {
                if(typeof askConfirm !== 'undefined') {
                    askConfirm(async () => { await this.performDelete([id]); });
                } else if(confirm("Are you sure?")) {
                    await this.performDelete([id]);
                }
            },

            async confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                if(typeof askConfirm !== 'undefined') {
                    askConfirm(async () => { await this.performDelete(this.selectedIds, true); });
                } else if(confirm("Delete selected items?")) {
                    await this.performDelete(this.selectedIds, true);
                }
            },

            async performDelete(ids, isBulk = false) {
                let url = isBulk 
                    ? "{{ route('admin.activity_logs.bulk_delete') }}" 
                    : "{{ route('admin.activity_logs.destroy', ':id') }}".replace(':id', ids[0]);
                
                let method = isBulk ? 'POST' : 'DELETE';
                let body = isBulk ? JSON.stringify({ ids: ids }) : null;

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: body
                    });
                    
                    const data = await response.json();

                    if(response.ok) {
                        this.selectedIds = [];
                        this.selectAll = false;
                        this.fetchLogs();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.failed_delete') }}" } }));
                    }
                } catch(e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.network_error') }}" } }));
                }
            }
        }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>
@endsection