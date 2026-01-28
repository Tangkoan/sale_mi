@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-1 py-1" x-data="tableManagement()">
    
    {{-- HEADER --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-table-line"></i>
                {{ __('messages.table_management') }}
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            {{-- Selected Actions --}}
            <div x-show="selectedIds.length > 0" x-transition 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                 <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1" x-text="selectedIds.length + ' {{ __('messages.selected_items') }}'"></span>
                
                <div class="flex gap-1">
                    @can('table-edit')
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition">
                        <i class="ri-edit-circle-line"></i>
                    </button>
                    @endcan

                    @can('table-delete')
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    @endcan
                </div>
            </div>

            {{-- COLUMN VISIBILITY TOGGLE (បន្ថែមថ្មីត្រង់នេះ) --}}
            <div class="relative w-full sm:w-auto" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" 
                        class="w-full sm:w-auto flex justify-center items-center gap-2 px-3 py-2.5 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm">
                    <i class="ri-layout-column-line"></i> <span class="sm:hidden lg:inline">{{ __('messages.columns') }}</span>
                </button>
                
                <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.name" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.table_name') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.status" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.status') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.created_at" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.created_at') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchTables()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="{{ __('messages.search_placeholder') }}">
            </div>

            {{-- Create Button --}}
            <button 
                @can('table-create') @click="openModal('create')" @endcan
                class="w-full sm:w-auto text-white font-bold py-2.5 px-6 rounded-xl flex justify-center items-center gap-2 transition-all shadow-lg shadow-primary/30 whitespace-nowrap
                @can('table-create') bg-primary hover:opacity-90 @else bg-gray-400 cursor-not-allowed opacity-70 @endcan"
                @cannot('table-create') disabled @endcannot
            >
                <i class="ri-add-circle-line text-xl"></i>
                <span>{{ __('messages.add_table') }}</span>
            </button>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 w-4">
                            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </th>
                        
                        {{-- SORTABLE + SHOW/HIDE: Name --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" 
                            @click="sort('name')" 
                            x-show="showCols.name"> {{-- បន្ថែម x-show --}}
                            <div class="flex items-center gap-1">
                                {{ __('messages.table_name') }}
                                <div class="flex flex-col text-[10px] leading-[0.5] opacity-50 group-hover:opacity-100">
                                    <i class="ri-arrow-up-s-fill" :class="sortBy === 'name' && sortDir === 'asc' ? 'text-primary' : ''"></i>
                                    <i class="ri-arrow-down-s-fill" :class="sortBy === 'name' && sortDir === 'desc' ? 'text-primary' : ''"></i>
                                </div>
                            </div>
                        </th>

                        {{-- SORTABLE + SHOW/HIDE: Status --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" 
                            @click="sort('status')"
                            x-show="showCols.status"> {{-- បន្ថែម x-show --}}
                            <div class="flex items-center gap-1">
                                {{ __('messages.status') }}
                                <div class="flex flex-col text-[10px] leading-[0.5] opacity-50 group-hover:opacity-100">
                                    <i class="ri-arrow-up-s-fill" :class="sortBy === 'status' && sortDir === 'asc' ? 'text-primary' : ''"></i>
                                    <i class="ri-arrow-down-s-fill" :class="sortBy === 'status' && sortDir === 'desc' ? 'text-primary' : ''"></i>
                                </div>
                            </div>
                        </th>

                        {{-- SORTABLE + SHOW/HIDE: Created At --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" 
                            @click="sort('created_at')"
                            x-show="showCols.created_at"> {{-- បន្ថែម x-show --}}
                            <div class="flex items-center gap-1">
                                {{ __('messages.created_at') }}
                                <div class="flex flex-col text-[10px] leading-[0.5] opacity-50 group-hover:opacity-100">
                                    <i class="ri-arrow-up-s-fill" :class="sortBy === 'created_at' && sortDir === 'asc' ? 'text-primary' : ''"></i>
                                    <i class="ri-arrow-down-s-fill" :class="sortBy === 'created_at' && sortDir === 'desc' ? 'text-primary' : ''"></i>
                                </div>
                            </div>
                        </th>

                        <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="item in tables" :key="item.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                            </td>
                            
                            {{-- Name (Show/Hide) --}}
                            <td class="px-6 py-4 font-bold text-text-color" x-text="item.name" x-show="showCols.name"></td>
                            
                            {{-- Status Badge (Show/Hide) --}}
                            <td class="px-6 py-4" x-show="showCols.status">
                                <span class="px-3 py-1 rounded-full text-xs font-bold capitalize flex items-center w-fit gap-1"
                                      :class="item.status === 'available' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                                      <span class="w-2 h-2 rounded-full" :class="item.status === 'available' ? 'bg-green-600' : 'bg-red-600'"></span>
                                      <span x-text="item.status"></span>
                                </span>
                            </td>

                            {{-- Created At (Show/Hide) --}}
                            <td class="px-6 py-4 text-secondary text-sm" x-text="new Date(item.created_at).toLocaleDateString()" x-show="showCols.created_at"></td>
                            
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @can('table-edit') @click="openModal('edit', item)" @endcan
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100">
                                            <i class="ri-pencil-line"></i>
                                    </button>
                                    <button @can('table-delete') @click="confirmDelete(item.id)" @endcan
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100">
                                            <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="tables.length === 0">
                        {{-- កែ colspan អោយត្រូវនឹងចំនួន Column ដែលកំពុង Show --}}
                        <td colspan="100%" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-layout-grid-line text-4xl mb-2 inline-block"></i>
                            <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <x-pagination />
    </div>

    {{-- MODAL (រក្សាទុកដដែល) --}}
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative w-full max-w-md bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
                <div>
                    <h3 class="text-lg font-bold text-text-color" x-text="editMode ? '{{ __('messages.edit') }} Table' : '{{ __('messages.create') }} Table'"></h3>
                    <template x-if="isSequenceMode">
                        <p class="text-xs text-primary font-bold mt-1">
                            {{ __('messages.edit') }} <span x-text="currentSeqIndex + 1"></span> {{ __('messages.of') }} <span x-text="sequenceQueue.length"></span>
                        </p>
                    </template>
                </div>
                <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>
            
            <form @submit.prevent="submitForm" class="p-6 space-y-4">
                
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.table_name') }}</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="Ex: T-01, VIP-1">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.status') }}</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer border border-input-border rounded-lg p-3 flex items-center justify-center gap-2 transition-all"
                               :class="form.status === 'available' ? 'bg-green-100 border-green-500 text-green-700' : 'hover:bg-page-bg'">
                            <input type="radio" x-model="form.status" value="available" class="hidden">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Available
                        </label>
                        <label class="cursor-pointer border border-input-border rounded-lg p-3 flex items-center justify-center gap-2 transition-all"
                               :class="form.status === 'busy' ? 'bg-red-100 border-red-500 text-red-700' : 'hover:bg-page-bg'">
                            <input type="radio" x-model="form.status" value="busy" class="hidden">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Busy
                        </label>
                    </div>
                    <p x-show="errors.status" x-text="errors.status" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div class="pt-4 flex justify-between items-center border-t border-border-color mt-2">
                    <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">
                        {{ __('messages.skip_this_user') }} <i class="ri-arrow-right-line align-middle"></i>
                    </button>
                    <div x-show="!isSequenceMode"></div> 

                    <div class="flex gap-3">
                        <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                            <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                            <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? '{{ __('messages.finish') }}' : '{{ __('messages.save_and_next') }}') : (editMode ? '{{ __('messages.update') }}' : '{{ __('messages.save') }}')"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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

            // Column Visibility (Load from LocalStorage)
            showCols: JSON.parse(localStorage.getItem('table_table_cols')) || { 
                name: true, 
                status: true, 
                created_at: true 
            },

            // Sorting Variables
            sortBy: 'created_at',
            sortDir: 'desc',

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', status: 'available' },
            errors: {},

            init() { 
                // Watch for changes in showCols and save to LocalStorage
                this.$watch('showCols', (value) => {
                    localStorage.setItem('table_table_cols', JSON.stringify(value));
                });
                this.fetchTables(); 
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
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            // Sort Function
            sort(col) {
                if (this.sortBy === col) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = col;
                    this.sortDir = 'desc'; 
                }
                this.fetchTables();
            },

            gotoPage(page) { this.currentPage = page; this.fetchTables(); },
            changePage(url) { if(url) this.fetchTables(); },

            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.tables.map(t => t.id) : [];
            },

            // Sequence Logic
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
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
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

            async confirmDelete(id) {
                askConfirm(async () => { await this.performDelete([id]); });
            },

            async confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                askConfirm(async () => { await this.performDelete(this.selectedIds, true); });
            },

            async performDelete(ids, isBulk = false) {
                let url = isBulk ? "{{ route('admin.tables.bulk_delete') }}" : `/admin/tables/${ids[0]}`;
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