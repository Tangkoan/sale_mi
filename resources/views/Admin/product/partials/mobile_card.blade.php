<div class="flex flex-col gap-4">
    
    {{-- Select All Row --}}
    <div class="flex items-center justify-between px-2 bg-card-bg py-2 rounded-xl border border-border-color shadow-sm" x-show="products.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>{{ __('messages.select_all') }}</span>
        </label>
        <span class="text-xs font-bold text-primary px-3 py-1 bg-primary/10 rounded-full"><span x-text="products.length"></span> {{ __('messages.items') }}</span>
    </div>

    <template x-for="item in products" :key="'mobile-' + item.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative transition-all duration-200 flex flex-col gap-3"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(item.id)}">
            
            {{-- Top bar: Checkbox + Status + Actions --}}
            <div class="flex items-center justify-between border-b border-dashed border-border-color pb-2">
                <div class="flex items-center gap-3">
                    <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white">
                    <div class="flex items-center gap-1.5">
                        @can('product-edit-status')
                        <button @click="toggleStatus(item.id)" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none" :class="item.is_active ? 'bg-green-500' : 'bg-gray-300'">
                            <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform shadow-sm" :class="item.is_active ? 'translate-x-4' : 'translate-x-0.5'"></span>
                        </button>
                        @else
                        <span class="w-2.5 h-2.5 rounded-full" :class="item.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                        @endcan
                        <span class="text-[10px] font-bold uppercase tracking-wider" :class="item.is_active ? 'text-green-600' : 'text-gray-400'" x-text="item.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'"></span>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    @can('product-edit')
                    <button @click="openModal('edit', item)" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 active:scale-95 transition-transform shadow-sm"><i class="ri-pencil-fill"></i></button>
                    @endcan
                    @can('product-delete')
                    <button @click="confirmDelete(item.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 active:scale-95 transition-transform shadow-sm"><i class="ri-delete-bin-line"></i></button>
                    @endcan
                </div>
            </div>

            {{-- Main Content: Image & Details --}}
            <div class="flex gap-3 items-center">
                <div class="relative shrink-0" x-show="showCols.image">
                    <div class="h-20 w-20 rounded-xl bg-gray-50 overflow-hidden border border-border-color shadow-sm">
                        <template x-if="item.image"><img :src="'/storage/' + item.image" class="w-full h-full object-cover"></template>
                        <template x-if="!item.image"><div class="w-full h-full flex items-center justify-center text-secondary bg-gray-100"><i class="ri-image-line text-3xl"></i></div></template>
                    </div>
                </div>
                
                <div class="flex-1 min-w-0 flex flex-col justify-center py-1">
                    <h3 class="font-extrabold text-text-color text-base truncate pr-1 mb-1" x-text="item.name"></h3>
                    
                    <div class="flex items-center gap-2 mb-2" x-show="showCols.category">
                        <span class="text-xs text-secondary flex items-center gap-1.5 bg-page-bg px-2 py-1.5 rounded-lg border border-border-color truncate max-w-[150px]">
                            <i class="ri-folder-3-line text-primary"></i> 
                            <span x-text="item.category ? item.category.name : 'No Category'" class="truncate"></span>
                        </span>
                    </div>

                    <div x-show="showCols.price">
                        <span class="text-base font-black text-primary bg-primary/5 px-2 py-0.5 rounded-lg border border-primary/20" x-text="parseFloat(item.price).toLocaleString() + ' ៛'"></span>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div x-show="products.length === 0" class="text-center py-12 text-secondary bg-card-bg rounded-2xl border border-dashed border-border-color shadow-sm">
        <i class="ri-file-search-line text-5xl mb-3 inline-block opacity-40 text-primary"></i>
        <p class="font-bold">{{ __('messages.no_products_found') }}</p>
    </div>
</div>