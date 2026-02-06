
@extends('admin.dashboard')

@section('content')

@section('title', 'User Management')


<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="userManagement()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('Admin.user.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('Admin.user.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('Admin.user.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('Admin.user.partials.pagination')

    {{-- 5. MODAL (CREATE / EDIT) --}}
    @include('Admin.user.partials.modal')

</div>

<script>
    function userManagement() {
        return {
            users: [],
            // ⚠️ Ensure $roles is passed from controller
            roles: @json($roles ?? []), 
            
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
            showCols: JSON.parse(localStorage.getItem('user_table_cols')) || { 
                role: true, 
                email: true, 
                created_at: true 
            },

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', email: '', role: '', password: '' },
            errors: {},

            init() { 
                this.$watch('showCols', (value) => { localStorage.setItem('user_table_cols', JSON.stringify(value)); });
                this.fetchUsers(); 
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

            async fetchUsers() {
                let url = "{{ route('admin.users.fetch') }}";
                const params = new URLSearchParams({
                    keyword: this.search,
                    per_page: this.perPage,
                    page: this.currentPage
                });
                
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.users = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                    this.selectAll = false;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            gotoPage(page) { if(page === '...') return; this.currentPage = page; this.fetchUsers(); },
            
            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.users.map(u => u.id) : [];
            },

            // ================= SEQUENTIAL EDIT =================
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.users.filter(item => selectedIdsString.includes(String(item.id)));
                
                if (this.sequenceQueue.length === 0) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.select_users_first') }}" } })); 
                    return;
                }

                this.isSequenceMode = true;
                this.currentSeqIndex = 0;
                this.loadUserToForm(this.sequenceQueue[0]);
                this.isModalOpen = true;
            },

            nextInSequence() {
                this.currentSeqIndex++;
                if (this.currentSeqIndex < this.sequenceQueue.length) {
                    this.loadUserToForm(this.sequenceQueue[this.currentSeqIndex]);
                } else {
                    this.closeModal(true); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.all_users_updated') }}" } }));
                }
            },

            loadUserToForm(user) {
                this.editMode = true;
                this.errors = {};
                this.form = { 
                    id: user.id, 
                    name: user.name, 
                    email: user.email, 
                    role: user.roles.length > 0 ? user.roles[0].name : '',
                    password: '' // Reset password field
                };
            },

            // ================= MODAL & FORM =================
            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                
                if (mode === 'edit') {
                    this.loadUserToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', email: '', role: '', password: '' };
                }
            },

            closeModal(force = false) {
                 if (!force && this.isSequenceMode && !confirm("{{ __('messages.confirm_stop_sequence') }}")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchUsers(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                
                let url = "{{ route('admin.users.store') }}";
                let method = 'POST';

                if (this.editMode) {
                    url = `/admin/users/${this.form.id}`;
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
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.fix_errors') }}" } }));
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Error' } }));
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        
                        if (this.isSequenceMode) {
                            this.nextInSequence();
                        } else {
                            this.closeModal();
                            this.fetchUsers();
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
                let url = isBulk ? "{{ route('admin.users.bulk_delete') }}" : `/admin/users/${ids[0]}`;
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
                        this.fetchUsers();
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