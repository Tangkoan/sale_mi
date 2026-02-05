@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="roleManagement()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('Admin.role.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('Admin.role.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('Admin.role.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('Admin.role.partials.pagination')

    {{-- 5. MODALS --}}
    @include('Admin.role.partials.modal')
    @include('Admin.role.partials.permission_modal')

</div>

<script>
    function roleManagement() {
        return {
            roles: [],
            search: '',
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            
            isModalOpen: false,
            isPermissionModalOpen: false,
            editMode: false,
            isLoading: false,
            selectedIds: [],
            selectAll: false,

            // Column Config
            showCols: JSON.parse(localStorage.getItem('role_table_cols')) || { 
                permissions: true, 
                users_count: true 
            },

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', level: 10 },
            permissionForm: { roleId: null, roleName: '', permissions: [] },
            allAvailablePermissions: [],
            errors: {},

            init() { 
                this.$watch('showCols', (value) => { localStorage.setItem('role_table_cols', JSON.stringify(value)); });
                this.fetchRoles(); 
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

            async fetchRoles() {
                let url = "{{ route('admin.roles.fetch') }}";
                const params = new URLSearchParams({
                    keyword: this.search,
                    per_page: this.perPage,
                    page: this.currentPage
                });
                
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.roles = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                    this.selectAll = false;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            gotoPage(page) { if(page === '...') return; this.currentPage = page; this.fetchRoles(); },
            
            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.roles.map(r => r.id) : [];
            },

            // ================= SEQUENTIAL EDIT =================
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.roles.filter(item => selectedIdsString.includes(String(item.id)));
                
                if (this.sequenceQueue.length === 0) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.select_users_first') }}" } })); 
                    return;
                }

                this.isSequenceMode = true;
                this.currentSeqIndex = 0;
                this.loadRoleToForm(this.sequenceQueue[0]);
                this.isModalOpen = true;
            },

            nextInSequence() {
                this.currentSeqIndex++;
                if (this.currentSeqIndex < this.sequenceQueue.length) {
                    this.loadRoleToForm(this.sequenceQueue[this.currentSeqIndex]);
                } else {
                    this.closeModal(true); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.all_users_updated') }}" } }));
                }
            },

            loadRoleToForm(role) {
                this.editMode = true;
                this.errors = {};
                this.form = { id: role.id, name: role.name, level: role.level };
            },

            // ================= ROLE MODAL =================
            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                
                if (mode === 'edit') {
                    this.loadRoleToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', level: 10 };
                }
            },

            closeModal(force = false) {
                 if (!force && this.isSequenceMode && !confirm("{{ __('messages.confirm_stop_sequence') }}")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchRoles(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                let url = this.editMode ? `/admin/roles/${this.form.id}` : "{{ route('admin.roles.store') }}";
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
                        
                        if (this.isSequenceMode) {
                            this.nextInSequence();
                        } else {
                            this.closeModal();
                            this.fetchRoles();
                        }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            // ================= PERMISSION MODAL =================
            async openPermissionModal(role) {
                this.permissionForm.roleId = role.id;
                this.permissionForm.roleName = role.name;
                this.isPermissionModalOpen = true;
                this.allAvailablePermissions = [];
                this.permissionForm.permissions = [];
                try {
                    const res = await fetch(`/admin/assign-permissions/${role.id}`);
                    const data = await res.json();
                    this.allAvailablePermissions = data.available_permissions;
                    this.permissionForm.permissions = data.checked_permissions; 
                } catch (e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.network_error') }}" } }));
                }
            },
            selectAllPermissions() {
                this.permissionForm.permissions = this.allAvailablePermissions.map(p => p.name);
            },
            async submitPermissions() {
                this.isLoading = true;
                try {
                    const res = await fetch("{{ route('admin.assign_permissions.update') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify({ role_id: this.permissionForm.roleId, permissions: this.permissionForm.permissions })
                    });
                    const data = await res.json();
                    if(res.ok) {
                        this.isPermissionModalOpen = false; this.fetchRoles();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    } else window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || "{{ __('messages.error_generic') }}" } }));
                } catch (e) { 
                    console.error(e);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.network_error') }}" } }));
                } finally { this.isLoading = false; }
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
                let url = isBulk ? "{{ route('admin.roles.bulk_delete') }}" : `/admin/roles/${ids[0]}`;
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
                        this.fetchRoles();
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