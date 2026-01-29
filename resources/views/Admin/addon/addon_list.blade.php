@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-1 py-1" x-data="addonManagement()">
    
    {{-- =========================================== --}}
    {{-- 1. HEADER & ACTIONS                         --}}
    {{-- =========================================== --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-puzzle-line"></i>
                {{ __('messages.addon_management') }}
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            {{-- Selected Actions (Edit Sequence & Bulk Delete) --}}
            <div x-show="selectedIds.length > 0" x-transition 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                 <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1" x-text="selectedIds.length + ' {{ __('messages.selected_items') }}'"></span>
                
                <div class="flex gap-1">
                    @can('addon-edit')
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition" title="{{ __('messages.edit_sequence') }}">
                        <i class="ri-edit-circle-line"></i>
                    </button>
                    @endcan

                    @can('addon-delete')
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition" title="{{ __('messages.delete_selected') }}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Column Visibility --}}
            <div class="relative w-full sm:w-auto" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" 
                        class="w-full sm:w-auto flex justify-center items-center gap-2 px-3 py-2.5 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm">
                    <i class="ri-layout-column-line"></i> <span class="sm:hidden lg:inline">{{ __('messages.columns') }}</span>
                </button>
                <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.name" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.addon_name') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.price" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.price') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.type" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Destination</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.created_at" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.created_at') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchAddons()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="{{ __('messages.search_placeholder') }}">
            </div>

            {{-- Create Button --}}
            <button 
                @can('addon-create') @click="openModal('create')" @endcan
                class="w-full sm:w-auto text-white font-bold py-2.5 px-6 rounded-xl flex justify-center items-center gap-2 transition-all shadow-lg shadow-primary/30 whitespace-nowrap
                @can('addon-create') bg-primary hover:opacity-90 @else bg-gray-400 cursor-not-allowed opacity-70 @endcan"
                @cannot('addon-create') disabled @endcannot
            >
                <i class="ri-add-circle-line text-xl"></i>
                <span>{{ __('messages.add_addon') }}</span>
            </button>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 2. TABLE LIST                               --}}
    {{-- =========================================== --}}
    <div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 w-4">
                            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </th>
                        
                        {{-- Name --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" 
                            @click="sort('name')" x-show="showCols.name">
                            <div class="flex items-center gap-1">
                                {{ __('messages.addon_name') }}
                                <div class="flex flex-col text-[10px] leading-[0.5] opacity-50 group-hover:opacity-100">
                                    <i class="ri-arrow-up-s-fill" :class="sortBy === 'name' && sortDir === 'asc' ? 'text-primary' : ''"></i>
                                    <i class="ri-arrow-down-s-fill" :class="sortBy === 'name' && sortDir === 'desc' ? 'text-primary' : ''"></i>
                                </div>
                            </div>
                        </th>

                        {{-- Price --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" 
                            @click="sort('price')" x-show="showCols.price">
                            <div class="flex items-center gap-1">
                                {{ __('messages.price') }}
                                <div class="flex flex-col text-[10px] leading-[0.5] opacity-50 group-hover:opacity-100">
                                    <i class="ri-arrow-up-s-fill" :class="sortBy === 'price' && sortDir === 'asc' ? 'text-primary' : ''"></i>
                                    <i class="ri-arrow-down-s-fill" :class="sortBy === 'price' && sortDir === 'desc' ? 'text-primary' : ''"></i>
                                </div>
                            </div>
                        </th>

                        {{-- Type (Kitchen/Bar) --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" 
                            @click="sort('type')" x-show="showCols.type">
                            <div class="flex items-center gap-1">
                                Destination (Type)
                                <div class="flex flex-col text-[10px] leading-[0.5] opacity-50 group-hover:opacity-100">
                                    <i class="ri-arrow-up-s-fill" :class="sortBy === 'type' && sortDir === 'asc' ? 'text-primary' : ''"></i>
                                    <i class="ri-arrow-down-s-fill" :class="sortBy === 'type' && sortDir === 'desc' ? 'text-primary' : ''"></i>
                                </div>
                            </div>
                        </th>

                        {{-- ✅ NEW: Status Column --}}
                        <th class="px-6 py-4 font-bold">{{ __('messages.status') }}</th>

                        {{-- Date --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" 
                            @click="sort('created_at')" x-show="showCols.created_at">
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
                    <template x-for="item in addons" :key="item.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                            </td>
                            
                            {{-- Name --}}
                            <td class="px-6 py-4 font-bold text-text-color" x-text="item.name" x-show="showCols.name"></td>
                            
                            {{-- Price --}}
                            <td class="px-6 py-4 font-bold text-primary" x-text="'$' + parseFloat(item.price).toFixed(2)" x-show="showCols.price"></td>

                            {{-- Type --}}
                            <td class="px-6 py-4" x-show="showCols.type">
                                <span class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                                      :class="item.type === 'kitchen' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600'"
                                      x-text="item.type">
                                </span>
                            </td>

                            {{-- ✅ NEW: Status Toggle --}}
                            <td class="px-6 py-4">
                                <button @click="toggleStatus(item.id)" 
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                        :class="(item.is_active == 1 || item.is_active == true) ? 'bg-green-500' : 'bg-gray-300'">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm"
                                        :class="(item.is_active == 1 || item.is_active == true) ? 'translate-x-6' : 'translate-x-1'"></span>
                                </button>
                            </td>

                            {{-- Date --}}
                            <td class="px-6 py-4 text-secondary text-sm" x-text="new Date(item.created_at).toLocaleDateString()" x-show="showCols.created_at"></td>
                            
                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @can('addon-edit') @click="openModal('edit', item)" @endcan
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100">
                                            <i class="ri-pencil-line"></i>
                                    </button>
                                    <button @can('addon-delete') @click="confirmDelete(item.id)" @endcan
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100">
                                            <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="addons.length === 0">
                        <td colspan="100%" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-puzzle-line text-4xl mb-2 inline-block"></i>
                            <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <x-pagination />
    </div>

    {{-- =========================================== --}}
    {{-- 3. CREATE / EDIT MODAL                      --}}
    {{-- =========================================== --}}
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative w-full max-w-md bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
                <div>
                    <h3 class="text-lg font-bold text-text-color" x-text="editMode ? '{{ __('messages.edit') }} Addon' : '{{ __('messages.create') }} Addon'"></h3>
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
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.addon_name') }}</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="Ex: Extra Shot, Sugar">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- Price --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.price') }} ($)</label>
                    <input type="number" step="0.01" x-model="form.price" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="0.00">
                    <p x-show="errors.price" x-text="errors.price" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Destination (Type)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer border border-input-border rounded-lg p-3 flex items-center justify-center gap-2 transition-all"
                               :class="form.type === 'kitchen' ? 'bg-primary/10 border-primary text-primary' : 'hover:bg-page-bg'">
                            <input type="radio" x-model="form.type" value="kitchen" class="hidden">
                            <i class="ri-restaurant-line"></i> Kitchen
                        </label>
                        <label class="cursor-pointer border border-input-border rounded-lg p-3 flex items-center justify-center gap-2 transition-all"
                               :class="form.type === 'bar' ? 'bg-primary/10 border-primary text-primary' : 'hover:bg-page-bg'">
                            <input type="radio" x-model="form.type" value="bar" class="hidden">
                            <i class="ri-cup-line"></i> Bar
                        </label>
                    </div>
                    <p x-show="errors.type" x-text="errors.type" class="text-red-500 text-xs mt-1"></p>
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
    function addonManagement() {
        return {
            addons: [],
            search: '',
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            selectedIds: [],
            selectAll: false,

            // Config
            showCols: JSON.parse(localStorage.getItem('addon_table_cols')) || { name: true, price: true, type: true, created_at: true },
            sortBy: 'created_at',
            sortDir: 'desc',

            // Sequence Edit
            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            // Form
            form: { id: null, name: '', price: '', type: 'kitchen' },
            errors: {},

            init() { 
                this.$watch('showCols', (value) => { localStorage.setItem('addon_table_cols', JSON.stringify(value)); });
                this.fetchAddons(); 
            },

            async fetchAddons() {
                let url = "{{ route('admin.addons.fetch') }}";
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
                    this.addons = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            sort(col) {
                if (this.sortBy === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                else { this.sortBy = col; this.sortDir = 'desc'; }
                this.fetchAddons();
            },

            gotoPage(page) { this.currentPage = page; this.fetchAddons(); },
            changePage(url) { if(url) this.fetchAddons(); },

            toggleSelectAll() { this.selectedIds = this.selectAll ? this.addons.map(t => t.id) : []; },

            // ✅ កែសម្រួល៖ Toggle Status (Optimistic Update)
            async toggleStatus(id) {
                // ១. រកមើលទីតាំងរបស់ Addon ក្នុង Array
                const index = this.addons.findIndex(item => item.id === id);
                if (index === -1) return;

                // ២. ទុកតម្លៃដើមសិន (ក្រែងលោ Server Error យើងប្ដូរមកវិញ)
                const originalState = this.addons[index].is_active;

                // ៣. ប្ដូរស្ថានភាពភ្លាមៗ (កុំអាលចាំ Server) -> ធ្វើអោយប៊ូតុងដើរលឿន
                // បើ 1 ប្ដូរទៅ 0, បើ 0 ប្ដូរទៅ 1
                this.addons[index].is_active = (originalState == 1 || originalState == true) ? 0 : 1;

                try {
                    // ៤. ផ្ញើទៅ Server
                    const response = await fetch(`/admin/addons/${id}/toggle`, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        }
                    });

                    if (!response.ok) throw new Error('Failed');

                    // ៥. Success
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Status updated successfully' } }));
                    
                    // មិនបាច់ហៅ this.fetchAddons() ទៀតទេ ព្រោះយើងបាន Update ក្នុង Array ខាងលើរួចហើយ
                    
                } catch(e) { 
                    console.error(e);
                    // ៦. បើមានបញ្ហា (Error) -> ប្ដូរមកស្ថានភាពដើមវិញ
                    this.addons[index].is_active = originalState;
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Failed to update status' } }));
                }
            },

            // Sequence Logic (Preserved)
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.addons.filter(item => selectedIdsString.includes(String(item.id)));
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
                    this.form = { id: null, name: '', price: '', type: 'kitchen' };
                }
            },

            closeModal(force = false) {
                 if (!force && this.isSequenceMode && !confirm("{{ __('messages.confirm_stop_sequence') }}")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchAddons(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                let url = this.editMode ? `/admin/addons/${this.form.id}` : "{{ route('admin.addons.store') }}";
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
                        if (this.isSequenceMode) { this.nextInSequence(); } else { this.closeModal(); this.fetchAddons(); }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            async confirmDelete(id) { askConfirm(async () => { await this.performDelete([id]); }); },
            async confirmBulkDelete() { if (this.selectedIds.length === 0) return; askConfirm(async () => { await this.performDelete(this.selectedIds, true); }); },

            async performDelete(ids, isBulk = false) {
                let url = isBulk ? "{{ route('admin.addons.bulk_delete') }}" : `/admin/addons/${ids[0]}`;
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
                        this.fetchAddons();
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