<div x-show="isPermissionModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center px-4" x-cloak>
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="isPermissionModalOpen = false"></div>
    <div class="relative w-full max-w-4xl bg-card-bg rounded-2xl shadow-2xl border border-border-color overflow-hidden flex flex-col max-h-[90vh]" 
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        <div class="px-6 py-4 border-b border-border-color flex justify-between items-center bg-page-bg/30 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600">
                    <i class="ri-shield-keyhole-line text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-text-color">{{ __('messages.assign_permissions') }}</h3>
                    <p class="text-xs text-secondary">Role: <span class="font-bold text-primary" x-text="permissionForm.roleName"></span></p>
                </div>
            </div>
            <button @click="isPermissionModalOpen = false" class="text-secondary hover:text-text-color"><i class="ri-close-line text-xl"></i></button>
        </div>

        <div class="overflow-y-auto p-6 bg-card-bg">
            <div class="flex justify-between items-center mb-4">
                <label class="text-sm font-bold text-text-color">{{ __('messages.select_permissions') }}</label>
                <div class="flex gap-3">
                    <button type="button" @click="selectAllPermissions()" class="text-xs text-primary font-bold hover:underline">{{ __('messages.select_all') }}</button>
                    <button type="button" @click="permissionForm.permissions = []" class="text-xs text-red-500 font-bold hover:underline">{{ __('messages.uncheck_all') }}</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <template x-for="perm in allAvailablePermissions" :key="perm.id">
                    <label class="flex items-center space-x-3 p-3 rounded-xl border border-input-border bg-input-bg hover:border-primary/50 cursor-pointer transition-all hover:shadow-sm select-none">
                        <div class="relative flex items-center">
                            <input type="checkbox" :value="perm.name" x-model="permissionForm.permissions"
                                   class="peer w-5 h-5 cursor-pointer appearance-none rounded border border-input-border checked:bg-primary checked:border-primary transition-all">
                            <i class="ri-check-line absolute text-white text-sm opacity-0 peer-checked:opacity-100 pointer-events-none left-[2px]"></i>
                        </div>
                        <span class="text-sm text-text-color capitalize font-medium" x-text="perm.name.replace(/-/g, ' ')"></span>
                    </label>
                </template>
                <div x-show="allAvailablePermissions.length === 0" class="col-span-full text-center text-gray-500 py-4">
                    {{ __('messages.no_assignable_permissions') }}
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-border-color bg-page-bg/30 flex justify-end gap-3 flex-shrink-0">
            <button type="button" @click="isPermissionModalOpen = false" class="px-4 py-2 rounded-lg border border-input-border text-text-color hover:bg-page-bg transition">{{ __('messages.cancel') }}</button>
            <button type="button" @click="submitPermissions" class="bg-yellow-500 text-white px-6 py-2 rounded-lg hover:opacity-90 transition flex items-center gap-2" :disabled="isLoading">
                <i x-show="isLoading" class="ri-loader-4-line animate-spin"></i>
                <span x-text="isLoading ? '{{ __('messages.saving') }}' : '{{ __('messages.save') }}'"></span>
            </button>
        </div>
    </div>
</div>