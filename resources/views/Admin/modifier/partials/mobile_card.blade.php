<div class="flex flex-col gap-4">
    <div class="flex items-center justify-between px-2 bg-card-bg py-2 rounded-xl border border-border-color shadow-sm" x-show="modifiers.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>ជ្រើសរើសទាំងអស់</span>
        </label>
        <span class="text-xs font-bold text-primary px-3 py-1 bg-primary/10 rounded-full"><span x-text="modifiers.length"></span> ធាតុ</span>
    </div>

    <template x-for="item in modifiers" :key="'mobile-' + item.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative transition-all duration-200 flex flex-col gap-3"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(item.id)}">
            
            <div class="flex items-center justify-between border-b border-dashed border-border-color pb-2">
                <div class="flex items-center gap-3">
                    <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white">
                    <div class="flex items-center gap-1.5">
                        <button @click="toggleStatus(item.id)" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none" :class="item.is_active ? 'bg-green-500' : 'bg-gray-300'">
                            <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform shadow-sm" :class="item.is_active ? 'translate-x-4' : 'translate-x-0.5'"></span>
                        </button>
                        <span class="text-[10px] font-bold uppercase tracking-wider" :class="item.is_active ? 'text-green-600' : 'text-gray-400'" x-text="item.is_active ? 'Active' : 'Inactive'"></span>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button @click="openModal('edit', item)" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100"><i class="ri-pencil-fill"></i></button>
                    <button @click="confirmDelete(item.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100"><i class="ri-delete-bin-line"></i></button>
                </div>
            </div>

            <div class="flex flex-col py-1">
                <h3 class="font-extrabold text-text-color text-base truncate mb-1" x-text="item.name"></h3>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs bg-page-bg text-secondary border border-border-color px-2 py-1 rounded" x-text="item.group ? item.group.name : ''"></span>
                    
                    <span x-show="parseFloat(item.price) > 0" class="text-sm font-black text-primary bg-primary/5 px-2 py-0.5 rounded-lg border border-primary/20" x-text="'+ ' + parseFloat(item.price).toLocaleString() + ' ៛'"></span>
                    <span x-show="parseFloat(item.price) <= 0" class="text-xs text-gray-400">ឥតគិតថ្លៃ</span>
                </div>
            </div>
        </div>
    </template>

    <div x-show="modifiers.length === 0" class="text-center py-12 text-secondary bg-card-bg rounded-2xl border border-dashed border-border-color shadow-sm">
        <i class="ri-node-tree text-5xl mb-3 inline-block opacity-40 text-primary"></i>
        <p class="font-bold">មិនមានទិន្នន័យទេ</p>
    </div>
</div>