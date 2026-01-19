@extends('admin.dashboard')

@section('content')
<div class="w-full h-full px-1 py-1" x-data="permissionManagement()">
    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-key-round-icon lucide-key-round"><path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"/><circle cx="16.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                {{ __('messages.permission_management') }}
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            <div x-show="selectedIds.length > 0" x-transition.opacity.duration.300ms 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                
                <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1 whitespace-nowrap">
                    <span x-text="selectedIds.length"></span> {{ __('messages.selected') }}
                </span>
                
                <div class="flex gap-1">
                    @role('Super Admin')
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition border border-transparent hover:border-blue-100" title="{{ __('messages.edit') }}">
                        <i class="ri-edit-circle-line mr-1"></i> <span class="hidden sm:inline">{{ __('messages.edit') }}</span>
                    </button>
                    @endrole

                    @role('Super Admin')
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition border border-transparent hover:border-red-100" title="{{ __('messages.delete') }}">
                        <i class="ri-delete-bin-line mr-1"></i> <span class="hidden sm:inline">{{ __('messages.delete') }}</span>
                    </button>
                    @endrole
                </div>
            </div>

            <div class="relative w-full sm:w-auto" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" 
                        class="w-full sm:w-auto flex justify-center items-center gap-2 px-3 py-2.5 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm">
                    <i class="ri-layout-column-line"></i> <span class="sm:hidden lg:inline">{{ __('messages.columns') }}</span>
                </button>
                <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.guard_name" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.guard_name') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.created_at" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.created_at') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchPermissions()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="{{ __('messages.search_placeholder_permission') }}">
            </div>

            <button 
                @role('Super Admin') @click="openModal('create')" @endrole
                class="w-full sm:w-auto text-white font-bold py-2.5 px-6 rounded-xl flex justify-center items-center gap-2 transition-all shadow-lg shadow-primary/30 whitespace-nowrap
                @unlessrole('Super Admin') bg-gray-400 cursor-not-allowed opacity-70 @else bg-primary hover:opacity-90 @endunlessrole"
                @unlessrole('Super Admin') disabled title="Restricted" @endunlessrole>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-plus-icon lucide-circle-plus"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                <span class="hidden sm:inline">{{ __('messages.add_permission') }}</span>
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
                        <th class="px-6 py-4 font-bold">{{ __('messages.th_permission_name') }}</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.guard_name">{{ __('messages.guard_name') }}</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.created_at">{{ __('messages.created_at') }}</th>
                        <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="perm in permissions" :key="perm.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(perm.id)}">
                            
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="perm.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4 cursor-pointer">
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-input-bg border border-input-border flex items-center justify-center text-secondary">
                                        <i class="ri-shield-keyhole-line"></i>
                                    </div>
                                    <span class="font-bold text-text-color text-lg" x-text="perm.name"></span>
                                </div>
                            </td>

                            <td class="px-6 py-4" x-show="showCols.guard_name">
                                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-600 border border-blue-200" x-text="perm.guard_name"></span>
                            </td>

                            <td class="px-6 py-4 text-secondary text-sm" x-show="showCols.created_at" x-text="new Date(perm.created_at).toLocaleDateString()"></td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 transition-opacity">
                                    <button 
                                        @role('Super Admin') @click="openModal('edit', perm)" @endrole
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors
                                        @role('Super Admin') bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-100 @else bg-gray-100 text-gray-400 cursor-not-allowed @endrole"
                                        @unlessrole('Super Admin') disabled @endunlessrole>
                                        <i class="ri-pencil-line"></i>
                                    </button>

                                    <button 
                                        @role('Super Admin') @click="confirmDelete(perm.id)" @endrole
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors
                                        @role('Super Admin') bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 @else bg-gray-100 text-gray-400 cursor-not-allowed @endrole"
                                        @unlessrole('Super Admin') disabled @endunlessrole>
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="permissions.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-file-search-line text-4xl mb-2 inline-block opacity-50"></i>
                            <p>{{ __('messages.no_permissions_found') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <x-pagination/>

    </div>

    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="relative w-full max-w-md bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
                <div>
                    <h3 class="text-lg font-bold text-text-color" x-text="editMode ? '{{ __('messages.edit_permission') }}' : '{{ __('messages.create_permission') }}'"></h3>
                    <template x-if="isSequenceMode">
                        <p class="text-xs text-primary font-bold mt-1">
                            {{ __('messages.editing_sequence', ['current' => '<span x-text="currentSeqIndex + 1"></span>', 'total' => '<span x-text="sequenceQueue.length"></span>']) }}
                        </p>
                    </template>
                </div>
                <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>

            <form @submit.prevent="submitForm" class="p-6 space-y-4">
                
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.th_permission_name') }}</label>
                    <input type="text" x-model="form.name" 
                           class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" 
                           placeholder="e.g., post-create">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div class="pt-4 flex justify-between items-center border-t border-border-color mt-2">
                    <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">
                        {{ __('messages.skip') }} <i class="ri-arrow-right-line align-middle"></i>
                    </button>
                    <div x-show="!isSequenceMode"></div> 
                    <div class="flex gap-3">
                        <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                            <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                            <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? '{{ __('messages.finish') }}' : '{{ __('messages.save_next') }}') : (editMode ? '{{ __('messages.update') }}' : '{{ __('messages.save') }}')"></span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <div x-data="{ 
            open: false, 
            callback: null,
            title: '{{ __('messages.confirm_delete_title') }}', 
            message: '{{ __('messages.confirm_delete_msg') }}',
            
            init() {
                window.askConfirm = (actionCallback) => {
                    this.callback = actionCallback;
                    this.open = true;
                }
            },

            confirm() {
                if (this.callback) {
                    this.callback();
                }
                this.close();
            },

            close() {
                this.open = false;
                setTimeout(() => { this.callback = null }, 300);
            }
        }"
        @keydown.escape.window="close()"
        x-show="open"
        style="display: none;"
        class="relative z-[200]" 
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true" x-cloak>

        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="open"
                     @click.away="close()"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-bold leading-6 text-gray-900" x-text="title"></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" x-text="message"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                        <button type="button" @click="confirm()" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-bold text-white shadow-lg shadow-red-600/30 hover:bg-red-500 sm:w-auto transition-all">
                            {{ __('messages.btn_yes_confirm') }}
                        </button>
                        <button type="button" @click="close()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white text-gray-900 px-3 py-2 text-sm font-bold shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            {{ __('messages.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function permissionManagement() {
        return {
            // Data & Loading
            permissions: [], 
            search: '', 
            isLoading: false, 
            errors: {},
            
            // Pagination Variables
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 

            // Selection & Filters
            selectedIds: [],
            selectAll: false,
            showCols: JSON.parse(localStorage.getItem('perm_table_cols')) || { guard_name: true, created_at: true },

            // Modal States
            isModalOpen: false, editMode: false, form: { id: null, name: '' },
            isSequenceMode: false, sequenceQueue: [], currentSeqIndex: 0,

            init() {
                this.$watch('showCols', (value) => localStorage.setItem('perm_table_cols', JSON.stringify(value)));
                this.fetchPermissions();
            },

            async fetchPermissions() {
                let url = "{{ route('admin.permissions.fetch') }}";
                const params = new URLSearchParams();
                
                if(this.search) params.append('keyword', this.search);
                params.append('per_page', this.perPage);
                params.append('page', this.currentPage); 

                url = url.split('?')[0] + '?' + params.toString();

                this.isLoading = true;
                try {
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    
                    this.permissions = data.data;
                    
                    this.pagination = { 
                        total: data.total, 
                        from: data.from, 
                        to: data.to, 
                        current_page: data.current_page,
                        last_page: data.last_page, 
                        prev_page_url: data.prev_page_url, 
                        next_page_url: data.next_page_url 
                    };
                    
                    this.currentPage = data.current_page;
                    
                    this.selectedIds = [];
                    this.selectAll = false;
                } catch (e) { 
                    console.error(e);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.network_error') }}" } }));
                } finally {
                    this.isLoading = false;
                }
            },
            
            gotoPage(page) {
                if (page < 1 || (this.pagination.last_page && page > this.pagination.last_page)) return;
                this.currentPage = page;
                this.fetchPermissions();
            },

            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.permissions.map(p => p.id) : [];
            },

            // --- BULK EDIT (Sequential) ---
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.permissions.filter(p => selectedIdsString.includes(String(p.id)));
                if (this.sequenceQueue.length === 0) return;

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
                    this.fetchPermissions();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.bulk_update_complete') }}" } }));
                }
            },

            // --- CRUD Logic ---
            openModal(mode, perm = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                if (mode === 'edit') {
                    this.loadPermToForm(perm);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '' };
                }
            },

            loadPermToForm(perm) {
                this.editMode = true;
                this.form = { id: perm.id, name: perm.name };
                this.errors = {};
            },

            closeModal(force = false) {
                if (!force && this.isSequenceMode && !confirm("{{ __('messages.stop_editing_sequence') }}")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.fetchPermissions();
            },

            async submitForm() {
                this.isLoading = true; this.errors = {};
                let url = this.editMode ? `/admin/permissions/${this.form.id}` : "{{ route('admin.permissions.store') }}";
                let method = this.editMode ? 'PUT' : 'POST';
                
                try {
                    const res = await fetch(url, {
                        method: method,
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify(this.form)
                    });
                    
                    const data = await res.json();

                    if (!res.ok) {
                        if (res.status === 422) {
                            this.errors = data.errors;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.check_input') }}" } }));
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || "{{ __('messages.error_generic') }}" } }));
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        if (this.isSequenceMode) this.nextInSequence();
                        else { this.isModalOpen = false; this.fetchPermissions(); }
                    }
                } catch (e) { 
                    console.error(e);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.network_error') }}" } }));
                } finally { 
                    this.isLoading = false; 
                }
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
                let url = isBulk ? "{{ route('admin.permissions.bulk_delete') }}" : `/admin/permissions/${ids[0]}`;
                let method = isBulk ? 'POST' : 'DELETE';
                let body = isBulk ? JSON.stringify({ ids: ids }) : null;

                try {
                    const res = await fetch(url, {
                        method: method,
                        headers: { 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: body
                    });
                    
                    const data = await res.json();
                    
                    if(res.ok) {
                        this.selectedIds = [];
                        this.selectAll = false;
                        this.fetchPermissions();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || "{{ __('messages.error_delete_generic') }}" } }));
                    }
                } catch (e) { 
                    console.error(e);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.network_error') }}" } }));
                }
            }
        }
    }
</script>
@endsection