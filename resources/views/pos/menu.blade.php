@extends('layouts.blank')

@section('content')
<div class="h-screen w-full bg-[#F6F8FC] dark:bg-[#0f172a] flex flex-col relative overflow-hidden font-sans" x-data="posMenu()" x-cloak>
    
    {{-- 1. HEADER --}}
    @include('pos.menu.header')

    {{-- 2. PRODUCT GRID --}}
    @include('pos.menu.product-grid')

    {{-- 3. FLOATING CART BUTTON --}}
    @include('pos.menu.cart-button')

    {{-- 4. PRODUCT MODAL --}}
    @include('pos.menu.product-modal')

    {{-- 5. CART MODAL --}}
    @include('pos.menu.cart-modal')

</div>

{{-- SCRIPTS --}}
@include('pos.menu.scripts')

@endsection