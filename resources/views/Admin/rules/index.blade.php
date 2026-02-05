@extends('admin.dashboard')

@section('content')

<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4">
    
    {{-- 1. HEADER --}}
    @include('Admin.rules.partials.header')

    {{-- 2. DESKTOP VIEW (TABLE) --}}
    <div class="hidden md:block">
        @include('Admin.rules.partials.table')
    </div>

    {{-- 3. MOBILE VIEW (CARDS) --}}
    <div class="md:hidden">
        @include('Admin.rules.partials.mobile_card')
    </div>

</div>
@endsection