@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-1 py-1" x-data="userManagement()">
    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                {{-- <i class="ri-team-line text-primary"></i> --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                User Management
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            <div x-show="selectedIds.length > 0" x-transition.opacity.duration.300ms 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1" x-text="selectedIds.length + ' selected'"></span>
                
                <div class="flex gap-1">
                    @can('user-edit')
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition" title="Edit Sequence">
                        <i class="ri-edit-circle-line"></i>
                    </button>
                    @endcan

                    @can('user-delete')
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition" title="Delete Selected">
                        <i class="ri-delete-bin-line"></i>
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
                            <input type="checkbox" x-model="showCols.role" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Role</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.email" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Email</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.created_at" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Created At</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchUsers()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="Search users...">
            </div>

            <button 
                @can('user-create') @click="openModal('create')" @endcan
                class="w-full sm:w-auto text-white font-bold py-2.5 px-6 rounded-xl flex justify-center items-center gap-2 transition-all shadow-lg shadow-primary/30 whitespace-nowrap
                @can('user-create') bg-primary hover:opacity-90 @else bg-gray-400 cursor-not-allowed opacity-70 @endcan"
                @cannot('user-create') disabled title="No Permission" @endcannot
            >
                {{-- <i class="ri-user-add-line"></i>  --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-plus-icon lucide-user-plus"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                <span>Add User</span>
            </button>
        </div>
    </div>
    <div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 w-4">
                            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </th>
                        <th class="px-6 py-4 font-bold">User</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.role">Role</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.email">Email</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.created_at">Created At</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="user in users" :key="user.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(user.id)}">
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="user.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <x-avatar /> <div>
                                        <p class="font-bold text-text-color" x-text="user.name"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4" x-show="showCols.role">
                                <template x-for="role in user.roles">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800" x-text="role.name"></span>
                                </template>
                            </td>
                            <td class="px-6 py-4 text-secondary text-sm" x-show="showCols.email" x-text="user.email"></td>
                            <td class="px-6 py-4 text-secondary text-sm" x-show="showCols.created_at" x-text="new Date(user.created_at).toLocaleDateString()"></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 transition-opacity">
                                    <button @can('user-edit') @click="openModal('edit', user)" @endcan
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors
                                            @can('user-edit') bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100 @else bg-gray-100 cursor-not-allowed @endcan"
                                            @cannot('user-edit') disabled @endcannot>
                                            <i class="ri-pencil-line"></i>
                                    </button>
                                    <button @can('user-delete') @click="confirmDelete(user.id)" @endcan
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors
                                            @can('user-delete') bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 @else bg-gray-100 cursor-not-allowed @endcan"
                                            @cannot('user-delete') disabled @endcannot>
                                            <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="users.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-ghost-line text-4xl mb-2 inline-block"></i>
                            <p>No users found matching your search.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <x-pagination x-model="perPage" @change="fetchUsers()" />
    </div>

    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative w-full max-w-lg bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
                <div>
                    <h3 class="text-lg font-bold text-text-color" x-text="editMode ? 'Edit User' : 'Create New User'"></h3>
                    <template x-if="isSequenceMode">
                        <p class="text-xs text-primary font-bold mt-1">
                            Editing user <span x-text="currentSeqIndex + 1"></span> of <span x-text="sequenceQueue.length"></span>
                        </p>
                    </template>
                </div>
                <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>
            
            <form @submit.prevent="submitForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Full Name</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Email Address</label>
                    <input type="email" x-model="form.email" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <p x-show="errors.email" x-text="errors.email" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Assign Role</label>
                    <select x-model="form.role" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                        <option value="" disabled>Select a role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <p x-show="errors.role" x-text="errors.role" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-color mb-1" x-text="editMode ? 'New Password (Optional)' : 'Password'"></label>
                    <input type="password" x-model="form.password" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                </div>

                <div class="pt-4 flex justify-between items-center border-t border-border-color mt-2">
                    <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">
                        Skip this user <i class="ri-arrow-right-line align-middle"></i>
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
</div>

<script>
    function userManagement() {
        return {
            users: [],
            search: '',
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            pagination: {},
            
            perPage: '10',

            showCols: JSON.parse(localStorage.getItem('user_table_cols')) || { 
                role: true, 
                email: true, 
                created_at: true 
            },

            selectedIds: [],
            selectAll: false,
            
            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', email: '', role: '', password: '' },
            errors: {},

            init() {
                this.$watch('showCols', (value) => {
                    localStorage.setItem('user_table_cols', JSON.stringify(value));
                });
                this.fetchUsers();
            },

            async fetchUsers(url = "{{ route('admin.users.fetch') }}") {
                const params = new URLSearchParams();
                if(this.search) params.append('keyword', this.search);
                params.append('per_page', this.perPage);
                
                url = url.split('?')[0] + '?' + params.toString();

                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    this.users = data.data;
                    this.pagination = {
                        total: data.total,
                        from: data.from,
                        to: data.to,
                        prev_page_url: data.prev_page_url,
                        next_page_url: data.next_page_url
                    };
                    
                    this.selectedIds = [];
                    this.selectAll = false;
                } catch (error) { console.error(error); }
            },

            changePage(url) { if(url) this.fetchUsers(url); },

            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.users.map(user => user.id) : [];
            },

            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.users.filter(user => 
                    selectedIdsString.includes(String(user.id))
                );
                
                if (this.sequenceQueue.length === 0) {
                    alert("Please select users first (Error: ID Mismatch)"); 
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
                    this.isModalOpen = false;
                    this.isSequenceMode = false;
                    this.selectedIds = [];
                    this.selectAll = false;
                    this.fetchUsers();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'All selected users updated!' } }));
                }
            },

            loadUserToForm(user) {
                this.editMode = true;
                this.errors = {};
                this.form.id = user.id;
                this.form.name = user.name;
                this.form.email = user.email;
                this.form.role = user.roles.length > 0 ? user.roles[0].name : '';
                this.form.password = '';
            },

            closeModal(force = false) {
                if (!force && this.isSequenceMode && !confirm("Stop editing sequence?")) {
                    return;
                }
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = []; 
                this.fetchUsers();
            },

            openModal(mode, user = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                if (mode === 'edit') {
                    this.loadUserToForm(user);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', email: '', role: '', password: '' };
                    this.errors = {};
                }
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                let url = this.editMode ? `/admin/users/${this.form.id}` : "{{ route('admin.users.store') }}";
                let method = this.editMode ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(this.form)
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        // [SECURITY FIX] Handle 403 Forbidden (If admin tries to hack role)
                        if (response.status === 403) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message } }));
                            this.closeModal(true);
                        }
                        else if (response.status === 422) {
                            this.errors = data.errors;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Please fix the errors below.' } }));
                        } 
                        else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Something went wrong!' } }));
                        }
                    } else {
                        // Success
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        if (this.isSequenceMode) {
                            this.nextInSequence();
                        } else {
                            this.isModalOpen = false;
                            this.fetchUsers();
                        }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            async confirmDelete(id) {
                askConfirm(async () => {
                    await this.performDelete([id]);
                });
            },

            async confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                askConfirm(async () => {
                    await this.performDelete(this.selectedIds, true);
                });
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
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message || 'Deleted successfully' } }));
                    } else {
                        // [SECURITY FIX] Handle delete errors (e.g., trying to delete Super Admin)
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Failed to delete.' } }));
                    }
                } catch(e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Network Error' } }));
                }
            }
        }
    }
</script>
@endsection