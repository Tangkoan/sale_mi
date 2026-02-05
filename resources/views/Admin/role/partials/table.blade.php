<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4">
                        <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                    </th>
                    <th class="px-6 py-4 font-bold">{{ __('messages.th_role_name') }}</th>
                    <th class="px-6 py-4 font-bold">{{ __('messages.th_level') }}</th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.permissions">{{ __('messages.th_permissions') }}</th>
                    <th class="px-6 py-4 font-bold text-center" x-show="showCols.users_count">{{ __('messages.th_users') }}</th>
                    <th class="px-6 py-4 font-bold text-right w-40">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="role in roles" :key="'desktop-' + role.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(role.id)}">
                        <td class="px-6 py-4 align-top">
                            <input type="checkbox" :value="role.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </td>
                        
                        <td class="px-6 py-4 align-top">
                            <span class="font-bold text-text-color text-lg" x-text="role.name"></span>
                        </td>

                        <td class="px-6 py-4 align-top">
                            <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-sm font-bold" x-text="role.level"></span>
                        </td>

                        <td class="px-6 py-4 align-top" x-show="showCols.permissions">
                            <div class="flex flex-wrap gap-2">
                                <template x-if="role.permissions && role.permissions.length > 0">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="perm in role.permissions.slice(0, 5)" :key="perm.id">
                                            <span class="px-2 py-1 rounded text-xs font-medium bg-input-bg border border-input-border text-secondary select-none" 
                                                  x-text="perm.name.replace(/-/g, ' ')"></span>
                                        </template>
                                        <span x-show="role.permissions.length > 5" class="text-xs text-secondary px-1 self-center">
                                            +<span x-text="role.permissions.length - 5"></span> {{ __('messages.more') }}
                                        </span>
                                    </div>
                                </template>
                                <span x-show="!role.permissions || role.permissions.length === 0" class="text-xs text-secondary italic opacity-50">
                                    {{ __('messages.no_permissions_assigned') }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center align-top" x-show="showCols.users_count">
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-blue-100 bg-blue-600 rounded-full" x-text="role.users_count || 0"></span>
                        </td>
                        
                        <td class="px-6 py-4 text-right align-top">
                            <div class="flex justify-end gap-2 relative z-10">
                                <button @can('role-assign') @click="openPermissionModal(role)" @endcan
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors
                                        @can('role-assign') bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 hover:bg-yellow-100 @else bg-gray-100 text-gray-400 cursor-not-allowed @endcan"
                                        title="Assign Permissions">
                                    <i class="ri-shield-keyhole-line"></i>
                                </button>

                                <button @can('role-edit') @click="openModal('edit', role)" @endcan 
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100 cursor-pointer"><i class="ri-pencil-line"></i></button>
                                
                                <button @can('role-delete') @click="confirmDelete(role.id)" @endcan 
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100 cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="roles.length === 0">
                    <td colspan="100%" class="px-6 py-12 text-center text-secondary">
                        <i class="ri-shield-line text-4xl mb-2 inline-block opacity-50"></i>
                        <p>{{ __('messages.no_roles_found') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>