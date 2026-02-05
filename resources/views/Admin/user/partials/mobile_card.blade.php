<div class="flex flex-col gap-3">
    <div class="flex items-center justify-between px-2" x-show="users.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>Select All</span>
        </label>
        <span class="text-xs text-secondary" x-text="users.length + ' Items'"></span>
    </div>

    <template x-for="user in users" :key="'mobile-' + user.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative overflow-hidden transition-all duration-200"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(user.id)}">
            
            {{-- Checkbox --}}
            <input type="checkbox" :value="user.id" x-model="selectedIds" 
                   class="absolute top-3 left-3 z-20 rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white cursor-pointer">

            <div class="pl-8 flex flex-col gap-2">
                {{-- User Info --}}
                <div class="flex items-center gap-3">
                    <x-avatar /> 
                    <div>
                        <h3 class="font-bold text-text-color text-base" x-text="user.name"></h3>
                        <p class="text-xs text-secondary" x-show="showCols.email" x-text="user.email"></p>
                    </div>
                </div>
                
                {{-- Role Badge --}}
                <div class="flex flex-wrap items-center gap-2" x-show="showCols.role">
                    <template x-for="role in user.roles">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-600 border border-blue-200" x-text="role.name"></span>
                    </template>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-dashed border-border-color">
                    <span class="text-xs text-secondary" x-show="showCols.created_at" x-text="new Date(user.created_at).toLocaleDateString()"></span>
                    
                    <div class="flex gap-2 relative z-30">
                        <button type="button" @click="openModal('edit', user)" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 active:scale-95 transition-transform cursor-pointer"><i class="ri-pencil-fill"></i></button>
                        <button type="button" @click="confirmDelete(user.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 active:scale-95 transition-transform cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div x-show="users.length === 0" class="text-center py-10 text-secondary bg-card-bg rounded-xl border border-dashed border-border-color">
        <i class="ri-ghost-line text-4xl mb-2 inline-block opacity-50"></i>
        <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
    </div>
</div>