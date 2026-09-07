@extends('admin.dashboard')

@section('title', 'គ្រប់គ្រងជម្រើសលម្អិត (Modifiers)')

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="modifierManagement()">
    
    @include('admin.modifier.partials.header')

    <div class="hidden md:block">
        @include('admin.modifier.partials.table')
    </div>

    <div class="md:hidden">
        @include('admin.modifier.partials.mobile_card')
    </div>
    
    @include('admin.product.partials.pagination') {{-- ប្រើ Pagination របស់ Product ដដែល --}}

    @include('admin.modifier.partials.modal')

</div>

<script>
    function modifierManagement() {
        return {
            modifiers: [],
            groups: @json($groups),
            
            search: '',
            filterGroup: '', // សម្រាប់ Filter តាមក្រុម
            
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            
            isModalOpen: false,
            editMode: false,
            isLoading: false,
            
            selectedIds: [],
            selectAll: false,

            sortBy: 'created_at',
            sortDir: 'desc',

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, modifier_group_id: '', name: '', price: 0 },
            errors: {},

            init() { 
                this.fetchModifiers(); 
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

            async fetchModifiers() {
                let url = "{{ route('admin.modifiers.fetch') }}";
                const params = new URLSearchParams({
                    keyword: this.search,
                    group_id: this.filterGroup,
                    per_page: this.perPage,
                    page: this.currentPage,
                    sort_by: this.sortBy,
                    sort_dir: this.sortDir
                });
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.modifiers = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                    this.selectAll = false; 
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            sort(col) { 
                if (this.sortBy === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; 
                else { this.sortBy = col; this.sortDir = 'desc'; } 
                this.fetchModifiers(); 
            },
            
            gotoPage(page) { 
                if(page === '...') return; 
                this.currentPage = page; 
                this.fetchModifiers(); 
            },
            
            toggleSelectAll() { 
                this.selectedIds = this.selectAll ? this.modifiers.map(m => m.id) : []; 
            },

            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.modifiers.filter(item => selectedIdsString.includes(String(item.id)));
                if (this.sequenceQueue.length === 0) return;
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
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "បានបញ្ចប់ការកែប្រែទាំងអស់" } }));
                }
            },

            loadDataToForm(item) {
                this.editMode = true;
                this.errors = {};
                this.form = { 
                    id: item.id,
                    modifier_group_id: item.modifier_group_id,
                    name: item.name,
                    price: parseFloat(item.price)
                };
            },

            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                if (mode === 'edit') {
                    this.loadDataToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, modifier_group_id: '', name: '', price: 0 };
                }
            },

            closeModal(force = false) {
                if (!force && this.isSequenceMode && !confirm("តើអ្នកពិតជាចង់បោះបង់ការកែប្រែបន្តបន្ទាប់មែនទេ?")) return;
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchModifiers(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                
                let formData = new FormData();
                formData.append('modifier_group_id', this.form.modifier_group_id);
                formData.append('name', this.form.name);
                formData.append('price', this.form.price);
                
                let url = "{{ route('admin.modifiers.store') }}";
                if (this.editMode) {
                    url = `/admin/modifiers/${this.form.id}`;
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
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'សូមពិនិត្យទិន្នន័យឡើងវិញ' } }));
                        } else {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Error' } }));
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        if (this.isSequenceMode) { this.nextInSequence(); } else { this.closeModal(); this.fetchModifiers(); }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            async confirmDelete(id) { 
                if(confirm("តើអ្នកពិតជាចង់លុបទិន្នន័យនេះមែនទេ?")) { await this.performDelete([id]); }
            },
            
            async confirmBulkDelete() { 
                if (this.selectedIds.length === 0) return; 
                if(confirm("តើអ្នកពិតជាចង់លុបទិន្នន័យដែលបានជ្រើសរើសមែនទេ?")) { await this.performDelete(this.selectedIds, true); }
            },

            async performDelete(ids, isBulk = false) {
                let url = isBulk ? "{{ route('admin.modifiers.bulk_delete') }}" : `/admin/modifiers/${ids[0]}`;
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
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        this.fetchModifiers();
                    }
                } catch(e) { console.error(e); }
            },

            async toggleStatus(id) {
                try {
                    await fetch(`/admin/modifiers/${id}/toggle`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    this.fetchModifiers();
                } catch(e) { console.error(e); }
            }
        }
    }
</script>
@endsection