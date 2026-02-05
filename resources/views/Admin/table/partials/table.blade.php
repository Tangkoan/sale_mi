<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4">
                        <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                    </th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('name')" x-show="showCols.name">
                        <div class="flex items-center gap-1">{{ __('messages.table_name') }} <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('status')" x-show="showCols.status">
                        <div class="flex items-center gap-1">{{ __('messages.status') }} <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('created_at')" x-show="showCols.created_at">
                        <div class="flex items-center gap-1">{{ __('messages.created_at') }} <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="item in tables" :key="'desktop-' + item.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                        <td class="px-6 py-4">
                            <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </td>
                        <td class="px-6 py-4 font-bold text-text-color" x-text="item.name" x-show="showCols.name"></td>
                        <td class="px-6 py-4" x-show="showCols.status">
                            <span class="px-3 py-1 rounded-full text-xs font-bold capitalize flex items-center w-fit gap-1"
                                  :class="item.status === 'available' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'">
                                <span class="w-2 h-2 rounded-full" :class="item.status === 'available' ? 'bg-green-600' : 'bg-red-600'"></span>
                                <span x-text="item.status"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-secondary text-sm" x-text="new Date(item.created_at).toLocaleDateString()" x-show="showCols.created_at"></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 relative z-10">
                                @can('table-edit')
                                <button type="button" @click="openModal('edit', item)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100 cursor-pointer"><i class="ri-pencil-line"></i></button>
                                @endcan
                                @can('table-delete')
                                <button type="button" @click="confirmDelete(item.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100 cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="tables.length === 0">
                    <td colspan="100%" class="px-6 py-12 text-center text-secondary">
                        <i class="ri-layout-grid-line text-4xl mb-2 inline-block opacity-50"></i>
                        <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>