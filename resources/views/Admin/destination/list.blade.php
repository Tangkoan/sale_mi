@extends('admin.dashboard')

@section('title', 'Kitchen Management')

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="destinationManagement()">
    
    {{-- 1. HEADER & ACTIONS --}}
    @include('Admin.destination.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('Admin.destination.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('Admin.destination.partials.mobile_card')
    </div>
    
    {{-- 4. PAGINATION --}}
    @include('Admin.destination.partials.pagination')

    {{-- 5. MODAL (CREATE / EDIT) --}}
    @include('Admin.destination.partials.modal')

</div>

<script>
    function destinationManagement() {
        return {
            destinations: [],
            search: '',
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

            form: { id: null, name: '', printnode_id: '' },
            errors: {},

            init() { this.fetchDestinations(); },

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

            async fetchDestinations() {
                let url = "{{ route('admin.destinations.fetch') }}";
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
                    this.destinations = data.data;
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
                this.fetchDestinations();
            },

            gotoPage(page) { if(page === '...') return; this.currentPage = page; this.fetchDestinations(); },
            toggleSelectAll() { this.selectedIds = this.selectAll ? this.destinations.map(c => c.id) : []; },

            startSequentialEdit() {
                const selectedIdsString = this.selectedIds.map(id => String(id));
                this.sequenceQueue = this.destinations.filter(item => selectedIdsString.includes(String(item.id)));
                
                if (this.sequenceQueue.length === 0) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "Please select items first" } })); 
                    return;
                }

                this.isSequenceMode = true;
                this.currentSeqIndex = 0;
                this.loadDestinationToForm(this.sequenceQueue[0]);
                this.isModalOpen = true;
            },

            nextInSequence() {
                this.currentSeqIndex++;
                if (this.currentSeqIndex < this.sequenceQueue.length) {
                    this.loadDestinationToForm(this.sequenceQueue[this.currentSeqIndex]);
                } else {
                    this.closeModal(true); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "All items updated!" } }));
                }
            },

            loadDestinationToForm(item) {
                this.editMode = true;
                this.errors = {};
                this.form = { ...item, printnode_id: item.printnode_id || '' };
            },

            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                if (mode === 'edit') {
                    this.loadDestinationToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', printnode_id: '' };
                }
            },

            closeModal(force = false) {
                if (!force && this.isSequenceMode && !confirm("Stop bulk editing?")) return;
                
                this.isModalOpen = false;
                this.isSequenceMode = false;
                this.selectedIds = [];
                this.selectAll = false;
                this.fetchDestinations(); 
            },

            async submitForm() {
                this.isLoading = true;
                this.errors = {};
                
                let url = "{{ route('admin.destinations.store') }}";
                if (this.editMode) url = `/admin/destinations/${this.form.id}`;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify(this.form)
                    });
                    
                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422) this.errors = data.errors;
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || 'Error' } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                        
                        if (this.isSequenceMode) {
                            this.nextInSequence();
                        } else {
                            this.closeModal();
                            this.fetchDestinations();
                        }
                    }
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },
            
            async confirmDelete(id) {
                if(!confirm("Are you sure?")) return;
                try {
                    await fetch(`/admin/destinations/${id}/delete`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    this.fetchDestinations();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Deleted Successfully' } }));
                } catch(e) { console.error(e); }
            },

            async confirmBulkDelete() {
                if (this.selectedIds.length === 0 || !confirm("Delete selected items?")) return;
                try {
                    await fetch("{{ route('admin.destinations.bulk_delete') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify({ ids: this.selectedIds })
                    });
                    this.closeModal();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Bulk Deleted Successfully' } }));
                } catch(e) { console.error(e); }
            }
        }
    }
</script>
@endsection