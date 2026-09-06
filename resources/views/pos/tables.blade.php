@extends('layouts.blank')
@section('title', __('messages.pos_management'))
@section('content')
<div class="h-screen w-full bg-[#F6F8FC] dark:bg-[#0f172a] flex flex-col font-sans relative overflow-hidden" x-data="posTables()">
    
    @include('pos.table.header')
    
    <div x-show="isLoading && tables.length === 0" class="flex-1 flex flex-col items-center justify-center text-gray-400">
        <i class="ri-loader-4-line text-5xl animate-spin mb-4 text-primary"></i>
    </div>

    @include('pos.table.grid')
    @include('pos.table.checkout-modal')
    
    {{-- បន្ថែម ២ នេះ --}}
    @include('pos.table.exchange-list-modal')
    @include('pos.table.exchange-modal')

    @include('pos.table.receipt')
</div>
@include('pos.table.scripts')
@include('pos.table.styles')
@endsection