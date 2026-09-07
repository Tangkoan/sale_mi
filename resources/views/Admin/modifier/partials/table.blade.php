<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4"><input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4"></th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('name')">
                        <div class="flex items-center gap-1">ជម្រើសលម្អិត <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold">ស្ថិតក្នុងក្រុម</th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('price')">
                        <div class="flex items-center gap-1">តម្លៃបូកថែម <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold">ស្ថានភាព</th>
                    <th class="px-6 py-4 font-bold text-right">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="item in modifiers" :key="'desktop-' + item.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group" :class="{'bg-primary/5': selectedIds.includes(item.id)}">
                        <td class="px-6 py-4"><input type="checkbox" :value="item.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4"></td>
                        <td class="px-6 py-4 font-bold text-text-color" x-text="item.name"></td>
                        
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs rounded-full border bg-page-bg border-border-color font-bold text-secondary" 
                                  x-text="item.group ? item.group.name : 'N/A'">
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 font-bold text-primary">
                            <span x-show="parseFloat(item.price) > 0" x-text="'+ ' + parseFloat(item.price).toLocaleString() + ' ៛'"></span>
                            <span x-show="parseFloat(item.price) <= 0" class="text-gray-400 text-sm">ឥតគិតថ្លៃ</span>
                        </td>

                        <td class="px-6 py-4">
                            <button @click="toggleStatus(item.id)" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="item.is_active ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="item.is_active ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button @click="openModal('edit', item)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-blue-50 text-blue-600 hover:bg-blue-100"><i class="ri-pencil-line"></i></button>
                                <button @click="confirmDelete(item.id)" class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 text-red-600 hover:bg-red-100"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="modifiers.length === 0"><td colspan="6" class="px-6 py-12 text-center text-secondary"><i class="ri-node-tree text-4xl mb-2 inline-block"></i><p>មិនមានទិន្នន័យទេ</p></td></tr>
            </tbody>
        </table>
    </div>
</div>