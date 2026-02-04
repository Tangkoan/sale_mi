<div class="flex flex-col gap-3">
    
    {{-- Select All Row --}}
    <div class="flex items-center justify-between px-2" x-show="products.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>Select All</span>
        </label>
        <span class="text-xs text-secondary" x-text="products.length + ' Items'"></span>
    </div>

    <template x-for="item in products" :key="'mobile-' + item.id">
        <div class="bg-card-bg p-3 rounded-2xl shadow-sm border border-border-color relative overflow-hidden transition-all duration-200"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(item.id)}">
            
            {{-- ✅ FIX: Checkbox moved outside image container and positioned absolutely --}}
            <input type="checkbox" :value="item.id" x-model="selectedIds" 
                   class="absolute top-3 left-3 z-20 rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white">

            <div class="flex gap-3 pl-1"> 
                
                {{-- Left: Image Area (Can be hidden) --}}
                <div class="relative shrink-0" x-show="showCols.image">
                    <div class="h-20 w-20 rounded-xl bg-gray-100 overflow-hidden border border-border-color">
                        <template x-if="item.image"><img :src="'/storage/' + item.image" class="w-full h-full object-cover"></template>
                        <template x-if="!item.image"><div class="w-full h-full flex items-center justify-center text-secondary"><i class="ri-image-line text-2xl"></i></div></template>
                    </div>
                </div>

                {{-- Right: Content --}}
                <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5" :class="{'pl-8': !showCols.image}">
                    
                    <div>
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-text-color text-base truncate pr-1" x-text="item.name" :class="{'pl-6': showCols.image}"></h3>
                            <span class="text-sm font-extrabold text-primary shrink-0" x-text="'$' + parseFloat(item.price).toFixed(2)" x-show="showCols.price"></span>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-2 mt-1" x-show="showCols.category" :class="{'pl-6': showCols.image}">
                            <span class="text-xs text-secondary flex items-center gap-1 bg-page-bg px-1.5 py-0.5 rounded border border-border-color">
                                <i class="ri-folder-3-line"></i> 
                                <span x-text="item.category ? item.category.name : 'No Category'"></span>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-dashed border-border-color">
                        <div class="flex items-center gap-2">
                            <button @can('product-edit-status') @click="toggleStatus(item.id)" @endcan class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none" :class="item.is_active ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform shadow-sm" :class="item.is_active ? 'translate-x-4' : 'translate-x-0.5'"></span>
                            </button>
                            <span class="text-[10px] font-bold uppercase" :class="item.is_active ? 'text-green-600' : 'text-gray-400'" x-text="item.is_active ? 'Active' : 'Inactive'"></span>
                        </div>

                        <div class="flex gap-2">
                            <button @click="openModal('edit', item)" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 active:scale-95 transition-transform"><i class="ri-pencil-fill"></i></button>
                            <button @click="confirmDelete(item.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 active:scale-95 transition-transform"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div x-show="products.length === 0" class="text-center py-10 text-secondary bg-card-bg rounded-xl border border-dashed border-border-color">
        <i class="ri-search-2-line text-4xl mb-2 inline-block opacity-50"></i>
        <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
    </div>
</div>