<div class="flex flex-col gap-3">
    <div class="flex items-center justify-between px-2 bg-card-bg py-2 rounded-xl border border-border-color shadow-sm" x-show="tables.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>{{ __('messages.select_all') }}</span>
        </label>
        <span class="text-xs font-bold text-primary bg-primary/10 px-3 py-1 rounded-full"><span x-text="tables.length"></span> {{ __('messages.items') }}</span>
    </div>

    <template x-for="item in tables" :key="'mobile-' + item.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative overflow-hidden transition-all duration-200"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(item.id)}">
            
            {{-- Checkbox --}}
            <input type="checkbox" :value="item.id" x-model="selectedIds" 
                   class="absolute top-4 left-4 z-20 rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white cursor-pointer">

            <div class="pl-8 flex flex-col gap-2">
                <div class="flex justify-between items-start">
                    <h3 class="font-extrabold text-text-color text-base" x-text="item.name"></h3>
                    
                    {{-- Status Badge សម្រាប់ Mobile (គ្មាន Toggle) --}}
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-sm"
                          :class="item.status === 'available' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="item.status === 'available' ? 'bg-green-600' : 'bg-red-600'"></span>
                        <span x-text="item.status === 'available' ? '{{ __('messages.available') }}' : '{{ __('messages.busy') }}'"></span>
                    </span>
                </div>
                
                <div class="flex items-center justify-between mt-2 pt-3 border-t border-dashed border-border-color">
                    <span class="text-xs font-semibold text-secondary flex items-center gap-1"><i class="ri-calendar-line"></i> <span x-text="new Date(item.created_at).toLocaleDateString()"></span></span>
                    <div class="flex gap-2 relative z-30">
                        @can('table-edit')
                        <button type="button" @click="openModal('edit', item)" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 active:scale-95 transition-transform cursor-pointer shadow-sm"><i class="ri-pencil-fill"></i></button>
                        @endcan
                        @can('table-delete')
                        <button type="button" @click="confirmDelete(item.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 active:scale-95 transition-transform cursor-pointer shadow-sm"><i class="ri-delete-bin-line"></i></button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div x-show="tables.length === 0" class="text-center py-10 text-secondary bg-card-bg rounded-xl border border-dashed border-border-color">
        <i class="ri-layout-grid-line text-4xl mb-2 inline-block opacity-50"></i>
        <p>{{ __('messages.no_tables_found') }}</p>
    </div>
</div>