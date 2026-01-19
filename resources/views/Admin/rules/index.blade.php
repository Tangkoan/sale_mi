@extends('admin.dashboard')

@section('content')
<div class="w-full h-full px-1 py-1">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                {{-- <i class="ri-git-merge-line text-primary"></i> --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-line-squiggle-icon lucide-line-squiggle"><path d="M7 3.5c5-2 7 2.5 3 4C1.5 10 2 15 5 16c5 2 9-10 14-7s.5 13.5-4 12c-5-2.5.5-11 6-2"/></svg>
                {{ __('messages.assignment_rules') }}
            </h1>
           
        </div>
    </div>

    <div class="bg-card-bg rounded-xl shadow-custom border border-border-color overflow-hidden">
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
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('messages.can_assign_count', ['count' => $role->assignable_permissions_count]) }}
                            </span>
                        @else
                            <span class="text-xs text-secondary italic">{{ __('messages.cannot_assign_any') }}</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-right">
                        {{-- ករណីមានសិទ្ធិ (Show Link) --}}
                        @can('rule-edit')
                            <a href="{{ route('admin.rules.edit', $role->id) }}" 
                            class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 px-4 py-2 rounded-lg font-medium transition-all shadow-sm">
                                <i class="ri-settings-4-line"></i> {{ __('messages.btn_configure') }}
                            </a>
                        @endcan

                        {{-- ករណីគ្មានសិទ្ធិ (Show Disabled Button) --}}
                        @cannot('rule-edit')
                            <button disabled
                            class="inline-flex items-center gap-2 bg-gray-100 text-gray-400 border border-gray-200 px-4 py-2 rounded-lg font-medium cursor-not-allowed shadow-sm">
                                <i class="ri-settings-4-line"></i> {{ __('messages.btn_configure') }}
                            </button>
                        @endcannot
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center text-secondary">{{ __('messages.no_roles_found_except_admin') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection