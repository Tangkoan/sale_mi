<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4">
                        <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                    </th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('name')" x-show="showCols.name">
                        <div class="flex items-center gap-1">{{ __('messages.addon_name') }} <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('price')" x-show="showCols.price">
                        <div class="flex items-center gap-1">{{ __('messages.price') }} <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('destination')" x-show="showCols.destination">
                        <div class="flex items-center gap-1">Destination <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.status">{{ __('messages.status') }}</th>
                    <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="item in addons" :key="'desktop-' + item.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                        <td class="px-6 py-4">
                            <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </td>
                        <td class="px-6 py-4 font-bold text-text-color" x-text="item.name" x-show="showCols.name"></td>
                        <td class="px-6 py-4 font-bold text-primary" x-text="'$' + parseFloat(item.price).toFixed(2)" x-show="showCols.price"></td>
                        <td class="px-6 py-4" x-show="showCols.destination">
                            <template x-if="item.destination">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200 inline-flex items-center gap-1">
                                    <i class="ri-printer-line text-sm"></i>
                                    <span x-text="item.destination.name"></span>
                                </span>
                            </template>
                            <template x-if="!item.destination">
                                <span class="text-xs text-gray-400 italic">No Destination</span>
                            </template>
                        </td>
                        <td class="px-6 py-4" x-show="showCols.status">
                            <button type="button" @click="toggleStatus(item.id)" 
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none cursor-pointer"
                                    :class="(item.is_active == 1 || item.is_active == true) ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm"
                                      :class="(item.is_active == 1 || item.is_active == true) ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 relative z-10">
                                <button type="button" @click="openModal('edit', item)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100 cursor-pointer"><i class="ri-pencil-line"></i></button>
                                <button type="button" @click="confirmDelete(item.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100 cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="addons.length === 0">
                    <td colspan="100%" class="px-6 py-12 text-center text-secondary">
                        <i class="ri-puzzle-line text-4xl mb-2 inline-block opacity-50"></i>
                        <p>{{ __('messages.no_users_found_matching_your_search') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>