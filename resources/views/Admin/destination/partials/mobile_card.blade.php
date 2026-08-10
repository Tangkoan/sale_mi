<div class="flex flex-col gap-3">
    {{-- Select All Row --}}
    <div class="flex items-center justify-between px-2" x-show="destinations.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>{{ __('messages.select_all') }}</span>
        </label>
        <span class="text-xs text-secondary" x-text="destinations.length + ' {{ __('messages.items') }}'"></span>
    </div>

    <template x-for="item in destinations" :key="'mobile-' + item.id">
        <div class="bg-card-bg p-3 rounded-2xl shadow-sm border border-border-color relative overflow-hidden transition-all duration-200"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(item.id)}">
            
            {{-- Checkbox --}}
            <input type="checkbox" :value="item.id" x-model="selectedIds" 
                   class="absolute top-3 left-3 z-20 rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white">

            <div class="pl-8"> {{-- Padding Left for Checkbox --}}
                <div class="flex justify-between items-start">
                    <h3 class="font-bold text-text-color text-base truncate pr-1" x-text="item.name"></h3>
                    <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-300" x-text="item.printnode_id || '{{ __('messages.no_id') }}'"></span>
                </div>
                
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-dashed border-border-color">
                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-green-100 text-green-600 border border-green-200 font-bold uppercase">{{ __('messages.active') }}</span>

                    <div class="flex gap-2">
                        <button @click="openModal('edit', item)" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 active:scale-95 transition-transform"><i class="ri-pencil-fill"></i></button>
                        <button @click="confirmDelete(item.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 active:scale-95 transition-transform"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div x-show="destinations.length === 0" class="text-center py-10 text-secondary bg-card-bg rounded-xl border border-dashed border-border-color">
        <i class="ri-printer-cloud-line text-4xl mb-2 inline-block opacity-50"></i>
        <p>{{ __('messages.no_destinations') }}</p>
    </div>
</div>