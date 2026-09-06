<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 font-bold">Logo</th>
                    <th class="px-6 py-4 font-bold cursor-pointer hover:text-primary transition-colors group" @click="sort('name')">
                        <div class="flex items-center gap-1">ឈ្មោះ <i class="ri-arrow-up-down-fill text-[10px] opacity-50 group-hover:opacity-100"></i></div>
                    </th>
                    <th class="px-6 py-4 font-bold">ស្ថានភាព</th>
                    <th class="px-6 py-4 font-bold text-right">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <template x-for="item in platforms" :key="'desktop-' + item.id">
                    <tr class="hover:bg-page-bg/30 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="h-10 w-10 rounded-full bg-gray-100 overflow-hidden border border-border-color p-1">
                                <template x-if="item.logo"><img :src="'/storage/' + item.logo" class="w-full h-full object-contain"></template>
                                <template x-if="!item.logo"><div class="w-full h-full flex items-center justify-center text-secondary"><i class="ri-image-line"></i></div></template>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-text-color" x-text="item.name"></td>
                        <td class="px-6 py-4">
                            <button @click="toggleStatus(item.id)" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="item.status === 'active' ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="item.status === 'active' ? 'translate-x-6' : 'translate-x-1'"></span>
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
                <tr x-show="platforms.length === 0"><td colspan="4" class="px-6 py-12 text-center text-secondary"><i class="ri-file-search-line text-4xl mb-2 inline-block"></i><p>មិនមានទិន្នន័យទេ</p></td></tr>
            </tbody>
        </table>
    </div>
</div>