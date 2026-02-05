<div class="flex flex-col gap-3">
    <div class="flex items-center justify-between px-2" x-show="logs.length > 0">
        <label class="flex items-center gap-2 text-sm font-bold text-text-color select-none cursor-pointer">
            <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-input-border text-primary focus:ring-primary h-5 w-5">
            <span>Select All</span>
        </label>
        <span class="text-xs text-secondary" x-text="logs.length + ' Items'"></span>
    </div>

    <template x-for="log in logs" :key="'mobile-' + log.id">
        <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative overflow-hidden transition-all duration-200"
             :class="{'ring-2 ring-primary bg-primary/5': selectedIds.includes(log.id)}">
            
            {{-- Checkbox --}}
            <input type="checkbox" :value="log.id" x-model="selectedIds" 
                   class="absolute top-3 left-3 z-20 rounded-md border-gray-300 text-primary focus:ring-primary h-5 w-5 shadow-sm bg-white cursor-pointer">

            <div class="pl-8 flex flex-col gap-3">
                
                {{-- Header: Actor & Action --}}
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-2" x-show="showCols.causer">
                        <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px] border border-primary/20" x-text="log.causer_initial"></div>
                        <div>
                            <p class="font-bold text-text-color text-sm" x-text="log.causer_name"></p>
                            <p class="text-[10px] text-secondary" x-text="log.causer_email"></p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border border-opacity-20" :class="log.badge_class" x-text="log.description"></span>
                </div>

                {{-- Subject Info --}}
                <div x-show="showCols.subject" class="bg-page-bg/30 p-2 rounded border border-border-color flex justify-between items-center">
                    <span class="text-xs font-bold text-primary" x-text="log.subject_type"></span>
                    <span class="text-[10px] font-mono text-secondary bg-white dark:bg-gray-800 px-1.5 rounded border border-border-color" x-text="'ID: ' + log.subject_id"></span>
                </div>

                {{-- Changes --}}
                <div x-show="showCols.changes" class="text-xs text-text-color bg-page-bg/50 rounded-lg p-2 border border-border-color max-h-32 overflow-y-auto custom-scrollbar" x-html="log.changes_html"></div>

                {{-- Footer: Date & Actions --}}
                <div class="flex items-center justify-between mt-1 pt-2 border-t border-dashed border-border-color">
                    <div class="text-xs text-secondary flex items-center gap-1" x-show="showCols.date">
                        <i class="ri-time-line"></i> <span x-text="log.created_at_ago"></span>
                    </div>
                    
                    @can('activity-delete')
                    <div class="flex gap-2 relative z-30">
                        <button type="button" @click="confirmDelete(log.id)" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 active:scale-95 transition-transform cursor-pointer"><i class="ri-delete-bin-line"></i></button>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </template>

    <div x-show="!isLoading && logs.length === 0" class="text-center py-10 text-secondary bg-card-bg rounded-xl border border-dashed border-border-color">
        <i class="ri-ghost-line text-4xl mb-2 inline-block opacity-50"></i>
        <p>{{ __('messages.no_logs_found') }}</p>
    </div>
</div>