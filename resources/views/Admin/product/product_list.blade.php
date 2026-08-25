@extends('admin.dashboard')

@section('title', __('messages.product_management'))

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="productManagement()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('admin.product.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('admin.product.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('admin.product.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('admin.product.partials.pagination')

    {{-- 5. MODAL (CREATE / EDIT) --}}
    @include('admin.product.partials.modal')

</div>

<script>
    // ✅ Component: Category Dropdown 
        function categoryDropdown() {
            return {
                isOpen: false,
                searchQuery: '',
                categoriesList: [],
                page: 1,
                hasMorePages: false,
                isLoading: false,
                isSelecting: false, // ✅ Flag សម្រាប់ការពារកុំឲ្យលោត Search ពេលយើងគ្រាន់តែចុចរើសឈ្មោះ

                init() {
                    this.$watch('form.category_id', (value) => {
                        if (value) {
                            // ពេល Edit: ស្វែងរកក្នុងបញ្ជីបច្ចុប្បន្ន
                            let selected = this.categoriesList.find(c => c.id == value);
                            
                            if (selected) {
                                this.setSearchText(selected.name);
                            } else {
                                // បើ Category នោះនៅទំព័រទី2+ (រកមិនឃើញក្នុងបញ្ជី 10 ដំបូង) -> ទាញពីទិន្នន័យ Product ដែលមានស្រាប់
                                // @ts-ignore - ហៅទិន្នន័យពី Main logic (productManagement)
                                const product = this.products?.find(p => p.category_id == value);
                                if (product && product.category) {
                                    this.categoriesList.push(product.category); // បញ្ចូលវាទៅក្នុងបញ្ជីបណ្ដោះអាសន្ន
                                    this.setSearchText(product.category.name);
                                }
                            }
                        } else {
                            this.setSearchText('');
                        }
                    });
                    this.fetchCategories(1);
                },

                // Function សម្រាប់បញ្ចៀសការហៅ API ដោយអចេតនា
                setSearchText(text) {
                    this.isSelecting = true;
                    this.searchQuery = text;
                    setTimeout(() => { this.isSelecting = false; }, 300);
                },

                openDropdown() {
                    this.isOpen = true;
                    if (this.categoriesList.length === 0) this.fetchCategories(1);
                },

                closeDropdown() {
                    this.isOpen = false;
                    // បើ User វាយអក្សរលេង តែមិនបានចុចរើស ឲ្យវាលោតមកឈ្មោះ Category ដើមវិញពេលបិទ
                    if (this.form.category_id) {
                        const selected = this.categoriesList.find(c => c.id == this.form.category_id);
                        if (selected && this.searchQuery !== selected.name) {
                            this.setSearchText(selected.name);
                        }
                    } else {
                        this.setSearchText('');
                    }
                },

                // ✅ Function ថ្មីសម្រាប់ Live Search តែម្ដង
                handleSearch() {
                    if (this.isSelecting) return; // បើជាការ Update UI ធម្មតា មិនបាច់ Search ទេ
                    this.fetchCategories(1);
                },

                async fetchCategories(page = 1) {
                    this.isLoading = true;
                    this.page = page;
                    
                    // ✅ ចាប់យកទិន្នន័យម្ដង 10 (Pagination)
                    let url = `{{ route('admin.categories.fetch') }}?page=${page}&per_page=10`;
                    
                    if (this.searchQuery && !this.isSelecting) {
                        url += `&keyword=${encodeURIComponent(this.searchQuery)}`;
                    }

                    try {
                        const response = await fetch(url);
                        const data = await response.json();
                        
                        if (page === 1) {
                            this.categoriesList = data.data; // បើទំព័រទី 1 ជំនួសទិន្នន័យចាស់
                        } else {
                            this.categoriesList = [...this.categoriesList, ...data.data]; // ✅ បើទំព័រទី 2+ គឺបន្ថែមទិន្នន័យបន្ត (Load More)
                        }
                        
                        // កំណត់ថាត្រូវមានប៊ូតុង Load More ទៀតឬអត់
                        this.hasMorePages = data.next_page_url !== null; 
                    } catch (error) { 
                        console.error("Error fetching categories:", error); 
                    } finally { 
                        this.isLoading = false; 
                    }
                },

                loadMore() { 
                    if (!this.isLoading && this.hasMorePages) {
                        this.fetchCategories(this.page + 1); 
                    }
                },

                selectCategory(category) {
                    this.form.category_id = category.id;
                    this.setSearchText(category.name);
                    this.isOpen = false;
                }
            }
        }

    
    // ✅ Main Logic
    // ✅ Main Logic
    function productManagement() {
        return {
            products: [],
            categories: @json($categories), 
            allAddons: @json($addons),      
            visibleAddons: [],              
            
            search: '',
            filterCategory: '',
            filterDestination: '',
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
                this.$watch('form.category_id', (value) => { this.filterAddonsByType(value); });
                this.fetchProducts(); 
            },

            get visiblePages() {
                const total = this.pagination.last_page;
                const current = this.currentPage;
                const delta = 2;
                let pages = [];
                if (total <= 7) { for (let i = 1; i <= total; i++) pages.push(i); return pages; }
                pages.push(1);
                if (current > delta + 2) pages.push('...');
                let start = Math.max(2, current - delta);
                let end = Math.min(total - 1, current + delta);
                for (let i = start; i <= end; i++) pages.push(i);
                if (current < total - delta - 1) pages.push('...');
                if (total > 1) pages.push(total);
                return pages;
            },

            async filterAddonsByType(categoryId) {
                if (!categoryId) { this.visibleAddons = []; return; }
                let selectedCat = this.categories.find(c => c.id == categoryId);
                // Fallback: search in loaded products if category not in initial list
                if (!selectedCat && this.products.length > 0) {
                     const product = this.products.find(p => p.category_id == categoryId);
                     if (product && product.category) selectedCat = product.category;
                }
                if (selectedCat && selectedCat.kitchen_destination_id) {
                    this.visibleAddons = this.allAddons.filter(a => a.kitchen_destination_id == selectedCat.kitchen_destination_id);
                } else {
                    this.visibleAddons = [];
                }
                this.selectAllAddons = false;
            },

            toggleSelectAllAddons() {
                if (this.selectAllAddons) {
                    this.visibleAddons.forEach(addon => {
                        if (!this.form.addons.includes(addon.id)) this.form.addons.push(addon.id);
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
                    destination_id: this.filterDestination,
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
                    this.selectAll = false; 
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            sort(col) { if (this.sortBy === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; else { this.sortBy = col; this.sortDir = 'desc'; } this.fetchProducts(); },
            gotoPage(page) { if(page === '...') return; this.currentPage = page; this.fetchProducts(); },
            toggleSelectAll() { this.selectedIds = this.selectAll ? this.products.map(p => p.id) : []; },
            handleFileUpload(e) { const file = e.target.files[0]; if (file) { this.form.image = file; this.imagePreview = URL.createObjectURL(file); } },

            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.products.filter(item => selectedIdsString.includes(String(item.id)));
                if (this.sequenceQueue.length === 0) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.select_items_first') }}" } })); 
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
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.all_items_updated') }}" } }));
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
                    addons: item.addons ? item.addons.map(a => a.id) : [] 
                };
                this.imagePreview = item.image ? '/storage/' + item.image : null;
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
                    this.form.addons.forEach((id, index) => { formData.append(`addons[${index}]`, id); });
                }
                if (this.form.image instanceof File) formData.append('image', this.form.image);
                
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

            // 🌟 [បន្ថែមថ្មី] Function សម្រាប់ Duplicate 🌟
            async duplicateProduct(id) {
                if(confirm("តើអ្នកពិតជាចង់ Duplicate ផលិតផលនេះមែនទេ?")) {
                    this.isLoading = true;
                    try {
                        const response = await fetch(`/admin/products/${id}/duplicate`, {
                            method: 'POST',
                            headers: { 
                                'Accept': 'application/json', // ប្រាប់ Laravel ឲ្យបញ្ជូនមកជា JSON កុំឲ្យបញ្ជូនជា HTML
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                            }
                        });
                        
                        // ឆែកមើលថាតើ Server ឆ្លើយតបមកជា HTML ឬអត់ (ការពារអត់ឲ្យ Error)
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            const data = await response.json();
                            
                            if(response.ok) {
                                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                                this.fetchProducts();
                            } else {
                                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'មានបញ្ហាក្នុងការ Duplicate!' } }));
                            }
                        } else {
                            // បើវាមិនមែនជា JSON វាអាចជា 404 ឬ 500 HTML 
                            console.error("Server មិនបានបញ្ជូនទិន្នន័យជា JSON មកទេ។ Status: " + response.status);
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'បញ្ហា Route ឬ Backend Error! (ឆែកមើលក្នុង Console/F12)' } }));
                        }
                    } catch(e) { 
                        console.error("កំហុស JavaScript:", e); 
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'មានបញ្ហាក្នុងការភ្ជាប់ទៅកាន់ Server!' } }));
                    } finally {
                        this.isLoading = false;
                    }
                }
            },

            async confirmDelete(id) { 
                if(typeof askConfirm !== 'undefined') { askConfirm(async () => { await this.performDelete([id]); }); }
                else if(confirm("{{ __('messages.confirm_delete') }}")) { await this.performDelete([id]); }
            },
            
            async confirmBulkDelete() { 
                if (this.selectedIds.length === 0) return; 
                if(typeof askConfirm !== 'undefined') { askConfirm(async () => { await this.performDelete(this.selectedIds, true); }); }
                else if(confirm("{{ __('messages.confirm_bulk_delete') }}")) { await this.performDelete(this.selectedIds, true); }
            },

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
                        
                        if (data.status === 'warning') {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', message: data.message } }));
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Error deleting item!' } }));
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