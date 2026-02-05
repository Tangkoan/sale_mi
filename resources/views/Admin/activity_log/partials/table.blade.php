<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 w-4">
                        <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4 cursor-pointer">
                    </th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.causer">{{ __('messages.user_actor') }}</th>
                    <th class="px-6 py-4 font-bold">{{ __('messages.action') }}</th>
                    <th class="px-6 py-4 font-bold" x-show="showCols.subject">{{ __('messages.subject') }}</th>
                    <th class="px-6 py-4 font-bold w-1/3" x-show="showCols.changes">{{ __('messages.changes') }}</th>
                    <th class="px-6 py-4 font-bold text-right" x-show="showCols.date">{{ __('messages.date') }}</th>
                    @can('activity-delete')
                    <th class="px-6 py-4 font-bold text-right w-20">{{ __('messages.actions') }}</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color text-sm text-text-color">
                <template x-if="isLoading">
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-secondary">
                            <i class="ri-loader-4-line text-3xl animate-spin inline-block mb-2"></i>
                            <p>{{ __('messages.loading_logs') }}</p>
                        </td>
                    </tr>
                </template>

                <template x-for="log in logs" :key="log.id">
                    <tr class="hover:bg-page-bg/30 transition-colors align-top group" :class="{'bg-primary/5': selectedIds.includes(log.id)}">
                        <td class="px-6 py-4">
                            <input type="checkbox" :value="log.id" x-model="selectedIds" class="rounded border-input-border text-primary focus:ring-primary h-4 w-4 cursor-pointer">
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap" x-show="showCols.causer">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs border border-primary/20" x-text="log.causer_initial"></div>
                                <div>
                                    <div class="font-bold text-text-color" x-text="log.causer_name"></div>
                                    <div class="text-xs text-secondary" x-text="log.causer_email"></div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wide border border-opacity-20" :class="log.badge_class" x-text="log.description"></span>
                        </td>

                        <td class="px-6 py-4" x-show="showCols.subject">
                            <div class="flex flex-col">
                                <span class="font-bold text-primary" x-text="log.subject_type"></span>
                                <span class="text-xs text-secondary font-mono bg-page-bg px-1.5 py-0.5 rounded w-fit mt-1 border border-border-color" x-text="'ID: ' + log.subject_id"></span>
                            </div>
                        </td>

                        <td class="px-6 py-4" x-show="showCols.changes">
                            <div class="text-xs text-text-color bg-page-bg/50 rounded-lg p-3 border border-border-color shadow-sm max-w-md overflow-x-auto custom-scrollbar" x-html="log.changes_html"></div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-right" x-show="showCols.date">
                            <div class="text-sm font-medium text-text-color" x-text="log.created_at_date"></div>
                            <div class="text-xs text-secondary mt-0.5 flex items-center justify-end gap-1">
                                <i class="ri-time-line"></i>
                                <span x-text="log.created_at_ago"></span>
                            </div>
                        </td>

                        @can('activity-delete')
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 relative z-10">
                                <button type="button" @click="confirmDelete(log.id)" 
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-colors bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 border border-transparent hover:border-red-200 cursor-pointer" 
                                        title="{{ __('messages.delete') }}">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </td>
                        @endcan
                    </tr>
                </template>

                <tr x-show="!isLoading && logs.length === 0">
                    <td colspan="7" class="px-6 py-12 text-center text-secondary">
                        <i class="ri-ghost-line text-4xl mb-2 inline-block opacity-50"></i>
                        <p>{{ __('messages.no_logs_found') }}</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>