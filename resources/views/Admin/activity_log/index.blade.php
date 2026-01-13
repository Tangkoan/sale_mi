@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-1 py-1" x-data="activityLogs()">
    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                {{-- <i class="ri-history-line text-primary"></i> --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-ban-icon lucide-shield-ban"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m4.243 5.21 14.39 12.472"/></svg>
                Activity Logs
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            <div x-show="selectedIds.length > 0" x-transition.opacity.duration.300ms 
                 class="flex items-center gap-2 mr-2 w-full sm:w-auto justify-between sm:justify-start bg-white dark:bg-gray-800 p-1 rounded-lg border border-border-color shadow-sm">
                <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1.5 rounded ml-1" x-text="selectedIds.length + ' selected'"></span>
                
                <div class="flex gap-1">
                    @can('activity-delete')
                    <button @click="confirmBulkDelete()" class="text-sm font-bold text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-md transition" title="Delete Selected">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    @endcan
                </div>
            </div>

            <div class="relative w-full sm:w-auto" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" 
                        class="w-full sm:w-auto flex justify-center items-center gap-2 px-3 py-2.5 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm">
                    <i class="ri-layout-column-line"></i> <span class="sm:hidden lg:inline">Columns</span>
                </button>
                <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.causer" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">User</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.subject" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Subject</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.changes" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Changes</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.date" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Date</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchLogs()"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder-secondary text-sm shadow-sm"
                       placeholder="Search logs...">
            </div>
        </div>
    </div>

    <div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 w-4">
                            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.causer">User (Actor)</th>
                        <th class="px-6 py-4 font-bold">Action</th>
                        <th class="px-6 py-4 font-bold" x-show="showCols.subject">Subject</th>
                        <th class="px-6 py-4 font-bold w-1/3" x-show="showCols.changes">Changes</th>
                        <th class="px-6 py-4 font-bold text-right" x-show="showCols.date">Date</th>
                        @can('activity-delete')
                        <th class="px-6 py-4 font-bold text-right w-20">Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color text-sm text-text-color">
                    <template x-if="isLoading">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-secondary">
                                <i class="ri-loader-4-line text-3xl animate-spin inline-block mb-2"></i>
                                <p>Loading activity logs...</p>
                            </td>
                        </tr>
                    </template>

                    <template x-for="log in logs" :key="log.id">
                        <tr class="hover:bg-page-bg/30 transition-colors align-top group" :class="{'bg-primary/5': selectedIds.includes(log.id)}">
                            
                            <td class="px-6 py-4">
                                <input type="checkbox" :value="log.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap" x-show="showCols.causer">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs border border-primary/20" 
                                         x-text="log.causer_initial">
                                    </div>
                                    <div>
                                        <div class="font-bold text-text-color" x-text="log.causer_name"></div>
                                        <div class="text-xs text-secondary" x-text="log.causer_email"></div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wide border border-opacity-20" 
                                      :class="log.badge_class" x-text="log.description"></span>
                            </td>

                            <td class="px-6 py-4" x-show="showCols.subject">
                                <div class="flex flex-col">
                                    <span class="font-bold text-primary" x-text="log.subject_type"></span>
                                    <span class="text-xs text-secondary font-mono bg-page-bg px-1.5 py-0.5 rounded w-fit mt-1 border border-border-color" x-text="'ID: ' + log.subject_id"></span>
                                </div>
                            </td>

                            <td class="px-6 py-4" x-show="showCols.changes">
                                <div class="text-xs text-text-color bg-page-bg/50 rounded-lg p-3 border border-border-color shadow-sm max-w-md overflow-x-auto custom-scrollbar" 
                                     x-html="log.changes_html">
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right" x-show="showCols.date">
                                <div class="text-sm font-medium text-text-color" x-text="log.created_at_date"></div>
                                <div class="text-xs text-secondary mt-0.5 flex items-center justify-end gap-1">
                                    <i class="ri-time-line"></i>
                                    <span x-text="log.created_at_ago"></span>
                                </div>
                            </td>

                            @can('activity-delete')
                            <td class="px-6 py-4 text-right">
                                <button @click="confirmDelete(log.id)" 
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors ml-auto
                                               bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 border border-transparent hover:border-red-200" 
                                        title="Delete Log">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </td>
                            @endcan
                        </tr>
                    </template>

                    <tr x-show="!isLoading && logs.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-ghost-line text-4xl mb-2 inline-block"></i>
                            <p>No activity logs found.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <x-pagination x-model="perPage" @change="fetchLogs()" />
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

<script>
    function activityLogs() {
        return {
            logs: [],
            search: '',
            isLoading: false,
            pagination: {},
            perPage: '10',
            selectedIds: [],
            selectAll: false,
            
            // Column Visibility State
            showCols: JSON.parse(localStorage.getItem('log_table_cols')) || { 
                causer: true, 
                subject: true, 
                changes: true, 
                date: true 
            },

            init() {
                // Save column state when changed
                this.$watch('showCols', (value) => {
                    localStorage.setItem('log_table_cols', JSON.stringify(value));
                });
                this.fetchLogs();
            },

            async fetchLogs(url = "{{ route('admin.activity_logs.fetch') }}") {
                this.isLoading = true;
                const params = new URLSearchParams();
                if(this.search) params.append('keyword', this.search);
                params.append('per_page', this.perPage);
                
                url = url.split('?')[0] + '?' + params.toString();

                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    this.logs = data.data;
                    this.pagination = { 
                        total: data.total, from: data.from, to: data.to, 
                        prev_page_url: data.prev_page_url, next_page_url: data.next_page_url 
                    };
                    this.selectedIds = [];
                    this.selectAll = false;
                } catch (e) { console.error(e); } 
                finally { this.isLoading = false; }
            },

            changePage(url) { if(url) this.fetchLogs(url); },

            toggleSelectAll() {
                this.selectedIds = this.selectAll ? this.logs.map(log => log.id) : [];
            },

            async confirmDelete(id) {
                askConfirm(async () => {
                    await this.performDelete([id]);
                });
            },

            async confirmBulkDelete() {
                if (this.selectedIds.length === 0) return;
                askConfirm(async () => {
                    await this.performDelete(this.selectedIds, true);
                });
            },

            async performDelete(ids, isBulk = false) {
                let url = isBulk 
                ? "{{ route('admin.activity_logs.bulk_delete') }}" 
                : "{{ route('admin.activity_logs.destroy', ':id') }}".replace(':id', ids[0]);
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
                        this.fetchLogs();
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: data.message } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Failed to delete.' } }));
                    }
                } catch(e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Network Error' } }));
                }
            }
        }
    }
</script>
@endsection