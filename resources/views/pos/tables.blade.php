@extends('layouts.blank')

@section('title', 'POS Management')

@section('content')
<div class="h-screen w-full bg-[#F6F8FC] dark:bg-[#0f172a] flex flex-col font-sans relative overflow-hidden" x-data="posTables()">
    
    {{-- 1. HEADER --}}
    @include('pos.table.header')
    

    {{-- LOADING STATE --}}
    <div x-show="isLoading && tables.length === 0" class="flex-1 flex flex-col items-center justify-center text-gray-400">
        <i class="ri-loader-4-line text-5xl animate-spin mb-4 text-primary"></i>
        <p>Loading...</p>
    </div>

    {{-- 2. TABLES GRID --}}
    @include('pos.table.grid')

    {{-- 3. CHECKOUT MODAL --}}
    @include('pos.table.checkout-modal')

    {{-- 4. HIDDEN RECEIPT --}}
    @include('pos.table.receipt')

</div>

{{-- SCRIPTS & STYLES --}}
@include('pos.table.scripts')
@include('pos.table.styles')

@endsection