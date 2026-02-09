@extends('admin.dashboard')

@section('title', __('messages.theme_management'))

<style>
    /* Mobile (Default) */
    #actionBar { left: 1.5rem; right: 1.5rem; transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    /* Desktop */
    @media (min-width: 768px) {
        #actionBar { left: 19rem; right: 2.5rem; }
        body.collapsed #actionBar { left: 6rem; }
    }
</style>

@section('content')
<div class="max-w-5xl mx-auto pb-32" x-data="{ activeTab: $store.theme.darkMode ? 'dark' : 'light' }"
    x-effect="activeTab = $store.theme.darkMode ? 'dark' : 'light'">

    {{-- 1. HEADER --}}
    @include('Admin.theme.header')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- 2. BRAND IDENTITY --}}
        @include('Admin.theme.brand_identity')

        {{-- 3. LAYOUT STRUCTURE --}}
        @include('Admin.theme.layout_structure')

        {{-- 4. CONTENT & FORMS --}}
        @include('Admin.theme.content_forms')

    </div>

    {{-- 5. ACTION BAR (FLOATING FOOTER) --}}
    @include('Admin.theme.action_bar')

</div>
@endsection