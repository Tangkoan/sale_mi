<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-4 gap-4">
    <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
        <i class="ri-node-tree"></i>
        គ្រប់គ្រងជម្រើសលម្អិត (Modifiers)
    </h1>
    
    <div class="hidden md:flex gap-2">
        <button @click="openModal('create')" class="btn-primary font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex items-center gap-2">
            <i class="ri-add-circle-line text-xl"></i><span>បន្ថែមជម្រើសថ្មី</span>
        </button>
    </div>
</div>

<div class="flex flex-col md:flex-row gap-3 mb-4 sm:mb-6">
    <div class="flex flex-wrap md:flex-nowrap items-center gap-2 w-full flex-1">
        
        {{-- Filter Group --}}
        <div class="w-[calc(50%-4px)] md:w-48 flex-grow-0">
            <select x-model="filterGroup" @change="fetchModifiers()" class="w-full px-2 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color text-xs sm:text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20 truncate">
                <option value="">គ្រប់ក្រុមជម្រើសទាំងអស់</option>
                <template x-for="g in groups" :key="g.id">
                    <option :value="g.id" x-text="g.name"></option>
                </template>
            </select>
        </div>

        {{-- Search --}}
        <div class="relative flex-1 w-full min-w-[200px]">
            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-secondary"><i class="ri-search-line"></i></span>
            <input type="text" x-model="search" @keyup.debounce.500ms="fetchModifiers()" class="w-full pl-8 pr-3 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color text-xs sm:text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20" placeholder="ស្វែងរកឈ្មោះជម្រើស...">
        </div>
    </div>

    <div class="flex gap-2 md:hidden">
        <button @click="openModal('create')" class="flex-[3] bg-primary text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex justify-center items-center gap-2">
            <i class="ri-add-circle-line text-xl"></i><span>បន្ថែមថ្មី</span>
        </button>
    </div>

    {{-- Selected Items --}}
    <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 w-full md:w-auto justify-between bg-primary/10 border border-primary/20 p-2 rounded-xl">
        <span class="text-xs font-bold text-primary px-2"><span x-text="selectedIds.length"></span> បានជ្រើសរើស</span>
        <div class="flex gap-1">
            <button @click="startSequentialEdit()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition" title="កែប្រែ"><i class="ri-edit-circle-line"></i></button>
            <button @click="confirmBulkDelete()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition" title="លុប"><i class="ri-delete-bin-line"></i></button>
        </div>
    </div>
</div>