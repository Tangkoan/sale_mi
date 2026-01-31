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
            
            {{-- Selected Actions --}}
            <div x-show="selectedIds.length > 0" x-transition 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                 <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1" x-text="selectedIds.length + ' {{ __('messages.selected_items') }}'"></span>
                
                <div class="flex gap-1">
                    @can('category-edit')
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition" title="{{ __('messages.edit_sequence') }}">
                        <i class="ri-edit-circle-line"></i>
                    </button>
                    @endcan

                    @can('category-delete')
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
                            <input type="checkbox" x-model="showCols.image" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.image') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.destination" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Destination</span>
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
                        {{-- Destination Column --}}
                        <th class="px-6 py-4 font-bold" x-show="showCols.destination">Destination</th>
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
                            
                            {{-- ✅ កែ៖ បង្ហាញ Destination Name ពី Relationship --}}
                            <td class="px-6 py-4" x-show="showCols.destination">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200 inline-flex items-center gap-1"
                                      x-show="item.destination">
                                    <i class="ri-printer-line text-sm"></i>
                                    <span x-text="item.destination ? item.destination.name : 'N/A'"></span>
                                </span>
                                <span x-show="!item.destination" class="text-xs text-secondary italic">Not Assigned</span>
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
            
            <form @submit.prevent="submitForm" class="p-6 space-y-5">
                
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.category_name') }}</label>
                    <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- ✅ កែ៖ Destination Selection (Dropdown from Table) --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Destination (Printer Location)</label>
                    <div class="relative">
                        <select x-model="form.kitchen_destination_id" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none appearance-none">
                            <option value="">Select Destination</option>
                            <template x-for="dest in destinations" :key="dest.id">
                                <option :value="dest.id" x-text="dest.name"></option>
                            </template>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="ri-arrow-down-s-line text-secondary"></i>
                        </div>
                    </div>
                    <p x-show="errors.kitchen_destination_id" x-text="errors.kitchen_destination_id" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- Image Upload --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.image') }}</label>
                    <div class="flex items-center gap-4 p-3 border border-dashed border-input-border rounded-xl bg-page-bg/30">
                        <div class="h-16 w-16 rounded-lg bg-gray-100 border border-border-color overflow-hidden flex-shrink-0 relative group">
                            <template x-if="imagePreview">
                                <img :src="imagePreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!imagePreview">
                                <div class="w-full h-full flex items-center justify-center text-secondary bg-white"><i class="ri-image-add-line text-2xl"></i></div>
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="file" @change="handleFileUpload" accept="image/*" class="block w-full text-sm text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-primary file:text-white hover:file:bg-primary/90 cursor-pointer">
                            <p class="text-[10px] text-secondary mt-1">Supported: JPEG, PNG, JPG (Max 2MB)</p>
                        </div>
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
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2 shadow-lg shadow-primary/30" :disabled="isLoading">
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
            // ✅ បន្ថែម៖ List សម្រាប់ Dropdown
            destinations: @json($destinations), 
            search: '',
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            selectedIds: [],
            selectAll: false,

            showCols: JSON.parse(localStorage.getItem('category_table_cols')) || { 
                image: true, 
                destination: true, 
                created_at: true 
            },

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            // ✅ កែ៖ form.kitchen_destination_id
            form: { id: null, name: '', kitchen_destination_id: '', image: null },
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
                // ✅ កែ៖ load kitchen_destination_id
                this.form = { ...item, image: null, kitchen_destination_id: item.kitchen_destination_id || '' }; 
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
                    // Reset Form
                    this.form = { id: null, name: '', kitchen_destination_id: '', image: null };
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
                // ✅ កែ៖ append kitchen_destination_id
                formData.append('kitchen_destination_id', this.form.kitchen_destination_id);
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
            
            async confirmDelete(id) {
                askConfirm(async () => { await this.performDelete([id]); });
            },
            async confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                askConfirm(async () => { await this.performDelete(this.selectedIds, true); });
            },
            async performDelete(ids, isBulk = false) {
                let url = isBulk ? "{{ route('admin.categories.bulk_delete') }}" : `/admin/categories/${ids[0]}`;
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