<div class="flex flex-col gap-3">
    {{-- Select All Row --}}
    <div class="flex items-center justify-between px-2" x-show="addons.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>Select All</span>
        </label>
        <span class="text-xs text-secondary" x-text="addons.length + ' Items'"></span>
    </div>

    <template x-for="item in addons" :key="'mobile-' + item.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative overflow-hidden transition-all duration-200"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(item.id)}">
            
            {{-- Fixed Checkbox --}}
            <input type="checkbox" :value="item.id" x-model="selectedIds" 
                   class="absolute top-3 left-3 z-20 rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white cursor-pointer">

            <div class="pl-8 flex flex-col gap-2">
                
                {{-- ✅ 1. Name & Price Row --}}
                <div class="flex justify-between items-start">
                    {{-- Name --}}
                    <h3 class="font-bold text-text-color text-base" 
                        x-text="item.name" 
                        x-show="showCols.name"></h3> {{-- បន្ថែម x-show --}}
                    
                    {{-- Price --}}
                    <span class="text-sm font-extrabold text-primary shrink-0" 
                          x-text="'$' + parseFloat(item.price).toFixed(2)" 
                          x-show="showCols.price"></span> {{-- បន្ថែម x-show --}}
                </div>
                
                {{-- ✅ 2. Destination Row --}}
                <div class="flex flex-wrap items-center gap-2" x-show="showCols.destination"> {{-- បន្ថែម x-show --}}
                    <template x-if="item.destination">
                        <span class="text-xs text-secondary flex items-center gap-1 bg-page-bg px-1.5 py-0.5 rounded border border-border-color">
                            <i class="ri-printer-line"></i> 
                            <span x-text="item.destination.name"></span>
                        </span>
                    </template>
                    <template x-if="!item.destination">
                        <span class="text-xs text-gray-400 italic">No Destination</span>
                    </template>
                </div>

                {{-- Action Row --}}
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-dashed border-border-color">
                    
                    {{-- ✅ 3. Status Toggle --}}
                    <div x-show="showCols.status"> {{-- បន្ថែម x-show សម្រាប់ wrapper --}}
                        <div class="flex items-center gap-2">
                            <button type="button" @click="toggleStatus(item.id)" 
                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none cursor-pointer"
                                    :class="(item.is_active == 1 || item.is_active == true) ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform shadow-sm"
                                      :class="(item.is_active == 1 || item.is_active == true) ? 'translate-x-4' : 'translate-x-0.5'"></span>
                            </button>
                            <span class="text-[10px] font-bold uppercase" 
                                  :class="(item.is_active == 1 || item.is_active == true) ? 'text-green-600' : 'text-gray-400'" 
                                  x-text="(item.is_active == 1 || item.is_active == true) ? 'Active' : 'Inactive'"></span>
                        </div>
                    </div>
                    
                    {{-- Empty div to push actions to right if status is hidden --}}
                    <div x-show="!showCols.status"></div>

                    {{-- Buttons (Always Visible) --}}
                    <div class="flex gap-2 relative z-30">
                        <button type="button" @click="openModal('edit', item)" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 active:scale-95 transition-transform cursor-pointer"><i class="ri-pencil-fill"></i></button>
                        <button type="button" @click="confirmDelete(item.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 active:scale-95 transition-transform cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div x-show="addons.length === 0" class="text-center py-10 text-secondary bg-card-bg rounded-xl border border-dashed border-border-color">
        <i class="ri-puzzle-line text-4xl mb-2 inline-block opacity-50"></i>
        <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
    </div>
</div>