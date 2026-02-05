<div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-page-bg/50 border-b border-border-color text-text-color text-sm uppercase tracking-wider">
                    <th class="px-6 py-4 font-bold">{{ __('messages.th_role_name') }}</th>
                    <th class="px-6 py-4 font-bold text-center">{{ __('messages.th_assignable_permissions') }}</th>
                    <th class="px-6 py-4 font-bold text-right">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                @forelse($roles as $role)
                <tr class="hover:bg-page-bg/30 transition-colors">
                    
                    <td class="px-6 py-4">
                        <span class="font-bold text-text-color text-lg">{{ $role->name }}</span>
                    </td>

                    <td class="px-6 py-4 text-center">
                        @if($role->assignable_permissions_count > 0)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                {{ __('messages.can_assign_count', ['count' => $role->assignable_permissions_count]) }}
                            </span>
                        @else
                            <span class="text-xs text-secondary italic bg-gray-100 px-2 py-1 rounded">{{ __('messages.cannot_assign_any') }}</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-right">
                        @can('rule-edit')
                            <a href="{{ route('admin.rules.edit', $role->id) }}" 
                               class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 px-4 py-2 rounded-lg font-bold transition-all shadow-sm">
                                <i class="ri-settings-4-line"></i> {{ __('messages.btn_configure') }}
                            </a>
                        @else
                            <button disabled class="inline-flex items-center gap-2 bg-gray-100 text-gray-400 border border-gray-200 px-4 py-2 rounded-lg font-bold cursor-not-allowed shadow-sm">
                                <i class="ri-settings-4-line"></i> {{ __('messages.btn_configure') }}
                            </button>
                        @endcan
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center text-secondary">
                        <i class="ri-file-search-line text-4xl mb-2 inline-block opacity-50"></i>
                        <p>{{ __('messages.no_roles_found_except_admin') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>