@extends('admin.dashboard')

@section('content')
<div class="w-full h-full px-6 py-5" 
     x-data="roleManagement()" 
     x-init="fetchRoles()">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-shield-user-line text-primary"></i> Role Management
            </h1>
            <p class="text-sm text-secondary mt-1">Define roles and assign permissions.</p>
        </div>

        <div class="flex gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchRoles()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary"
                       placeholder="Search roles...">
            </div>

            <button @click="openModal('create')" 
                    class="bg-primary hover:opacity-90 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary/30 transition-all flex items-center gap-2">
                <i class="ri-add-circle-line"></i> <span class="hidden sm:inline">Add Role</span>
            </button>
        </div>
    </div>

    <div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold">Role Name</th>
                        <th class="px-6 py-4 font-bold">Permissions</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="role in roles" :key="role.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="font-bold text-text-color text-lg" x-text="role.name"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="perm in role.permissions">
                                        <span class="px-2 py-1 rounded text-xs font-medium bg-input-bg border border-input-border text-secondary" x-text="perm.name"></span>
                                    </template>
                                    <span x-show="role.permissions.length === 0" class="text-xs text-secondary italic">No permissions assigned</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openModal('edit', role)" class="h-8 w-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100 transition-colors flex items-center justify-center">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <button @click="confirmDelete(role.id)" class="h-8 w-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 transition-colors flex items-center justify-center">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="roles.length === 0">
                        <td colspan="3" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-shield-line text-4xl mb-2 inline-block"></i>
                            <p>No roles found.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-border-color flex justify-between items-center" x-show="pagination.total > 0">
            <span class="text-sm text-secondary">Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> roles</span>
            <div class="flex gap-2">
                <button @click="changePage(pagination.prev_page_url)" :disabled="!pagination.prev_page_url" class="px-3 py-1 rounded border border-input-border text-text-color disabled:opacity-50">Prev</button>
                <button @click="changePage(pagination.next_page_url)" :disabled="!pagination.next_page_url" class="px-3 py-1 rounded border border-input-border text-text-color disabled:opacity-50">Next</button>
            </div>
        </div>
    </div>

    <div x-show="isModalOpen" 
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center px-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="isModalOpen = false"></div>

        <div class="relative w-full max-w-2xl bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30 flex-shrink-0">
                <h3 class="text-lg font-bold text-text-color" x-text="editMode ? 'Edit Role' : 'Create New Role'"></h3>
                <button @click="isModalOpen = false" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>

            <div class="overflow-y-auto p-6">
                <form @submit.prevent="submitForm" id="roleForm" class="space-y-6">
                    
                    <div>
                        <label class="block text-sm font-bold text-text-color mb-1">Role Name</label>
                        <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="e.g., Manager">
                        <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-text-color mb-3">Assign Permissions</label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($permissions as $perm)
                            <label class="flex items-center space-x-3 p-3 rounded-lg border border-input-border bg-input-bg hover:border-primary/50 cursor-pointer transition-colors">
                                <input type="checkbox" value="{{ $perm->name }}" x-model="form.permissions"
                                       class="w-4 h-4 text-primary bg-card-bg border-input-border rounded focus:ring-primary">
                                <span class="text-sm text-text-color select-none capitalize">{{ str_replace('-', ' ', $perm->name) }}</span>
                            </label>
                            @endforeach
                        </div>
                        <p x-show="errors.permissions" x-text="errors.permissions" class="text-red-500 text-xs mt-1"></p>
                    </div>

                </form>
            </div>

            <div class="px-6 py-4 border-t border-border-color bg-page-bg/30 flex justify-end gap-3 flex-shrink-0">
                <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">Cancel</button>
                <button type="submit" form="roleForm" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                    <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                    <span x-text="editMode ? 'Update' : 'Save'"></span>
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
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            pagination: {},
            form: {
                id: null,
                name: '',
                permissions: [] // Array សម្រាប់ផ្ទុក Permission ដែលបានធីក
            },
            errors: {},

            async fetchRoles(url = "{{ route('admin.roles.fetch') }}") {
                if(this.search) url = url.includes('?') ? `${url}&keyword=${this.search}` : `${url}?keyword=${this.search}`;
                
                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    this.roles = data.data;
                    this.pagination = {
                        total: data.total, from: data.from, to: data.to,
                        prev_page_url: data.prev_page_url, next_page_url: data.next_page_url
                    };
                } catch (error) { console.error(error); }
            },

            changePage(url) { if(url) this.fetchRoles(url); },

            openModal(mode, role = null) {
                this.isModalOpen = true;
                this.errors = {};
                
                if (mode === 'edit') {
                    this.editMode = true;
                    this.form.id = role.id;
                    this.form.name = role.name;
                    // Map permissions from object to array of names
                    this.form.permissions = role.permissions.map(p => p.name);
                } else {
                    this.editMode = false;
                    this.form.id = null;
                    this.form.name = '';
                    this.form.permissions = [];
                }
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
                        if (response.status === 422) this.errors = data.errors;
                        else window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Something went wrong!' } }));
                    } else {
                        this.isModalOpen = false;
                        this.fetchRoles();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            async confirmDelete(id) {
                if(!confirm('Delete this role? This action cannot be undone.')) return;
                try {
                    const response = await fetch(`/admin/roles/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    const data = await response.json();
                    if(response.ok) {
                        this.fetchRoles();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    }
                } catch (error) { console.error(error); }
            }
        }
    }
</script>
@endsection