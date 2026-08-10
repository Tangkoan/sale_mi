@extends('admin.dashboard')

@section('title', __('messages.shop_management'))


@section('content')

{{-- Error Alert --}}
@if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
        <strong class="font-bold"><i class="ri-error-warning-line mr-1"></i> Error!</strong>
        <ul class="mt-1 list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="min-h-screen py-8 bg-page-bg" x-data="shopForm()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <form action="{{ route('admin.shop_info.save') }}" method="POST" enctype="multipart/form-data" class="relative">
            @csrf

            {{-- 1. HEADER & ACTIONS --}}
            @include('admin.shop_info.partials.header')

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                
                {{-- LEFT COLUMN (English & Khmer Details) --}}
                <div class="xl:col-span-8 space-y-8">
                    @include('admin.shop_info.partials.english_details')
                    @include('admin.shop_info.partials.khmer_details')
                </div>

                {{-- RIGHT COLUMN (Settings & Branding) --}}
                <div class="xl:col-span-4 space-y-8">
                    @include('admin.shop_info.partials.general_settings')
                    @include('admin.shop_info.partials.branding')
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    function shopForm() {
        return {
            logoPreview: '{{ $shop->logo ? asset("storage/".$shop->logo) : null }}',
            favPreview: '{{ $shop->fav ? asset("storage/".$shop->fav) : null }}',

            updatePreview(event, previewName) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => { this[previewName] = e.target.result; };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endsection