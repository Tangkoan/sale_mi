@extends('admin.dashboard') {{-- ឬ Layout របស់អ្នក --}}

@section('content')

{{-- x-data ដាក់នៅទីនេះដើម្បីអោយ Component ខាងក្នុងអាចប្រើ variables រួមគ្នាបាន --}}
<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="categoryManagement()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('Admin.category.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('Admin.category.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('Admin.category.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('Admin.category.partials.pagination')

    {{-- 5. MODAL (CREATE / EDIT) --}}
    @include('Admin.category.partials.modal')

</div>

<script>
    function categoryManagement() {
        return {
            categories: [],
            destinations: @json($destinations ?? []), 
            
            search: '',
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            selectedIds: [],
            selectAll: false,

            // Column Config
            showCols: JSON.parse(localStorage.getItem('category_table_cols')) || { 
                image: true, 
                destination: true, 
                created_at: true 
            },

            sortBy: 'created_at',
            sortDir: 'desc',

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', kitchen_destination_id: '', image: null },
            imagePreview: null,
            errors: {},

            init() { 
                this.$watch('showCols', (value) => { localStorage.setItem('category_table_cols', JSON.stringify(value)); });
                this.fetchCategories(); 
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

            async fetchCategories() {
                let url = "{{ route('admin.categories.fetch') }}";
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
                    this.categories = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                    this.selectAll = false;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            sort(col) {
                if (this.sortBy === col) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = col;
                    this.sortDir = 'desc';
                }
                this.fetchCategories();
            },

            gotoPage(page) { if(page === '...') return; this.currentPage = page; this.fetchCategories(); },
            
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

            // ================= SEQUENTIAL EDIT =================
            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.categories.filter(item => selectedIdsString.includes(String(item.id)));
                
                if (this.sequenceQueue.length === 0) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.select_items_first') }}" } })); 
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
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.all_items_updated') }}" } }));
                }
            },

            loadCategoryToForm(item) {
                this.editMode = true;
                this.errors = {};
                this.form = { 
                    ...item, 
                    image: null, 
                    kitchen_destination_id: item.kitchen_destination_id || '' 
                };
                this.imagePreview = item.image ? '/storage/' + item.image : null;
            },

            // ================= MODAL & FORM =================
            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                this.imagePreview = null;
                
                if (mode === 'edit') {
                    this.loadCategoryToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', kitchen_destination_id: '', image: null };
                }
            },

            closeModal(force = false) {
                 if (!force && this.isSequenceMode && !confirm("{{ __('messages.confirm_stop_sequence') }}")) return;
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
            
            // ================= DELETE =================
            async confirmDelete(id) {
                if(typeof askConfirm !== 'undefined') {
                    askConfirm(async () => { await this.performDelete([id]); });
                } else if(confirm("{{ __('messages.confirm_delete') }}")) {
                    await this.performDelete([id]);
                }
            },

            async confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                if(typeof askConfirm !== 'undefined') {
                    askConfirm(async () => { await this.performDelete(this.selectedIds, true); });
                } else if(confirm("{{ __('messages.confirm_bulk_delete') }}")) {
                    await this.performDelete(this.selectedIds, true);
                }
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