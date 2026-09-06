<div class="flex flex-col gap-4">
    <template x-for="item in platforms" :key="'mobile-' + item.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative transition-all duration-200 flex flex-col gap-3">
            
            <div class="flex items-center justify-between border-b border-dashed border-border-color pb-2">
                <div class="flex items-center gap-1.5">
                    <button @click="toggleStatus(item.id)" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none" :class="item.status === 'active' ? 'bg-green-500' : 'bg-gray-300'">
                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform shadow-sm" :class="item.status === 'active' ? 'translate-x-4' : 'translate-x-0.5'"></span>
                    </button>
                    <span class="text-[10px] font-bold uppercase tracking-wider" :class="item.status === 'active' ? 'text-green-600' : 'text-gray-400'" x-text="item.status === 'active' ? 'Active' : 'Inactive'"></span>
                </div>
                
                <div class="flex gap-2">
                    <button @click="openModal('edit', item)" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100"><i class="ri-pencil-fill"></i></button>
                    <button @click="confirmDelete(item.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100"><i class="ri-delete-bin-line"></i></button>
                </div>
            </div>

            <div class="flex gap-3 items-center">
                <div class="relative shrink-0">
                    <div class="h-16 w-16 rounded-full bg-gray-50 overflow-hidden border border-border-color shadow-sm p-1">
                        <template x-if="item.logo"><img :src="'/storage/' + item.logo" class="w-full h-full object-contain"></template>
                        <template x-if="!item.logo"><div class="w-full h-full flex items-center justify-center text-secondary bg-gray-100"><i class="ri-image-line text-2xl"></i></div></template>
                    </div>
                </div>
                
                <div class="flex-1 min-w-0 flex flex-col justify-center py-1">
                    <h3 class="font-extrabold text-text-color text-base truncate pr-1" x-text="item.name"></h3>
                </div>
            </div>
        </div>
    </template>

    <div x-show="platforms.length === 0" class="text-center py-12 text-secondary bg-card-bg rounded-2xl border border-dashed border-border-color shadow-sm">
        <i class="ri-file-search-line text-5xl mb-3 inline-block opacity-40 text-primary"></i>
        <p class="font-bold">មិនមានទិន្នន័យទេ</p>
    </div>
</div>