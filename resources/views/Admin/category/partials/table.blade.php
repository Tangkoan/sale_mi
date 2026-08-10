<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4">
                        <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                    </th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.image">{{ __('messages.image') }}</th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('name')">
                        <div class="flex items-center gap-1">{{ __('messages.category_name') }} <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.destination">{{ __('messages.destination') }}</th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('created_at')" x-show="showCols.created_at">
                        <div class="flex items-center gap-1">{{ __('messages.created_at') }} <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="item in categories" :key="'desktop-' + item.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                        <td class="px-6 py-4">
                            <input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4">
                        </td>
                        <td class="px-6 py-4" x-show="showCols.image">
                            <div class="h-10 w-10 rounded-lg bg-gray-100 overflow-hidden border border-border-color">
                                <template x-if="item.image"><img :src="'/storage/' + item.image" class="w-full h-full object-cover"></template>
                                <template x-if="!item.image"><div class="w-full h-full flex items-center justify-center text-secondary"><i class="ri-image-line"></i></div></template>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-text-color" x-text="item.name"></td>
                        <td class="px-6 py-4" x-show="showCols.destination">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200 inline-flex items-center gap-1" x-show="item.destination">
                                <i class="ri-printer-line text-sm"></i> <span x-text="item.destination ? item.destination.name : ''"></span>
                            </span>
                            <span x-show="!item.destination" class="text-xs text-secondary italic">{{ __('messages.not_assigned') }}</span>
                        </td>
                        <td class="px-6 py-4 text-secondary text-sm" x-show="showCols.created_at" x-text="new Date(item.created_at).toLocaleDateString()"></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 relative z-10">
                                @can('category-edit')
                                <button type="button" @click="openModal('edit', item)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100 cursor-pointer"><i class="ri-pencil-line"></i></button>
                                @endcan

                                @can('category-delete')
                                <button type="button" @click="confirmDelete(item.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100 cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="categories.length === 0">
                    <td colspan="6" class="px-6 py-12 text-center text-secondary">
                        <i class="ri-folder-open-line text-4xl mb-2 inline-block opacity-50"></i>
                        <p>{{ __('messages.no_categories_found') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>