@extends('admin.dashboard')

@section('content')
<div class="w-full h-full px-1 py-1" x-data="roleManagement()">
    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-shield-user-line text-primary"></i> Role Management
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            <div x-show="selectedIds.length > 0" x-transition.opacity.duration.300ms 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                
                <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1 whitespace-nowrap" x-text="selectedIds.length + ' selected'"></span>
                
                <div class="flex gap-1">
                    @can('role-edit')
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition border border-transparent hover:border-blue-100" title="Edit Sequence">
                        <i class="ri-edit-circle-line mr-1"></i> <span class="hidden sm:inline">Edit</span>
                    </button>
                    @endcan

                    @can('role-delete')
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition border border-transparent hover:border-red-100" title="Delete Selected">
                        <i class="ri-delete-bin-line mr-1"></i> <span class="hidden sm:inline">Delete</span>
                    </button>
                    @endcan
                </div>
            </div>

            <div class="relative w-full sm:w-auto" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" 
                        class="w-full sm:w-auto flex justify-center items-center gap-2 px-3 py-2.5 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm">
                    <i class="ri-layout-column-line"></i> <span class="sm:hidden lg:inline">Columns</span>
                </button>
                <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.permissions" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Permissions</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.users_count" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Users Count</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchRoles()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="Search roles...">
            </div>

            <button 
                @can('role-create') @click="openModal('create')" @endcan
                class="w-full sm:w-auto text-white font-bold py-2.5 px-6 rounded-xl flex justify-center items-center gap-2 transition-all shadow-lg shadow-primary/30 whitespace-nowrap
                @can('role-create') bg-primary hover:opacity-90 @else bg-gray-400 cursor-not-allowed opacity-70 @endcan"
                @cannot('role-create') disabled title="No Permission" @endcannot
            >
                <i class="ri-add-circle-line"></i> 
                <span>Add Role</span>
            </button>
        </div>
    </div>

    <div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 w-4">
                            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 font-bold w-1/4">Role Name</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.permissions">Permissions Preview</th>
                        <th class="px-6 py-4 font-bold text-center" x-show="showCols.users_count">Users</th>
                        <th class="px-6 py-4 font-bold text-right w-40">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="role in roles" :key="role.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(role.id)}">
                            
                            <td class="px-6 py-4 align-top">
                                <input type="checkbox" :value="role.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4 cursor-pointer">
                            </td>

                            <td class="px-6 py-4 align-top">
                                <span class="font-bold text-text-color text-lg" x-text="role.name"></span>
                            </td>

                            <td class="px-6 py-4" x-show="showCols.permissions">
                                <div class="flex flex-wrap gap-2">
                                    <template x-if="role.permissions && role.permissions.length > 0">
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="perm in role.permissions.slice(0, 5)" :key="perm.id">
                                                <span class="px-2 py-1 rounded text-xs font-medium bg-input-bg border border-input-border text-secondary select-none" 
                                                      x-text="perm.name.replace(/-/g, ' ')"></span>
                                            </template>
                                            <span x-show="role.permissions.length > 5" class="text-xs text-secondary px-1 self-center">
                                                +<span x-text="role.permissions.length - 5"></span> more
                                            </span>
                                        </div>
                                    </template>
                                    <span x-show="!role.permissions || role.permissions.length === 0" class="text-xs text-secondary italic opacity-50">
                                        No permissions assigned
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center align-top" x-show="showCols.users_count">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-blue-100 bg-blue-600 rounded-full" x-text="role.users_count || 0"></span>
                            </td>

                            <td class="px-6 py-4 text-right align-top">
                                <div class="flex justify-end gap-2 transition-opacity">
                                    <button 
                                        @can('role-edit') @click="openPermissionModal(role)" @endcan
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors
                                               @can('role-edit') bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 hover:bg-yellow-100 @else bg-gray-100 text-gray-400 cursor-not-allowed @endcan"
                                        @cannot('role-edit') disabled title="No Permission" @endcannot>
                                        <i class="ri-shield-keyhole-line"></i>
                                    </button>

                                    <button 
                                        @can('role-edit') @click="openModal('edit', role)" @endcan
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors
                                               @can('role-edit') bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100 @else bg-gray-100 text-gray-400 cursor-not-allowed @endcan"
                                        @cannot('role-edit') disabled title="No Permission" @endcannot>
                                        <i class="ri-pencil-line"></i>
                                    </button>

                                    <button 
                                        @can('role-delete') @click="confirmDelete(role.id)" @endcan
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors
                                               @can('role-delete') bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 @else bg-gray-100 text-gray-400 cursor-not-allowed @endcan"
                                        @cannot('role-delete') disabled title="No Permission" @endcannot>
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="roles.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-shield-line text-4xl mb-2 inline-block"></i>
                            <p>No roles found.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <x-pagination x-model="perPage" @change="fetchRoles()" />

    </div>

    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="relative w-full max-w-md bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
                <div>
                    <h3 class="text-lg font-bold text-text-color" x-text="editMode ? 'Edit Role' : 'Create New Role'"></h3>
                    <template x-if="isSequenceMode">
                        <p class="text-xs text-primary font-bold mt-1">
                            Editing role <span x-text="currentSeqIndex + 1"></span> of <span x-text="sequenceQueue.length"></span>
                        </p>
                    </template>
                </div>
                <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>

            <form @submit.prevent="submitForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Role Name</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="e.g., Manager">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div class="pt-4 flex justify-between items-center border-t border-border-color mt-2">
                    <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">
                        Skip <i class="ri-arrow-right-line align-middle"></i>
                    </button>
                    <div x-show="!isSequenceMode"></div> 
                    <div class="flex gap-3">
                        <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">Cancel</button>
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                            <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                            <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? 'Finish' : 'Save & Next') : (editMode ? 'Update' : 'Save')"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div x-show="isPermissionModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="isPermissionModalOpen = false"></div>
        <div class="relative w-full max-w-4xl bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden flex flex-col max-h-[90vh]" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600">
                        <i class="ri-shield-keyhole-line text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-text-color">Assign Permissions</h3>
                        <p class="text-xs text-secondary">Role: <span class="font-bold text-primary" x-text="permissionForm.roleName"></span></p>
                    </div>
                </div>
                <button @click="isPermissionModalOpen = false" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>

            <div class="overflow-y-auto p-6 bg-card-bg">
                <div class="flex justify-between items-center mb-4">
                    <label class="text-sm font-bold text-text-color">Select Permissions</label>
                    <div class="flex gap-3">
                        <button type="button" @click="selectAllPermissions()" class="text-xs text-primary font-bold hover:underline">Select All</button>
                        <button type="button" @click="permissionForm.permissions = []" class="text-xs text-red-500 font-bold hover:underline">Uncheck All</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($permissions as $perm)
                    <label class="flex items-center space-x-3 p-3 rounded-xl border border-input-border bg-input-bg hover:border-primary/50 cursor-pointer transition-all hover:shadow-sm select-none">
                        <div class="relative flex items-center">
                            <input type="checkbox" value="{{ $perm->name }}" x-model="permissionForm.permissions"
                                   class="peer w-5 h-5 cursor-pointer appearance-none rounded border border-input-border checked:bg-primary checked:border-primary transition-all">
                            <i class="ri-check-line absolute text-white text-sm opacity-0 peer-checked:opacity-100 pointer-events-none left-[2px]"></i>
                        </div>
                        <span class="text-sm text-text-color capitalize font-medium">{{ str_replace('-', ' ', $perm->name) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="px-6 py-4 border-t border-border-color bg-page-bg/30 flex justify-end gap-3 flex-shrink-0">
                <button type="button" @click="isPermissionModalOpen = false" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">Cancel</button>
                <button type="button" @click="submitPermissions" class="bg-yellow-500 text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                    <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                    <span x-text="isLoading ? 'Saving...' : 'Save Permissions'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function roleManagement() {
        return {
            roles: [],
            search: '',
            isLoading: false,
            pagination: {},
            errors: {},
            
            // New: Bulk Features
            perPage: '10',
            selectedIds: [],
            selectAll: false,
            
            // New: Sequential Edit
            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,
            
            showCols: JSON.parse(localStorage.getItem('role_table_cols')) || { permissions: true, users_count: true },

            // Modal State
            isModalOpen: false, editMode: false, form: { id: null, name: '' },
            isPermissionModalOpen: false, permissionForm: { roleId: null, roleName: '', permissions: [] },

            init() {
                this.$watch('showCols', (value) => localStorage.setItem('role_table_cols', JSON.stringify(value)));
                this.fetchRoles();
            },

            async fetchRoles(url = "{{ route('admin.roles.fetch') }}") {
                const params = new URLSearchParams();
                if(this.search) params.append('keyword', this.search);
                params.append('per_page', this.perPage);
                
                url = url.split('?')[0] + '?' + params.toString();

                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    this.roles = data.data;
                    this.pagination = { total: data.total, from: data.from, to: data.to, prev_page_url: data.prev_page_url, next_page_url: data.next_page_url };
                    
                    // Reset Checkboxes on refresh
                    this.selectedIds = [];
                    this.selectAll = false;
                } catch (e) { console.error(e); }
            },
            
            changePage(url) { if(url) this.fetchRoles(url); },

            // Checkbox Logic
            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.roles.map(role => role.id) : [];
            },

            // --- BULK EDIT (Sequential) ---
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.roles.filter(role => selectedIdsString.includes(String(role.id)));
                
                if (this.sequenceQueue.length === 0) return;

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
                    this.fetchRoles();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Bulk update completed!' } }));
                }
            },

            // --- CRUD Logic ---
            openModal(mode, role = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                if (mode === 'edit') {
                    this.loadRoleToForm(role);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '' };
                }
            },

            loadRoleToForm(role) {
                this.editMode = true;
                this.form = { id: role.id, name: role.name };
                this.errors = {};
            },

            closeModal(force = false) {
                if (!force && this.isSequenceMode && !confirm("Stop editing sequence?")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.fetchRoles();
            },

            async submitForm() {
                this.isLoading = true; this.errors = {};
                let url = this.editMode ? `/admin/roles/${this.form.id}` : "{{ route('admin.roles.store') }}";
                let method = this.editMode ? 'PUT' : 'POST';
                
                try {
                    const res = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json',
                        'Accept': 'application/json', // <--- បន្ថែមបន្ទាត់នេះ
                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(this.form)
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        if (res.status === 422) this.errors = data.errors;
                        else window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Error occurred!' } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        if (this.isSequenceMode) this.nextInSequence();
                        else { this.isModalOpen = false; this.fetchRoles(); }
                    }
                } catch (e) { console.error(e); } finally { this.isLoading = false; }
            },

            // --- DELETE Logic ---
            async confirmDelete(id) {
                askConfirm(async () => { await this.performDelete([id]); });
            },

            async confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                askConfirm(async () => { await this.performDelete(this.selectedIds, true); });
            },

            async performDelete(ids, isBulk = false) {
                let url = isBulk ? "{{ route('admin.roles.bulk_delete') }}" : `/admin/roles/${ids[0]}`;
                let method = isBulk ? 'POST' : 'DELETE';
                let body = isBulk ? JSON.stringify({ ids: ids }) : null;

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: body
                    });
                    
                    const data = await response.json();

                    if(response.ok) {
                        this.selectedIds = [];
                        this.selectAll = false;
                        this.fetchRoles();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    } else {
                        // Handle error (e.g., Role has users assigned - 422 Unprocessable Entity)
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Cannot delete.' } }));
                    }
                } catch(e) { console.error(e); }
            },

            // --- PERMISSION Logic ---
            async openPermissionModal(role) {
                this.permissionForm.roleId = role.id;
                this.permissionForm.roleName = role.name;
                this.isPermissionModalOpen = true;
                try {
                    const res = await fetch(`/admin/assign-permissions/${role.id}`);
                    this.permissionForm.permissions = await res.json(); 
                } catch (e) { console.error(e); }
            },

            selectAllPermissions() {
                this.permissionForm.permissions = @json($permissions->pluck('name'));
            },

            async submitPermissions() {
                this.isLoading = true;
                try {
                    const res = await fetch("{{ route('admin.assign_permissions.update') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify({ role_id: this.permissionForm.roleId, permissions: this.permissionForm.permissions })
                    });
                    const data = await res.json();
                    if(res.ok) {
                        this.isPermissionModalOpen = false;
                        this.fetchRoles();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    }
                } catch (e) { console.error(e); } finally { this.isLoading = false; }
            }
        }
    }
</script>
@endsection