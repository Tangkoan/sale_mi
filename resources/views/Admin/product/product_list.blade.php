@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-1 py-1" x-data="productManagement()">
    
    {{-- HEADER --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-shopping-bag-3-line"></i>
                {{ __('messages.product_management') }}
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            {{-- Selected Actions --}}
            <div x-show="selectedIds.length > 0" x-transition 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                 <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1" x-text="selectedIds.length + ' {{ __('messages.selected_items') }}'"></span>
                
                <div class="flex gap-1">
                    @can('product-edit')
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition">
                        <i class="ri-edit-circle-line"></i>
                    </button>
                    @endcan

                    @can('product-delete')
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Column Visibility --}}
            <div class="relative w-full sm:w-auto" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" class="w-full sm:w-auto flex justify-center items-center gap-2 px-3 py-2.5 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm">
                    <i class="ri-layout-column-line"></i> <span class="sm:hidden lg:inline">{{ __('messages.columns') }}</span>
                </button>
                <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.image" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.image') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.category" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.category') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.price" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.price') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Category Filter --}}
            <div class="relative w-full sm:w-48">
                <select x-model="filterCategory" @change="fetchProducts()" class="w-full px-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-sm shadow-sm">
                    <option value="">{{ __('messages.all_categories') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchProducts()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="{{ __('messages.search_placeholder') }}">
            </div>

            {{-- Create Button --}}
            <button 
                @can('product-create') @click="openModal('create')" @endcan
                class="w-full sm:w-auto text-white font-bold py-2.5 px-6 rounded-xl flex justify-center items-center gap-2 transition-all shadow-lg shadow-primary/30 whitespace-nowrap
                @can('product-create') bg-primary hover:opacity-90 @else bg-gray-400 cursor-not-allowed opacity-70 @endcan"
                @cannot('product-create') disabled @endcannot
            >
                <i class="ri-add-circle-line text-xl"></i>
                <span>{{ __('messages.add_product') }}</span>
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
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('name')">
                            <div class="flex items-center gap-1">{{ __('messages.product_name') }}<i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                        </th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.category">{{ __('messages.category') }}</th>
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('price')" x-show="showCols.price">
                            <div class="flex items-center gap-1">{{ __('messages.price') }}<i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                        </th>
                        <th class="px-6 py-4 font-bold">{{ __('messages.status') }}</th>
                        <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="item in products" :key="item.id">
                        <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                            </td>
                            <td class="px-6 py-4" x-show="showCols.image">
                                <div class="h-10 w-10 rounded-lg bg-gray-100 overflow-hidden border border-border-color">
                                    <template x-if="item.image"><img :src="'/storage/' + item.image" class="w-full h-full object-cover"></template>
                                    <template x-if="!item.image"><div class="w-full h-full flex items-center justify-center text-secondary"><i class="ri-image-line"></i></div></template>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-text-color" x-text="item.name"></td>
                            <td class="px-6 py-4" x-show="showCols.category">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border"
                                      :class="item.category ? (item.category.destination === 'kitchen' ? 'bg-orange-50 text-orange-600 border-orange-200' : 'bg-blue-50 text-blue-600 border-blue-200') : 'bg-gray-100'">
                                    <span x-text="item.category ? item.category.name : 'N/A'"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-primary" x-text="'$' + parseFloat(item.price).toFixed(2)" x-show="showCols.price"></td>
                            <td class="px-6 py-4">
                                <button @click="toggleStatus(item.id)" 
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                        :class="item.is_active ? 'bg-green-500' : 'bg-gray-300'">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                          :class="item.is_active ? 'translate-x-6' : 'translate-x-1'"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @can('product-edit') @click="openModal('edit', item)" @endcan class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100"><i class="ri-pencil-line"></i></button>
                                    <button @can('product-delete') @click="confirmDelete(item.id)" @endcan class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100"><i class="ri-delete-bin-line"></i></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="products.length === 0">
                        <td colspan="100%" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-shopping-bag-3-line text-4xl mb-2 inline-block"></i>
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

        <div class="relative w-full max-w-2xl bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30">
                <div>
                    <h3 class="text-lg font-bold text-text-color" x-text="editMode ? '{{ __('messages.edit') }} Product' : '{{ __('messages.create') }} Product'"></h3>
                    <template x-if="isSequenceMode">
                        <p class="text-xs text-primary font-bold mt-1">
                            {{ __('messages.edit') }} <span x-text="currentSeqIndex + 1"></span> {{ __('messages.of') }} <span x-text="sequenceQueue.length"></span>
                        </p>
                    </template>
                </div>
                <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>
            
            <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.product_name') }}</label>
                        <input type="text" x-model="form.name" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                        <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.category') }}</label>
                        <select x-model="form.category_id" @change="filterAddonsByType()" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->destination) }})</option>
                            @endforeach
                        </select>
                        <p x-show="errors.category_id" x-text="errors.category_id" class="text-red-500 text-xs mt-1"></p>
                    </div>
                </div>

                {{-- Price & Image --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.price') }} ($)</label>
                        <input type="number" step="0.01" x-model="form.price" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                        <p x-show="errors.price" x-text="errors.price" class="text-red-500 text-xs mt-1"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-text-color mb-1">{{ __('messages.image') }}</label>
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-lg bg-gray-100 border border-border-color overflow-hidden flex-shrink-0">
                                <template x-if="imagePreview"><img :src="imagePreview" class="w-full h-full object-cover"></template>
                                <template x-if="!imagePreview"><div class="w-full h-full flex items-center justify-center text-secondary"><i class="ri-image-add-line"></i></div></template>
                            </div>
                            <input type="file" @change="handleFileUpload" accept="image/*" class="text-sm text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        </div>
                    </div>
                </div>

                {{-- ADDONS SELECTION (Smart Filtered) --}}
                <div class="border-t border-border-color pt-4" x-show="visibleAddons.length > 0">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-bold text-text-color">Available Addons</label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-primary font-bold hover:text-primary/80">
                            <input type="checkbox" x-model="selectAllAddons" @change="toggleSelectAllAddons()" class="rounded border-input-border text-primary focus:ring-primary h-3.5 w-3.5">
                            Select All
                        </label>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-40 overflow-y-auto p-2 bg-page-bg/50 rounded-lg border border-input-border custom-scrollbar">
                        <template x-for="addon in visibleAddons" :key="addon.id">
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white dark:hover:bg-white/5 p-2 rounded transition-colors border border-transparent hover:border-input-border">
                                <input type="checkbox" :value="addon.id" x-model="form.addons" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                                <div class="text-xs">
                                    <span class="font-bold text-text-color block" x-text="addon.name"></span>
                                    <span class="text-secondary" x-text="'+$' + parseFloat(addon.price).toFixed(2)"></span>
                                </div>
                            </label>
                        </template>
                    </div>
                    <p class="text-xs text-secondary mt-1">Filtering addons based on category destination.</p>
                </div>
                
                <div class="border-t border-border-color pt-4 text-center text-sm text-secondary italic" x-show="!form.category_id">
                    Please select a category to see available addons.
                </div>
                
                <div class="border-t border-border-color pt-4 text-center text-sm text-secondary italic" x-show="form.category_id && visibleAddons.length === 0">
                    No addons available for this category type.
                </div>

            </form>

            <div class="p-6 pt-0 flex justify-between items-center border-t border-border-color mt-auto bg-card-bg z-10 pt-4">
                <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">
                    {{ __('messages.skip_this_user') }} <i class="ri-arrow-right-line align-middle"></i>
                </button>
                <div x-show="!isSequenceMode"></div> 

                <div class="flex gap-3">
                    <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">{{ __('messages.cancel') }}</button>
                    <button type="button" @click="submitForm" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                        <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                        <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? '{{ __('messages.finish') }}' : '{{ __('messages.save_and_next') }}') : (editMode ? '{{ __('messages.update') }}' : '{{ __('messages.save') }}')"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function productManagement() {
        return {
            products: [],
            categories: @json($categories), 
            allAddons: @json($addons),      
            visibleAddons: [],              
            
            search: '',
            filterCategory: '',
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            selectedIds: [],
            selectAll: false,
            selectAllAddons: false,

            showCols: JSON.parse(localStorage.getItem('product_table_cols')) || { image: true, category: true, price: true },
            sortBy: 'created_at',
            sortDir: 'desc',

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', category_id: '', price: '', image: null, addons: [] },
            imagePreview: null,
            errors: {},

            init() { 
                this.$watch('showCols', (value) => { localStorage.setItem('product_table_cols', JSON.stringify(value)); });
                // Watch: ពេលប្តូរ Category ក្នុង Form -> Filter Addons ភ្លាម
                this.$watch('form.category_id', (value) => {
                    this.filterAddonsByType();
                });
                this.fetchProducts(); 
            },

            // ✅ Logic ថ្មី: Filter Addons តាម Destination របស់ Category
            filterAddonsByType() {
                if (!this.form.category_id) {
                    this.visibleAddons = [];
                    return;
                }
                const selectedCat = this.categories.find(c => c.id == this.form.category_id);
                
                if (selectedCat) {
                    // Filter Addons ដែលមាន type ដូច destination របស់ category
                    this.visibleAddons = this.allAddons.filter(a => a.type === selectedCat.destination);
                } else {
                    this.visibleAddons = [];
                }
                this.selectAllAddons = false; 
            },

            // ✅ Logic ថ្មី: Select All Addons
            toggleSelectAllAddons() {
                if (this.selectAllAddons) {
                    this.visibleAddons.forEach(addon => {
                        if (!this.form.addons.includes(addon.id)) {
                            this.form.addons.push(addon.id);
                        }
                    });
                } else {
                    const visibleIds = this.visibleAddons.map(a => a.id);
                    this.form.addons = this.form.addons.filter(id => !visibleIds.includes(id));
                }
            },

            async fetchProducts() {
                let url = "{{ route('admin.products.fetch') }}";
                const params = new URLSearchParams({
                    keyword: this.search,
                    category_id: this.filterCategory,
                    per_page: this.perPage,
                    page: this.currentPage,
                    sort_by: this.sortBy,
                    sort_dir: this.sortDir
                });
                
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.products = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            sort(col) { if (this.sortBy === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; else { this.sortBy = col; this.sortDir = 'desc'; } this.fetchProducts(); },
            gotoPage(page) { this.currentPage = page; this.fetchProducts(); },
            changePage(url) { if(url) this.fetchProducts(); },
            toggleSelectAll() { this.selectedIds = this.selectAll ? this.products.map(p => p.id) : []; },
            handleFileUpload(e) { const file = e.target.files[0]; if (file) { this.form.image = file; this.imagePreview = URL.createObjectURL(file); } },

            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.products.filter(item => selectedIdsString.includes(String(item.id)));
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
                this.form = { 
                    id: item.id,
                    name: item.name,
                    category_id: item.category_id,
                    price: item.price,
                    image: null,
                    // Load addons ដែលមានស្រាប់មកដាក់ក្នុង form
                    addons: item.addons ? item.addons.map(a => a.id) : [] 
                };
                this.imagePreview = item.image ? '/storage/' + item.image : null;
                
                // Trigger filter manually to show correct addons immediately
                this.filterAddonsByType();
            },

            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                if (mode === 'edit') {
                    this.loadDataToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', category_id: '', price: '', image: null, addons: [] };
                    this.imagePreview = null;
                    this.visibleAddons = [];
                }
            },

            closeModal(force = false) {
                 if (!force && this.isSequenceMode && !confirm("{{ __('messages.confirm_stop_sequence') }}")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchProducts(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                
                let formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('category_id', this.form.category_id);
                formData.append('price', this.form.price);
                
                if(this.form.addons && this.form.addons.length > 0) {
                    this.form.addons.forEach((id, index) => {
                        formData.append(`addons[${index}]`, id);
                    });
                }

                if (this.form.image instanceof File) {
                    formData.append('image', this.form.image);
                }

                let url = "{{ route('admin.products.store') }}";
                if (this.editMode) {
                    url = `/admin/products/${this.form.id}`;
                    formData.append('_method', 'POST'); 
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
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
                        if (this.isSequenceMode) { this.nextInSequence(); } else { this.closeModal(); this.fetchProducts(); }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            async confirmDelete(id) { askConfirm(async () => { await this.performDelete([id]); }); },
            async confirmBulkDelete() { if (this.selectedIds.length === 0) return; askConfirm(async () => { await this.performDelete(this.selectedIds, true); }); },

            async performDelete(ids, isBulk = false) {
                let url = isBulk ? "{{ route('admin.products.bulk_delete') }}" : `/admin/products/${ids[0]}`;
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
                        this.fetchProducts();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message } }));
                    }
                } catch(e) { console.error(e); }
            },

            async toggleStatus(id) {
                try {
                    await fetch(`/admin/products/${id}/toggle`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    this.fetchProducts();
                } catch(e) { console.error(e); }
            }
        }
    }
</script>
@endsection