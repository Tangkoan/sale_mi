<div class="flex flex-col gap-3">
    <div class="flex items-center justify-between px-2" x-show="roles.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>Select All</span>
        </label>
        <span class="text-xs text-secondary" x-text="roles.length + ' Items'"></span>
    </div>

    <template x-for="role in roles" :key="'mobile-' + role.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative overflow-hidden transition-all duration-200"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(role.id)}">
            
            {{-- Checkbox --}}
            <input type="checkbox" :value="role.id" x-model="selectedIds" 
                   class="absolute top-3 left-3 z-20 rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white cursor-pointer">

            <div class="pl-8 flex flex-col gap-2">
                {{-- Role Info --}}
                <div class="flex justify-between items-start">
                    <h3 class="font-bold text-text-color text-base" x-text="role.name"></h3>
                    <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs font-bold" x-text="'Lvl: ' + role.level"></span>
                </div>
                
                {{-- Users Count --}}
                <div class="flex items-center gap-2 text-xs text-secondary" x-show="showCols.users_count">
                    <i class="ri-group-line"></i>
                    <span x-text="(role.users_count || 0) + ' Users assigned'"></span>
                </div>

                {{-- Permissions Preview --}}
                <div class="flex flex-wrap gap-1 mt-1" x-show="showCols.permissions">
                    <template x-for="perm in (role.permissions || []).slice(0, 3)" :key="perm.id">
                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-input-bg border border-input-border text-secondary" x-text="perm.name.replace(/-/g, ' ')"></span>
                    </template>
                    <span x-show="(role.permissions || []).length > 3" class="text-[10px] text-primary font-bold self-center">
                        +<span x-text="role.permissions.length - 3"></span>
                    </span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end mt-2 pt-2 border-t border-dashed border-border-color">
                    <div class="flex gap-2 relative z-30">
                        <button @can('role-assign') @click="openPermissionModal(role)" @endcan class="h-8 w-8 rounded-full flex items-center justify-center bg-yellow-50 text-yellow-600 border border-yellow-100 hover:bg-yellow-100 active:scale-95 transition-transform cursor-pointer"><i class="ri-shield-keyhole-line"></i></button>
                        <button @can('role-edit') @click="openModal('edit', role)" @endcan class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 active:scale-95 transition-transform cursor-pointer"><i class="ri-pencil-fill"></i></button>
                        <button @can('role-delete') @click="confirmDelete(role.id)" @endcan class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 active:scale-95 transition-transform cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div x-show="roles.length === 0" class="text-center py-10 text-secondary bg-card-bg rounded-xl border border-dashed border-border-color">
        <i class="ri-shield-line text-4xl mb-2 inline-block opacity-50"></i>
        <p>{{ __('messages.no_roles_found') }}</p>
    </div>
</div>