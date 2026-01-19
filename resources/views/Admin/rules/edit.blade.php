@extends('admin.dashboard')

@section('content')
<div class="w-full h-full px-1 py-1">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-text-color flex items-center gap-2">
                <i class="ri-settings-4-line text-primary"></i> {{ __('messages.configure_rules') }}: <span class="text-primary">{{ $role->name }}</span>
            </h1>
            {{-- ប្រើ {!! !!} ដើម្បីអនុញ្ញាតឱ្យប្រើ Tag <strong> នៅក្នុងអក្សរបាន --}}
            <p class="text-sm text-secondary mt-1">
                {!! __('messages.rule_configure_desc', ['name' => '<strong>' . $role->name . '</strong>']) !!}
            </p>
        </div>
        <a href="{{ route('admin.rules.index') }}" class="text-secondary hover:text-text-color flex items-center gap-1">
            <i class="ri-arrow-left-line"></i> {{ __('messages.btn_back') }}
        </a>
    </div>

    <form action="{{ route('admin.rules.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="bg-card-bg rounded-xl shadow-custom border border-border-color p-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($permissions as $groupName => $perms)
                <div class="border border-border-color rounded-xl overflow-hidden">
                    <div class="bg-page-bg/50 px-4 py-2 border-b border-border-color font-bold text-text-color capitalize">
                        {{ $groupName }}
                    </div>
                    <div class="p-4 space-y-3 bg-input-bg">
                        @foreach($perms as $perm)
                        <label class="flex items-center space-x-3 cursor-pointer select-none">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                    {{ $role->assignablePermissions->contains($perm->id) ? 'checked' : '' }}
                                    class="peer w-5 h-5 cursor-pointer appearance-none rounded border border-input-border checked:bg-primary checked:border-primary transition-all bg-card-bg">
                                <i class="ri-check-line absolute text-white text-sm opacity-0 peer-checked:opacity-100 pointer-events-none left-[2px]"></i>
                            </div>
                            <span class="text-sm text-text-color">{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-border-color pt-6">
                <a href="{{ route('admin.rules.index') }}" class="px-6 py-2.5 rounded-xl border border-input-border text-text-color hover:bg-page-bg transition">{{ __('messages.cancel') }}</a>
                <button type="submit" class="bg-primary text-white px-8 py-2.5 rounded-xl hover:opacity-90 transition shadow-lg shadow-primary/30 font-bold">
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection