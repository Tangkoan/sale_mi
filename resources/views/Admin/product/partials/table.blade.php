<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4"><input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4"></th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.image">{{ __('messages.image') }}</th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('name')">
                        <div class="flex items-center gap-1">{{ __('messages.product_name') }}<i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.category">{{ __('messages.category') }}</th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('price')" x-show="showCols.price">
                        <div class="flex items-center gap-1">{{ __('messages.price') }}<i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold">{{ __('messages.status') }}</th>
                    <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="item in products" :key="'desktop-' + item.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                        <td class="px-6 py-4"><input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4"></td>
                        <td class="px-6 py-4" x-show="showCols.image">
                            <div class="h-10 w-10 rounded-lg bg-gray-100 overflow-hidden border border-border-color">
                                <template x-if="item.image"><img :src="'/storage/' + item.image" class="w-full h-full object-cover"></template>
                                <template x-if="!item.image"><div class="w-full h-full flex items-center justify-center text-secondary"><i class="ri-image-line"></i></div></template>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-text-color" x-text="item.name"></td>
                        <td class="px-6 py-4" x-show="showCols.category">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-200" x-text="item.category ? item.category.name : 'N/A'"></span>
                                <template x-if="item.category && item.category.destination">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border"
                                        :class="{
                                            'bg-orange-50 test-primary border-orange-200': item.category.destination.name.toLowerCase().includes('wok'),
                                            'bg-red-50 text-red-600 border-red-200': item.category.destination.name.toLowerCase().includes('soup'),
                                            'bg-blue-50 text-blue-600 border-blue-200': item.category.destination.name.toLowerCase().includes('bar')
                                        }">
                                        <i class="ri-printer-line mr-1"></i><span x-text="item.category.destination.name"></span>
                                    </span>
                                </template>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-primary" x-text="'$' + parseFloat(item.price).toFixed(2)" x-show="showCols.price"></td>
                        <td class="px-6 py-4">
                            @can('product-edit-status')
                            <button @click="toggleStatus(item.id)" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="item.is_active ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="item.is_active ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full border" 
                                  :class="item.is_active ? 'bg-green-100 text-green-600 border-green-200' : 'bg-gray-100 text-gray-500 border-gray-200'"
                                  x-text="item.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'">
                            </span>
                            @endcan
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                @can('product-edit')
                                <button @click="openModal('edit', item)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100" title="{{ __('messages.edit') }}"><i class="ri-pencil-line"></i></button>
                                @endcan
                                @can('product-delete')
                                <button @click="confirmDelete(item.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100" title="{{ __('messages.delete') }}"><i class="ri-delete-bin-line"></i></button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="products.length === 0"><td colspan="100%" class="px-6 py-12 text-center text-secondary"><i class="ri-shopping-bag-3-line text-4xl mb-2 inline-block"></i><p>{{ __('messages.no_products_found') }}</p></td></tr>
            </tbody>
        </table>
    </div>
</div>