<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    
    {{-- 1. TITLE --}}
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            {{ __('messages.user_management') }}
        </h1>
    </div>

    {{-- 2. ACTIONS GROUP --}}
    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">

        {{-- A. Selected Actions --}}
        <div x-show="selectedIds.length > 0" x-transition 
             class="flex items-center gap-2 w-full sm:w-auto justify-between bg-primary/10 border border-primary/20 p-2 rounded-xl order-first sm:order-none">
            <span class="text-xs font-bold text-primary px-2" x-text="selectedIds.length + ' {{ __('messages.selected_items') }}'"></span>
            <div class="flex gap-1">
                @can('user-edit')
                <button @click="startSequentialEdit()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition"><i class="ri-edit-circle-line"></i></button>
                @endcan
                @can('user-delete')
                <button @click="confirmBulkDelete()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition"><i class="ri-delete-bin-line"></i></button>
                @endcan
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
                            <input type="checkbox" x-model="showCols.role" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.role') }}</span>
                        </label>
                        <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer select-none">
                            <input type="checkbox" x-model="showCols.email" class="rounded text-primary focus:ring-primary border-input-border">
                            <span class="text-sm text-text-color">{{ __('messages.email') }}</span>
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
                <input type="text" x-model="search" @keyup.debounce.500ms="fetchUsers()" 
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-input-border bg-white dark:bg-gray-800 text-text-color text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" 
                       placeholder="{{ __('messages.search_placeholder') }}">
            </div>

            {{-- Add Button --}}
            <button @can('user-create') @click="openModal('create')" @endcan 
                    class="bg-primary text-white font-bold py-2.5 px-4 md:px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex items-center gap-2 shrink-0 transition-all whitespace-nowrap cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-plus"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                <span class="md:hidden">Add</span> 
                <span class="hidden md:inline">{{ __('messages.add_user') }}</span>
            </button>
        </div>

    </div>
</div>