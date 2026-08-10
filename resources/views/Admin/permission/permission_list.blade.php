@extends('admin.dashboard')


@section('title', __('messages.permission_management'))

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="permissionManagement()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('admin.permission.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('admin.permission.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('admin.permission.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('admin.permission.partials.pagination')

    {{-- 5. MODAL (CREATE / EDIT) --}}
    @include('admin.permission.partials.modal')

</div>

<script>
    function permissionManagement() {
        return {
            permissions: [],
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
            showCols: JSON.parse(localStorage.getItem('perm_table_cols')) || { 
                guard_name: true, 
                created_at: true 
            },

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', guard_name: 'web' },
            errors: {},

            init() { 
                this.$watch('showCols', (value) => { localStorage.setItem('perm_table_cols', JSON.stringify(value)); });
                this.fetchPermissions(); 
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

            async fetchPermissions() {
                let url = "{{ route('admin.permissions.fetch') }}";
                const params = new URLSearchParams({
                    keyword: this.search,
                    per_page: this.perPage,
                    page: this.currentPage
                });
                
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.permissions = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                    this.selectAll = false;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            gotoPage(page) { if(page === '...') return; this.currentPage = page; this.fetchPermissions(); },
            
            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.permissions.map(p => p.id) : [];
            },

            // ================= SEQUENTIAL EDIT =================
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.permissions.filter(item => selectedIdsString.includes(String(item.id)));
                
                if (this.sequenceQueue.length === 0) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.select_items_first') }}" } })); 
                    return;
                }

                this.isSequenceMode = true;
                this.currentSeqIndex = 0;
                this.loadPermToForm(this.sequenceQueue[0]);
                this.isModalOpen = true;
            },

            nextInSequence() {
                this.currentSeqIndex++;
                if (this.currentSeqIndex < this.sequenceQueue.length) {
                    this.loadPermToForm(this.sequenceQueue[this.currentSeqIndex]);
                } else {
                    this.closeModal(true); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.bulk_update_complete') }}" } }));
                }
            },

            loadPermToForm(perm) {
                this.editMode = true;
                this.errors = {};
                this.form = { id: perm.id, name: perm.name, guard_name: perm.guard_name };
            },

            // ================= MODAL & FORM =================
            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                
                if (mode === 'edit') {
                    this.loadPermToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', guard_name: 'web' };
                }
            },

            closeModal(force = false) {
                 if (!force && this.isSequenceMode && !confirm("{{ __('messages.stop_editing_sequence') }}")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchPermissions(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                
                let url = "{{ route('admin.permissions.store') }}";
                let method = 'POST';

                if (this.editMode) {
                    url = `/admin/permissions/${this.form.id}`;
                    method = 'PUT';
                }

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
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.check_input') }}" } }));
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Error' } }));
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        
                        if (this.isSequenceMode) {
                            this.nextInSequence();
                        } else {
                            this.closeModal();
                            this.fetchPermissions();
                        }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
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
                let url = isBulk ? "{{ route('admin.permissions.bulk_delete') }}" : `/admin/permissions/${ids[0]}`;
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
                        this.fetchPermissions();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message || "{{ __('messages.delete_success') }}" } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || "{{ __('messages.delete_fail') }}" } }));
                    }
                } catch(e) { console.error(e); }
            }
        }
    }
</script>
@endsection