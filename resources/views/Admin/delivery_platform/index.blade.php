@extends('admin.dashboard')

@section('title', 'Delivery Platforms')

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="platformManagement()">
    
    @include('admin.delivery_platform.partials.header')

    <div class="hidden md:block">
        @include('admin.delivery_platform.partials.table')
    </div>

    <div class="md:hidden">
        @include('admin.delivery_platform.partials.mobile_card')
    </div>
    
    @include('admin.delivery_platform.partials.pagination')

    @include('admin.delivery_platform.partials.modal')

</div>

<script>
    function platformManagement() {
        return {
            platforms: [],
            search: '',
            perPage: '10',
            currentPage: 1, 
            pagination: { last_page: 1, total: 0 }, 
            isModalOpen: false,
            editMode: false,
            isLoading: false,

            sortBy: 'created_at',
            sortDir: 'desc',

            form: { id: null, name: '', logo: null },
            imagePreview: null,
            errors: {},

            init() { 
                this.fetchPlatforms(); 
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

            async fetchPlatforms() {
                let url = "{{ route('admin.delivery_platforms.fetch') }}";
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
                    this.platforms = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            sort(col) { 
                if (this.sortBy === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; 
                else { this.sortBy = col; this.sortDir = 'desc'; } 
                this.fetchPlatforms(); 
            },
            
            gotoPage(page) { 
                if(page === '...') return; 
                this.currentPage = page; 
                this.fetchPlatforms(); 
            },

            handleFileUpload(e) { 
                const file = e.target.files[0]; 
                if (file) { 
                    this.form.logo = file; 
                    this.imagePreview = URL.createObjectURL(file); 
                } 
            },

            openModal(mode, item = null) {
                this.isModalOpen = true;
                this.errors = {};
                if (mode === 'edit') {
                    this.editMode = true;
                    this.form = { id: item.id, name: item.name, logo: null };
                    this.imagePreview = item.logo ? '/storage/' + item.logo : null;
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', logo: null };
                    this.imagePreview = null;
                }
            },

            closeModal() {
                this.isModalOpen = false;
                this.fetchPlatforms(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                let formData = new FormData();
                formData.append('name', this.form.name);
                if (this.form.logo instanceof File) formData.append('logo', this.form.logo);
                
                let url = "{{ route('admin.delivery_platforms.store') }}";
                if (this.editMode) {
                    url = `/admin/delivery-platforms/${this.form.id}`;
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
                        this.closeModal(); 
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            async confirmDelete(id) { 
                if(confirm("តើអ្នកពិតជាចង់លុប Platform នេះមែនទេ?")) {
                    try {
                        const response = await fetch(`/admin/delivery-platforms/${id}`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                        });
                        const data = await response.json();
                        if(response.ok) {
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                            this.fetchPlatforms();
                        }
                    } catch(e) { console.error(e); }
                }
            },

            async toggleStatus(id) {
                try {
                    await fetch(`/admin/delivery-platforms/${id}/toggle`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    this.fetchPlatforms();
                } catch(e) { console.error(e); }
            }
        }
    }
</script>
@endsection