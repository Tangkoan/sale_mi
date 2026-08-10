<div class="flex flex-col gap-4 mb-4 sm:mb-6">
    
    {{-- 1. TITLE ROW --}}
    <div class="flex justify-between items-center">
        <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
            <i class="ri-printer-cloud-line"></i>
            {{ __('messages.kitchen_destinations') }}
        </h1>
        
        {{-- Selected Items Badge --}}
        <div x-show="selectedIds.length > 0" x-transition class="hidden md:flex items-center gap-2 bg-primary/10 border border-primary/20 p-1.5 rounded-lg">
            <span class="text-xs font-bold text-primary px-2">
                <span x-text="selectedIds.length"></span> {{ __('messages.selected') }}
            </span>
            <div class="flex gap-1">
                <button @click="startSequentialEdit()" class="h-7 w-7 flex items-center justify-center rounded-md bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="{{ __('messages.edit') }}">
                    <i class="ri-edit-circle-line"></i>
                </button>
                <button @click="confirmBulkDelete()" class="h-7 w-7 flex items-center justify-center rounded-md bg-red-100 text-red-600 hover:bg-red-200 transition" title="{{ __('messages.delete') }}">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- 2. TOOLBAR ROW --}}
    <div class="flex flex-col md:flex-row gap-3">
        
        {{-- Selected Actions (Mobile Only) --}}
        <div x-show="selectedIds.length > 0" x-transition class="md:hidden flex items-center gap-2 w-full justify-between bg-primary/10 border border-primary/20 p-2 rounded-xl">
             <span class="text-xs font-bold text-primary px-2">
                <span x-text="selectedIds.length"></span> {{ __('messages.selected') }}
            </span>
            <div class="flex gap-1">
                <button @click="startSequentialEdit()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition"><i class="ri-edit-circle-line"></i></button>
                <button @click="confirmBulkDelete()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition"><i class="ri-delete-bin-line"></i></button>
            </div>
        </div>

        {{-- ✅ Search & Add Button Container --}}
        <div class="flex items-center gap-2 w-full">
            
            {{-- Search Input --}}
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary"><i class="ri-search-line"></i></span>
                <input type="text" 
                       x-model="search" 
                       @keyup.debounce.500ms="fetchDestinations()" 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" 
                       placeholder="{{ __('messages.search_placeholder') }}">
            </div>

            {{-- ✅ Add Button (With Permission Check) --}}
            @can('destinations-add')
            <button @click="openModal('create')" class="bg-primary text-white font-bold py-2.5 px-4 md:px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex items-center gap-2 shrink-0 transition-all">
                <i class="ri-add-circle-line text-xl"></i>
                {{-- Show "Add" on Mobile, "Add Destination" on Desktop --}}
                <span class="md:hidden">{{ __('messages.add') }}</span> 
                <span class="hidden md:inline">{{ __('messages.add_destination') }}</span>
            </button>
            @endcan
            
        </div>
    </div>
</div>