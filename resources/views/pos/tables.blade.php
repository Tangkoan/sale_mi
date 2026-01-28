@extends('admin.dashboard') {{-- យើងអាចប្រើ Layout ដើមសិន ឬបង្កើតថ្មីក៏បាន --}}

@section('content')
<div class="h-[calc(100vh-80px)] flex flex-col" x-data="posTables()">
    
    {{-- Header --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-text-color">{{ __('messages.select_table') }}</h1>
            <p class="text-sm text-secondary">{{ __('messages.please_select_table_to_order') }}</p>
        </div>
        
        {{-- Status Legend (ពន្យល់ពណ៌) --}}
        <div class="flex gap-3 text-xs font-bold">
            <div class="flex items-center gap-1 bg-white dark:bg-gray-800 px-3 py-1 rounded-full shadow-sm border border-gray-200 dark:border-gray-700">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-text-color">{{ __('messages.available') }}</span>
            </div>
            <div class="flex items-center gap-1 bg-white dark:bg-gray-800 px-3 py-1 rounded-full shadow-sm border border-gray-200 dark:border-gray-700">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-text-color">{{ __('messages.busy') }}</span>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div x-show="isLoading && tables.length === 0" class="flex-1 flex items-center justify-center">
        <i class="ri-loader-4-line text-4xl animate-spin text-primary"></i>
    </div>

    {{-- Tables Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 overflow-y-auto pb-20 custom-scrollbar" x-show="tables.length > 0" x-cloak>
        <template x-for="table in tables" :key="table.id">
            <a :href="'/pos/select-table/' + table.id" 
               class="relative group aspect-square rounded-2xl flex flex-col items-center justify-center transition-all duration-200 border-2 shadow-sm hover:shadow-md active:scale-95"
               :class="table.status === 'available' 
                    ? 'bg-white dark:bg-gray-800 border-green-500/30 hover:border-green-500' 
                    : 'bg-red-50 dark:bg-red-900/10 border-red-500/30 hover:border-red-500'">
                
                {{-- Icon --}}
                <div class="mb-2 p-3 rounded-full transition-colors"
                     :class="table.status === 'available' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                     <i class="ri-restaurant-2-line text-2xl"></i>
                </div>

                {{-- Table Name --}}
                <h3 class="text-lg font-bold text-text-color" x-text="table.name"></h3>
                
                {{-- Status Text --}}
                <span class="text-xs font-medium mt-1 uppercase tracking-wide"
                      :class="table.status === 'available' ? 'text-green-600' : 'text-red-600'"
                      x-text="table.status === 'available' ? '{{ __('messages.available') }}' : '{{ __('messages.busy') }}'">
                </span>

                {{-- Busy Indicator (Optional: Timer or User) --}}
                <template x-if="table.status === 'busy'">
                    <div class="absolute top-2 right-2 w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                </template>
            </a>
        </template>
    </div>

</div>

<script>
    function posTables() {
        return {
            tables: [],
            isLoading: false,
            interval: null,

            init() {
                this.fetchTables();
                // Auto Refresh រៀងរាល់ 5 វិនាទី ដើម្បីអោយឃើញ Status តុ Update ភ្លាមៗ
                this.interval = setInterval(() => {
                    this.fetchTables(true); // true = silent refresh (មិនបង្ហាញ Loading)
                }, 5000);
            },

            async fetchTables(silent = false) {
                if (!silent) this.isLoading = true;
                try {
                    const response = await fetch("{{ route('pos.tables.fetch') }}");
                    const data = await response.json();
                    this.tables = data;
                } catch (error) {
                    console.error('Error fetching tables:', error);
                } finally {
                    if (!silent) this.isLoading = false;
                }
            }
        }
    }
</script>
@endsection