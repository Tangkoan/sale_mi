<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    
    {{-- 1. TITLE --}}
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-key-round-icon lucide-key-round"><path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"/><circle cx="16.5" cy="7.5" r=".5" fill="currentColor"/></svg>
            {{ __('messages.permission_management') }}
        </h1>
    </div>

    {{-- 2. ACTIONS GROUP --}}
    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">

        {{-- A. Selected Actions --}}
        <div x-show="selectedIds.length > 0" x-transition 
             class="flex items-center gap-2 w-full sm:w-auto justify-between bg-primary/10 border border-primary/20 p-2 rounded-xl order-first sm:order-none">
            <span class="text-xs font-bold text-primary px-2" x-text="selectedIds.length + ' {{ __('messages.selected') }}'"></span>
            <div class="flex gap-1">
                @role('Super Admin')
                <button @click="startSequentialEdit()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition"><i class="ri-edit-circle-line"></i></button>
                <button @click="confirmBulkDelete()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition"><i class="ri-delete-bin-line"></i></button>
                @endrole
            </div>
        </div>

        {{-- B. TOOLBAR (Columns + Search + Add) --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            
            {{-- Columns Button --}}
            <div class="relative shrink-0" x-data="{ openCol: false }">
                <button @click="openCol = !openCol" @click.outside="openCol = false" 
                        class="h-[42px] px-3 bg-white dark:bg-gray-800 border border-input-border rounded-xl text-text-color hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium shadow-sm flex items-center justify-center gap-2">
                    <i class="ri-layout-column-line text-lg"></i> 
                    <span class="hidden lg:inline">{{ __('messages.columns') }}</span>
                </button>
                
                {{-- Dropdown --}}
                <div x-show="openCol" class="absolute left-0 md:left-auto md:right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.guard_name" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.guard_name') }}</span>
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
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchPermissions()" 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-input-border bg-white dark:bg-gray-800 text-text-color text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" 
                       placeholder="{{ __('messages.search_placeholder_permission') }}">
            </div>

            {{-- Add Button --}}
            @role('Super Admin')
            <button @click="openModal('create')" 
                    class="bg-primary text-white font-bold py-2.5 px-4 md:px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex items-center gap-2 shrink-0 transition-all whitespace-nowrap cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-plus"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
                <span class="md:hidden">Add</span> 
                <span class="hidden md:inline">{{ __('messages.add_permission') }}</span>
            </button>
            @endrole
        </div>

    </div>
</div>