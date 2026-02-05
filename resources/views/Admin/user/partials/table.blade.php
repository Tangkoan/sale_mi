<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4">
                        <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                    </th>
                    <th class="px-6 py-4 font-bold">{{ __('messages.user') }}</th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.role">{{ __('messages.role') }}</th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.email">{{ __('messages.email') }}</th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.created_at">{{ __('messages.created_at') }}</th>
                    <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="user in users" :key="'desktop-' + user.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(user.id)}">
                        <td class="px-6 py-4">
                            <input type="checkbox" :value="user.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <x-avatar /> 
                                <p class="font-bold text-text-color" x-text="user.name"></p>
                            </div>
                        </td>

                        <td class="px-6 py-4" x-show="showCols.role">
                            <template x-for="role in user.roles">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800" x-text="role.name"></span>
                            </template>
                        </td>

                        <td class="px-6 py-4 text-secondary text-sm" x-show="showCols.email" x-text="user.email"></td>
                        <td class="px-6 py-4 text-secondary text-sm" x-show="showCols.created_at" x-text="new Date(user.created_at).toLocaleDateString()"></td>
                        
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 relative z-10">
                                <button type="button" @click="openModal('edit', user)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100 cursor-pointer"><i class="ri-pencil-line"></i></button>
                                <button type="button" @click="confirmDelete(user.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100 cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="users.length === 0">
                    <td colspan="100%" class="px-6 py-12 text-center text-secondary">
                        <i class="ri-ghost-line text-4xl mb-2 inline-block opacity-50"></i>
                        <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>