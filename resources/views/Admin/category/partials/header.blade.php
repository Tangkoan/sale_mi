<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    
    {{-- 1. TITLE --}}
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
            <i class="ri-folder-open-line"></i>
            {{ __('messages.category_management') }}
        </h1>
    </div>

    {{-- 2. ACTIONS GROUP --}}
    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">

        {{-- A. Selected Actions --}}
        <div x-show="selectedIds.length > 0" x-transition 
             class="flex items-center gap-2 w-full sm:w-auto justify-between bg-primary/10 border border-primary/20 p-2 rounded-xl order-first sm:order-none">
            <span class="text-xs font-bold text-primary px-2" x-text="selectedIds.length + ' {{ __('messages.selected_items') }}'"></span>
            <div class="flex gap-1">
                @can('category-edit')
                <button @click="startSequentialEdit()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition"><i class="ri-edit-circle-line"></i></button>
                @endcan
                @can('category-delete')
                <button @click="confirmBulkDelete()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition"><i class="ri-delete-bin-line"></i></button>
                @endcan
            </div>
        </div>

        {{-- B. TOOLBAR (Columns + Search + Add in ONE ROW) --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            
            {{-- Columns Button --}}
            <div class="relative shrink-0" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" 
                        class="h-[42px] px-3 bg-white dark:bg-gray-800 border border-input-border rounded-xl text-text-color hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium shadow-sm flex items-center justify-center gap-2">
                    <i class="ri-layout-column-line text-lg"></i> 
                    <span class="hidden lg:inline">{{ __('messages.columns') }}</span>
                </button>
                <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.image" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.image') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.destination" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">Destination</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.created_at" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.created_at') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Search Input --}}
            <div class="relative flex-1 md:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-secondary"><i class="ri-search-line"></i></span>
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchCategories()" 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-input-border bg-white dark:bg-gray-800 text-text-color text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" 
                       placeholder="{{ __('messages.search_placeholder') }}">
            </div>

            {{-- Add Button --}}
            <button @can('category-create') @click="openModal('create')" @endcan 
                    class="bg-primary text-white font-bold py-2.5 px-4 md:px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex items-center gap-2 shrink-0 transition-all whitespace-nowrap">
                <i class="ri-add-circle-line text-xl"></i>
                <span class="md:hidden">Add</span> 
                <span class="hidden md:inline">{{ __('messages.add_category') }}</span>
            </button>
        </div>

    </div>
</div>