@extends('admin.dashboard')

@section('title', __('messages.sale_report'))

@section('content')
<div class="w-full h-full px-2 py-2 sm:px-4 sm:py-4" x-data="productManagement()">
    
    {{-- 1. Filters --}}
    @include('admin.report.sale_report.partials.filters')

    {{-- 2. Summary Cards --}}
    @include('admin.report.sale_report.partials.summary-cards')

    {{-- 3. Data Table/Cards --}}
    @include('admin.report.sale_report.partials.table')

    {{-- Include Modal Here --}}
    @include('admin.report.sale_report.partials.modal-detail')

</div>

{{-- 4. Scripts --}}
@include('admin.report.sale_report.scripts')

@endsection