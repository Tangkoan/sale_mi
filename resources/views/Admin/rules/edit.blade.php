@extends('admin.dashboard')


@section('title', __('messages.rule_management'))

@section('content')
<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4">
    
    {{-- 1. FORM HEADER --}}
    @include('admin.rules.partials.form_header')

    {{-- 2. FORM BODY --}}
    <form action="{{ route('admin.rules.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        @include('admin.rules.partials.permission_grid')

        {{-- 3. FORM FOOTER (ACTIONS) --}}
        <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-border-color bg-card-bg p-4 rounded-xl shadow-sm border md:border-t-0 md:bg-transparent md:shadow-none md:p-0">
            <a href="{{ route('admin.rules.index') }}" class="px-6 py-2.5 rounded-xl border border-input-border text-text-color bg-white dark:bg-gray-800 hover:bg-page-bg transition font-medium">
                {{ __('messages.cancel') }}
            </a>
            <button type="submit" class="bg-primary text-white px-8 py-2.5 rounded-xl hover:opacity-90 transition shadow-lg shadow-primary/30 font-bold flex items-center gap-2">
                <i class="ri-save-line"></i> {{ __('messages.save') }}
            </button>
        </div>
    </form>

</div>
@endsection