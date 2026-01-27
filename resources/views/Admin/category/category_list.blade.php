@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-1 py-1" x-data="categoryManagement()">
    
    {{-- HEADER --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-folder-open-line"></i>
                {{ __('messages.category_management') }}
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            {{-- Selected Actions (Bulk Delete & Edit Sequence) --}}
            <div x-show="selectedIds.length > 0" x-transition 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                 <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1" x-text="selectedIds.length + ' {{ __('messages.selected_items') }}'"></span>
                
                <div class="flex gap-1">
                    {{-- Edit Sequence Button --}}
                    @can('category-edit')
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition" title="{{ __('messages.edit_sequence') }}">
                        <i class="ri-edit-circle-line"></i>
                    </button>
                    @endcan

                    {{-- Bulk Delete Button --}}
                    @can('category-delete')
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition" title="{{ __('messages.delete_selected') }}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Column Visibility Toggle --}}
            <div class="relative w-full sm:w-auto" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" 
                        class="w-full sm:w-auto flex justify-center items-center gap-2 px-3 py-2.5 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm">
                    <i class="ri-layout-column-line"></i> <span class="sm:hidden lg:inline">{{ __('messages.columns') }}</span>
                </button>
                <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.image" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.image') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.type" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Type</span>
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
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchCategories()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="{{ __('messages.search_placeholder') }}">
            </div>

            {{-- Create Button --}}
            <button 
                @can('category-create') @click="openModal('create')" @endcan
                class="w-full sm:w-auto text-white font-bold py-2.5 px-6 rounded-xl flex justify-center items-center gap-2 transition-all shadow-lg shadow-primary/30 whitespace-nowrap
                @can('category-create') bg-primary hover:opacity-90 @else bg-gray-400 cursor-not-allowed opacity-70 @endcan"
                @cannot('category-create') disabled @endcannot
            >
                <i class="ri-add-circle-line text-xl"></i>
                <span>{{ __('messages.add_category') }}</span>
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
                        <th class="px-6 py-4 font-bold" x-show="showCols.image">{{ __('messages.image') }}</th>
                        <th class="px-6 py-4 font-bold">{{ __('messages.category_name') }}</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.type">{{ __('messages.status') }} (Type)</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.created_at">{{ __('messages.created_at') }}</th>
                        <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="item in categories" :key="item.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                            </td>
                            {{-- Image Column --}}
                            <td class="px-6 py-4" x-show="showCols.image">
                                <div class="h-10 w-10 rounded-lg bg-gray-100 overflow-hidden border border-border-color">
                                    <template x-if="item.image">
                                        <img :src="'/storage/' + item.image" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image">
                                        <div class="w-full h-full flex items-center justify-center text-secondary">
                                            <i class="ri-image-line"></i>
                                        </div>
                                    </template>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-text-color" x-text="item.name"></td>
                            {{-- Type Badge --}}
                            <td class="px-6 py-4" x-show="showCols.type">
                                <span class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                                      :class="item.type === 'food' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600'"
                                      x-text="item.type">
                                </span>
                            </td>
                            <td class="px-6 py-4 text-secondary text-sm" x-show="showCols.created_at" x-text="new Date(item.created_at).toLocaleDateString()"></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @can('category-edit') @click="openModal('edit', item)" @endcan
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100">
                                            <i class="ri-pencil-line"></i>
                                    </button>
                                    <button @can('category-delete') @click="confirmDelete(item.id)" @endcan
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100">
                                            <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="categories.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-inbox-line text-4xl mb-2 inline-block"></i>
                            <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <x-pagination />
    </div>

    {{-- MODAL --}}
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative w-full max-w-md bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            {{-- Modal Header with Sequence Info --}}
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
                <div>
                    <h3 class="text-lg font-bold text-text-color" x-text="editMode ? '{{ __('messages.edit') }} Category' : '{{ __('messages.create') }} Category'"></h3>
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
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.category_name') }}</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Type (Food/Drink)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer border border-input-border rounded-lg p-3 flex items-center justify-center gap-2 transition-all"
                               :class="form.type === 'food' ? 'bg-primary/10 border-primary text-primary' : 'hover:bg-page-bg'">
                            <input type="radio" x-model="form.type" value="food" class="hidden">
                            <i class="ri-restaurant-line"></i> Food
                        </label>
                        <label class="cursor-pointer border border-input-border rounded-lg p-3 flex items-center justify-center gap-2 transition-all"
                               :class="form.type === 'drink' ? 'bg-primary/10 border-primary text-primary' : 'hover:bg-page-bg'">
                            <input type="radio" x-model="form.type" value="drink" class="hidden">
                            <i class="ri-cup-line"></i> Drink
                        </label>
                    </div>
                    <p x-show="errors.type" x-text="errors.type" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- Image Upload --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.image') }}</label>
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 rounded-lg bg-gray-100 border border-border-color overflow-hidden flex-shrink-0">
                            <template x-if="imagePreview">
                                <img :src="imagePreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!imagePreview">
                                <div class="w-full h-full flex items-center justify-center text-secondary"><i class="ri-image-add-line"></i></div>
                            </template>
                        </div>
                        <input type="file" @change="handleFileUpload" accept="image/*" class="text-sm text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    </div>
                    <p x-show="errors.image" x-text="errors.image" class="text-red-500 text-xs mt-1"></p>
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
    function categoryManagement() {
        return {
            categories: [],
            search: '',
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            selectedIds: [],
            selectAll: false,

            // Column Visibility
            showCols: JSON.parse(localStorage.getItem('category_table_cols')) || { 
                image: true, 
                type: true, 
                created_at: true 
            },

            // Sequence Edit Variables
            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', type: 'food', image: null },
            imagePreview: null,
            errors: {},

            init() { 
                this.$watch('showCols', (value) => {
                    localStorage.setItem('category_table_cols', JSON.stringify(value));
                });
                this.fetchCategories(); 
            },

            async fetchCategories() {
                let url = "{{ route('admin.categories.fetch') }}";
                const params = new URLSearchParams({
                    keyword: this.search,
                    per_page: this.perPage,
                    page: this.currentPage
                });
                
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.categories = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            gotoPage(page) { this.currentPage = page; this.fetchCategories(); },
            changePage(url) { if(url) this.fetchCategories(); },

            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.categories.map(c => c.id) : [];
            },

            handleFileUpload(e) {
                const file = e.target.files[0];
                if (file) {
                    this.form.image = file;
                    this.imagePreview = URL.createObjectURL(file);
                }
            },

            // ... (Sequence Edit Logic រក្សាទុកដដែល) ...
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.categories.filter(item => 
                    selectedIdsString.includes(String(item.id))
                );
                
                if (this.sequenceQueue.length === 0) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.select_users_first') }}" } })); 
                    return;
                }

                this.isSequenceMode = true;
                this.currentSeqIndex = 0;
                this.loadCategoryToForm(this.sequenceQueue[0]);
                this.isModalOpen = true;
            },

            nextInSequence() {
                this.currentSeqIndex++;
                if (this.currentSeqIndex < this.sequenceQueue.length) {
                    this.loadCategoryToForm(this.sequenceQueue[this.currentSeqIndex]);
                } else {
                    this.closeModal(true); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.all_users_updated') }}" } }));
                }
            },

            loadCategoryToForm(item) {
                this.editMode = true;
                this.errors = {};
                this.form = { ...item, image: null }; 
                this.imagePreview = item.image ? '/storage/' + item.image : null;
            },

            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                this.imagePreview = null;
                
                if (mode === 'edit') {
                    this.loadCategoryToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', type: 'food', image: null };
                }
            },

            closeModal(force = false) {
                 if (!force && this.isSequenceMode && !confirm("{{ __('messages.confirm_stop_sequence') }}")) {
                    return;
                }
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchCategories(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                
                let formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('type', this.form.type);
                if (this.form.image instanceof File) {
                    formData.append('image', this.form.image);
                }

                let url = "{{ route('admin.categories.store') }}";
                
                if (this.editMode) {
                    url = `/admin/categories/${this.form.id}`;
                    formData.append('_method', 'POST'); 
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: formData
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
                            this.fetchCategories();
                        }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },
            
            // ===========================================
            // DELETE LOGIC (UPDATED TO USE askConfirm)
            // ===========================================

            // 1. Single Delete
            async confirmDelete(id) {
                // ប្រើ askConfirm ជំនួស confirm()
                askConfirm(async () => {
                    await this.performDelete([id]);
                });
            },

            // 2. Bulk Delete
            async confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                
                // ប្រើ askConfirm ជំនួស confirm()
                askConfirm(async () => {
                    await this.performDelete(this.selectedIds, true);
                });
            },

            // 3. Shared Perform Delete Function
            async performDelete(ids, isBulk = false) {
                let url = isBulk ? "{{ route('admin.categories.bulk_delete') }}" : `/admin/categories/${ids[0]}`;
                let method = isBulk ? 'POST' : 'DELETE';
                
                // Note: Delete method មិនត្រូវការ body ទេ ប៉ុន្តែ Bulk Delete (POST) ត្រូវការ
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
                        this.fetchCategories();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message || "{{ __('messages.delete_success') }}" } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || "{{ __('messages.delete_fail') }}" } }));
                    }
                } catch(e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.network_error') }}" } }));
                }
            }
        }
    }
</script>
@endsection