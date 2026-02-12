@extends('admin.dashboard')


@section('title', __('messages.table_management'))

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="tableManagement()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('admin.table.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('admin.table.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('admin.table.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('admin.table.partials.pagination')

    {{-- 5. MODAL (CREATE / EDIT) --}}
    @include('admin.table.partials.modal')

</div>

<script>
    function tableManagement() {
        return {
            tables: [],
            search: '',
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            selectedIds: [],
            selectAll: false,

            // Column Config
            showCols: JSON.parse(localStorage.getItem('table_table_cols')) || { 
                name: true, 
                status: true, 
                created_at: true 
            },

            sortBy: 'created_at',
            sortDir: 'desc',

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', status: 'available' },
            errors: {},

            init() { 
                this.$watch('showCols', (value) => { localStorage.setItem('table_table_cols', JSON.stringify(value)); });
                this.fetchTables(); 
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

            async fetchTables() {
                let url = "{{ route('admin.tables.fetch') }}";
                const params = new URLSearchParams({
                    keyword: this.search,
                    per_page: this.perPage,
                    page: this.currentPage,
                    sort_by: this.sortBy,
                    sort_dir: this.sortDir
                });
                
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.tables = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                    this.selectAll = false;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            sort(col) {
                if (this.sortBy === col) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = col;
                    this.sortDir = 'desc'; 
                }
                this.fetchTables();
            },

            gotoPage(page) { if(page === '...') return; this.currentPage = page; this.fetchTables(); },
            
            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.tables.map(t => t.id) : [];
            },

            // ================= MODAL LOGIC =================
            loadDataToForm(item) {
                this.editMode = true;
                this.errors = {};
                this.form = { ...item };
            },

            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                if (mode === 'edit') {
                    this.loadDataToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', status: 'available' };
                }
            },

            closeModal(force = false) {
                 if (!force && this.isSequenceMode && !confirm("{{ __('messages.confirm_stop_sequence') }}")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchTables(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                let url = this.editMode ? `/admin/tables/${this.form.id}` : "{{ route('admin.tables.store') }}";
                let method = this.editMode ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: { 
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify(this.form)
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.errors;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.fix_errors') }}" } }));
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Error' } }));
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        if (this.isSequenceMode) { this.nextInSequence(); } else { this.closeModal(); this.fetchTables(); }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            // ================= SEQUENTIAL EDIT =================
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.tables.filter(item => selectedIdsString.includes(String(item.id)));
                if (this.sequenceQueue.length === 0) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.select_users_first') }}" } })); 
                    return;
                }
                this.isSequenceMode = true;
                this.currentSeqIndex = 0;
                this.loadDataToForm(this.sequenceQueue[0]);
                this.isModalOpen = true;
            },

            nextInSequence() {
                this.currentSeqIndex++;
                if (this.currentSeqIndex < this.sequenceQueue.length) {
                    this.loadDataToForm(this.sequenceQueue[this.currentSeqIndex]);
                } else {
                    this.closeModal(true); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.all_users_updated') }}" } }));
                }
            },

            // ================= DELETE =================
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
                let url = isBulk ? "{{ route('admin.tables.bulk_delete') }}" : `/admin/tables/${ids[0]}`;
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
                        this.fetchTables();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message } }));
                    }
                } catch(e) { console.error(e); }
            }
        }
    }
</script>
@endsection