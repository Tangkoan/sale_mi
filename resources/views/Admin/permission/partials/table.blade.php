<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4">
                        <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4 cursor-pointer">
                    </th>
                    <th class="px-6 py-4 font-bold">{{ __('messages.th_permission_name') }}</th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.guard_name">{{ __('messages.guard_name') }}</th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.created_at">{{ __('messages.created_at') }}</th>
                    <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="perm in permissions" :key="'desktop-' + perm.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(perm.id)}">
                        <td class="px-6 py-4">
                            <input type="checkbox" :value="perm.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4 cursor-pointer">
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-input-bg border border-input-border flex items-center justify-center text-secondary">
                                    <i class="ri-shield-keyhole-line"></i>
                                </div>
                                <span class="font-bold text-text-color" x-text="perm.name"></span>
                            </div>
                        </td>

                        <td class="px-6 py-4" x-show="showCols.guard_name">
                            <span class="px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-600 border border-blue-200" x-text="perm.guard_name"></span>
                        </td>

                        <td class="px-6 py-4 text-secondary text-sm" x-show="showCols.created_at" x-text="new Date(perm.created_at).toLocaleDateString()"></td>
                        
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 relative z-10">
                                @role('Super Admin')
                                <button type="button" @click="openModal('edit', perm)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100 cursor-pointer"><i class="ri-pencil-line"></i></button>
                                <button type="button" @click="confirmDelete(perm.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100 cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                                @endrole
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="permissions.length === 0">
                    <td colspan="100%" class="px-6 py-12 text-center text-secondary">
                        <i class="ri-file-search-line text-4xl mb-2 inline-block opacity-50"></i>
                        <p>{{ __('messages.no_permissions_found') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>