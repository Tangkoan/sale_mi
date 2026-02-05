<div class="bg-card-bg rounded-xl shadow-custom border border-border-color p-4 md:p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($permissions as $groupName => $perms)
        <div class="border border-border-color rounded-xl overflow-hidden shadow-sm h-full flex flex-col">
            
            {{-- Group Header --}}
            <div class="bg-page-bg/50 px-4 py-3 border-b border-border-color flex justify-between items-center">
                <span class="font-bold text-text-color capitalize flex items-center gap-2">
                    <i class="ri-folder-shield-2-line text-primary"></i> {{ $groupName }}
                </span>
                <span class="text-[10px] bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded-full text-secondary font-bold">
                    {{ count($perms) }}
                </span>
            </div>

            {{-- Checkboxes --}}
            <div class="p-4 space-y-3 bg-white dark:bg-gray-800 flex-1">
                @foreach($perms as $perm)
                <label class="flex items-center space-x-3 cursor-pointer select-none group p-2 rounded-lg hover:bg-page-bg transition-colors border border-transparent hover:border-input-border">
                    <div class="relative flex items-center">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                            {{ $role->assignablePermissions->contains($perm->id) ? 'checked' : '' }}
                            class="peer w-5 h-5 cursor-pointer appearance-none rounded border border-input-border checked:bg-primary checked:border-primary transition-all bg-card-bg">
                        <i class="ri-check-line absolute text-white text-sm opacity-0 peer-checked:opacity-100 pointer-events-none left-[2px]"></i>
                    </div>
                    <span class="text-sm text-text-color group-hover:text-primary transition-colors font-medium">
                        {{ $perm->name }}
                    </span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>