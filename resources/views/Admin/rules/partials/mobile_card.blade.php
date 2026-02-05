<div class="flex flex-col gap-3">
    @forelse($roles as $role)
    <div class="bg-card-bg p-4 rounded-2xl shadow-sm border border-border-color relative overflow-hidden transition-all duration-200">
        
        <div class="flex justify-between items-start mb-3">
            <div>
                <h3 class="font-bold text-text-color text-lg">{{ $role->name }}</h3>
                <p class="text-xs text-secondary mt-1">Role Level: {{ $role->level ?? 'N/A' }}</p>
            </div>
            
            @if($role->assignable_permissions_count > 0)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 border border-green-200 uppercase">
                    {{ $role->assignable_permissions_count }} PERMS
                </span>
            @else
                <span class="text-[10px] text-gray-400 italic bg-gray-100 px-2 py-0.5 rounded border border-gray-200">None</span>
            @endif
        </div>

        <div class="pt-3 border-t border-dashed border-border-color flex justify-end">
            @can('rule-edit')
                <a href="{{ route('admin.rules.edit', $role->id) }}" 
                   class="w-full flex items-center justify-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 py-2 rounded-lg font-bold transition-all">
                    <i class="ri-settings-4-line"></i> {{ __('messages.btn_configure') }}
                </a>
            @else
                <button disabled class="w-full flex items-center justify-center gap-2 bg-gray-100 text-gray-400 border border-gray-200 py-2 rounded-lg font-bold cursor-not-allowed">
                    <i class="ri-settings-4-line"></i> {{ __('messages.btn_configure') }}
                </button>
            @endcan
        </div>

    </div>
    @empty
    <div class="text-center py-10 text-secondary bg-card-bg rounded-xl border border-dashed border-border-color">
        <i class="ri-file-search-line text-4xl mb-2 inline-block opacity-50"></i>
        <p>{{ __('messages.no_roles_found_except_admin') }}</p>
    </div>
    @endforelse
</div>