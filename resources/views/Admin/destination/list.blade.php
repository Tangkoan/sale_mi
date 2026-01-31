@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-1 py-1" x-data="destinationManagement()">
    
    {{-- HEADER (រក្សានៅដដែល) --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-printer-cloud-line"></i>
                Kitchen Destinations
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            {{-- Selected Actions --}}
            <div x-show="selectedIds.length > 0" x-transition 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                 <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1" x-text="selectedIds.length + ' Selected'"></span>
                
                <div class="flex gap-1">
                    <button @click="startSequentialEdit()" class="text-sm font-bold text-blue-600 hover:bg-blue-50 px-3 py-1.5 rounded-md transition" title="Edit Selected">
                        <i class="ri-edit-circle-line"></i>
                    </button>
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition" title="Delete Selected">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>

            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchDestinations()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="Search name or IP...">
            </div>

            {{-- Create Button --}}
            <button @click="openModal('create')" 
                class="w-full sm:w-auto bg-primary text-white font-bold py-2.5 px-6 rounded-xl flex justify-center items-center gap-2 transition-all shadow-lg shadow-primary/30 hover:opacity-90">
                <i class="ri-add-circle-line text-xl"></i>
                <span>Add Destination</span>
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
                        
                        {{-- ✅ កែសម្រួល៖ ដាក់ Sort នៅលើ Name --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('name')">
                            <div class="flex items-center gap-1">
                                Name 
                                {{-- Icon សម្រាប់បង្ហាញថាបច្ចុប្បន្នកំពុង Sort តាមអី --}}
                                <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100" 
                                   :class="{'text-primary opacity-100': sortBy === 'name'}"></i>
                            </div>
                        </th>

                        {{-- ✅ កែសម្រួល៖ ដាក់ Sort នៅលើ Printer IP --}}
                        <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('printer_ip')">
                            <div class="flex items-center gap-1">
                                Printer IP
                                <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"
                                   :class="{'text-primary opacity-100': sortBy === 'printer_ip'}"></i>
                            </div>
                        </th>

                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color">
                    <template x-for="item in destinations" :key="item.id">
                        <tr class="hover:bg-page-bg/30 transition-colors" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                            </td>
                            <td class="px-6 py-4 font-bold text-text-color" x-text="item.name"></td>
                            
                            {{-- IP Address --}}
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-300" 
                                      x-text="item.printer_ip || 'No Printer'"></span>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-600 border border-green-200">Active</span>
                            </td>
                            
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="openModal('edit', item)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <button @click="confirmDelete(item.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="destinations.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-printer-cloud-line text-4xl mb-2 inline-block"></i>
                            <p>No destinations found.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <x-pagination />
    </div>

    {{-- MODAL (រក្សានៅដដែល) --}}
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="relative w-full max-w-md bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="px-6 py-4 border-b border-border-color flex justify-between items-center" :class="isSequenceMode ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-page-bg/30'">
                <div>
                    <h3 class="text-lg font-bold text-text-color" x-text="editMode ? 'Edit Destination' : 'Add Destination'"></h3>
                    <template x-if="isSequenceMode">
                        <p class="text-xs text-primary font-bold mt-1">
                            Editing <span x-text="currentSeqIndex + 1"></span> of <span x-text="sequenceQueue.length"></span>
                        </p>
                    </template>
                </div>
                <button @click="closeModal(true)" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
            </div>
            
            <form @submit.prevent="submitForm" class="p-6 space-y-5">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Destination Name</label>
                    <input type="text" x-model="form.name" placeholder="e.g. Wok, Soup, Bar" class="w-full px-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    <p x-show="errors.name" x-text="errors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                {{-- Printer IP --}}
                <div>
                    <label class="block text-sm font-bold text-text-color mb-1">Printer IP Address (Optional)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                            <i class="ri-printer-line"></i>
                        </span>
                        <input type="text" x-model="form.printer_ip" placeholder="192.168.1.200" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-input-border bg-input-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none font-mono">
                    </div>
                    <p x-show="errors.printer_ip" x-text="errors.printer_ip" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div class="pt-4 flex justify-between items-center border-t border-border-color mt-2">
                    <button type="button" x-show="isSequenceMode" @click="nextInSequence()" class="text-secondary hover:text-text-color text-sm font-bold px-2">
                        Skip <i class="ri-arrow-right-line align-middle"></i>
                    </button>
                    <div x-show="!isSequenceMode"></div> 

                    <div class="flex gap-3">
                        <button type="button" @click="closeModal(true)" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">Cancel</button>
                        <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2 shadow-lg shadow-primary/30" :disabled="isLoading">
                            <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                            <span x-text="isSequenceMode ? (currentSeqIndex + 1 === sequenceQueue.length ? 'Finish' : 'Save & Next') : (editMode ? 'Update' : 'Save')"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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

            // ✅ បន្ថែម៖ Variable សម្រាប់ Sort
            sortBy: 'created_at',
            sortDir: 'desc',

            isSequenceMode: false,
            sequenceQueue: [],
            currentSeqIndex: 0,

            form: { id: null, name: '', printer_ip: '' },
            errors: {},

            init() { this.fetchDestinations(); },

            // ✅ កែសម្រួល៖ បញ្ជូន sort_by និង sort_dir ទៅ API
            async fetchDestinations() {
                let url = "{{ route('admin.destinations.fetch') }}";
                const params = new URLSearchParams({ 
                    keyword: this.search, 
                    per_page: this.perPage, 
                    page: this.currentPage,
                    sort_by: this.sortBy,   // ថ្មី
                    sort_dir: this.sortDir  // ថ្មី
                });
                
                this.isLoading = true;
                try {
                    const response = await fetch(`${url}?${params}`);
                    const data = await response.json();
                    this.destinations = data.data;
                    this.pagination = data; 
                    this.currentPage = data.current_page;
                } catch (error) { console.error(error); } 
                finally { this.isLoading = false; }
            },

            // ✅ បន្ថែម៖ Function Sort
            sort(col) {
                if (this.sortBy === col) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = col;
                    this.sortDir = 'desc';
                }
                this.fetchDestinations();
            },

            gotoPage(page) { this.currentPage = page; this.fetchDestinations(); },
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
                this.form = { ...item, printer_ip: item.printer_ip || '' };
            },

            openModal(mode, item = null) {
                this.isSequenceMode = false;
                this.isModalOpen = true;
                this.errors = {};
                if (mode === 'edit') {
                    this.loadDestinationToForm(item);
                } else {
                    this.editMode = false;
                    this.form = { id: null, name: '', printer_ip: '' };
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