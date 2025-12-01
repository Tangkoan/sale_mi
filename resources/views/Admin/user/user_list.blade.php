@extends('admin.dashboard')

@section('content')
<div class="w-full h-full px-6 py-5" 
     x-data="userManagement()" 
     x-init="fetchUsers()">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-team-line text-primary"></i> User Management
            </h1>
            <p class="text-sm text-secondary mt-1">Manage users, roles and permissions.</p>
        </div>

        <div class="flex gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchUsers()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary"
                       placeholder="Search users...">
            </div>

            <button @click="openModal('create')" 
                    class="bg-primary hover:opacity-90 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary/30 transition-all flex items-center gap-2">
                <i class="ri-user-add-line"></i> <span class="hidden sm:inline">Add User</span>
            </button>
        </div>
    </div>

    <div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold">User</th>
                        <th class="px-6 py-4 font-bold">Role</th>
                        <th class="px-6 py-4 font-bold">Email</th>
                        <th class="px-6 py-4 font-bold">Created At</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="user in users" :key="user.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden border border-border-color">
                                        <img x-show="user.avatar" :src="'/storage/' + user.avatar" class="w-full h-full object-cover">
                                        <span x-show="!user.avatar" x-text="user.name.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-text-color" x-text="user.name"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <template x-for="role in user.roles">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800" x-text="role.name"></span>
                                </template>
                            </td>
                            <td class="px-6 py-4 text-secondary text-sm" x-text="user.email"></td>
                            <td class="px-6 py-4 text-secondary text-sm" x-text="new Date(user.created_at).toLocaleDateString()"></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openModal('edit', user)" class="h-8 w-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100 transition-colors flex items-center justify-center">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <button @click="confirmDelete(user.id)" class="h-8 w-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 transition-colors flex items-center justify-center">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="users.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-ghost-line text-4xl mb-2 inline-block"></i>
                            <p>No users found matching your search.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-border-color flex justify-between items-center" x-show="pagination.total > 0">
            <span class="text-sm text-secondary">Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> results</span>
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

        <div class="relative w-full max-w-lg bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30">
                <h3 class="text-lg font-bold text-text-color" x-text="editMode ? 'Edit User' : 'Create New User'"></h3>
                <button @click="isModalOpen = false" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>

            <form @submit.prevent="submitForm" class="p-6 space-y-4">
                
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Full Name</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="Enter name">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Email Address</label>
                    <input type="email" x-model="form.email" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="Enter email">
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
                    <input type="password" x-model="form.password" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="••••••••">
                    <p x-show="errors.password" x-text="errors.password" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-border-color mt-2">
                    <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">Cancel</button>
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                        <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                        <span x-text="editMode ? 'Update' : 'Save'"></span>
                    </button>
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
            form: {
                id: null,
                name: '',
                email: '',
                role: '',
                password: ''
            },
            errors: {},

            // 1. Fetch Users
            async fetchUsers(url = "{{ route('admin.users.fetch') }}") {
                // Add search query to URL
                if(this.search) {
                    url = url.includes('?') ? `${url}&keyword=${this.search}` : `${url}?keyword=${this.search}`;
                }

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
                } catch (error) {
                    console.error('Error fetching users:', error);
                }
            },

            // Pagination Helper
            changePage(url) {
                if(url) this.fetchUsers(url);
            },

            // 2. Open Modal
            openModal(mode, user = null) {
                this.isModalOpen = true;
                this.errors = {};
                this.form.password = ''; // Clear password

                if (mode === 'edit') {
                    this.editMode = true;
                    this.form.id = user.id;
                    this.form.name = user.name;
                    this.form.email = user.email;
                    // Get first role name if exists
                    this.form.role = user.roles.length > 0 ? user.roles[0].name : '';
                } else {
                    this.editMode = false;
                    this.form.id = null;
                    this.form.name = '';
                    this.form.email = '';
                    this.form.role = '';
                }
            },

            // 3. Submit Form (Create / Update)
            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                
                let url = this.editMode ? `/admin/users/${this.form.id}` : "{{ route('admin.users.store') }}";
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
                            this.errors = data.errors; // Validation errors
                        } else {
                            // Show Toast Error (using your existing toast system)
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Something went wrong!' } }));
                        }
                    } else {
                        // Success
                        this.isModalOpen = false;
                        this.fetchUsers(); // Refresh list
                        
                        // Show Toast Success
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    this.isLoading = false;
                }
            },

            // 4. Delete User
            async confirmDelete(id) {
                if(!confirm('Are you sure you want to delete this user?')) return;

                try {
                    const response = await fetch(`/admin/users/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if(response.ok) {
                        this.fetchUsers();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    }
                } catch (error) {
                    console.error(error);
                }
            }
        }
    }
</script>
@endsection