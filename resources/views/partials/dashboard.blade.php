@extends('layouts.master')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container mx-auto">
        <div class="bg-white p-8 rounded-lg shadow-lg border-l-4 border-green-500">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 p-3 rounded-full text-green-600 mr-4">
                    <i class="ri-checkbox-circle-line text-3xl"></i>
                </div>
                <h2 class="text-2xl text-gray-800 font-bold">Login ជោគជ័យ!</h2>
            </div>
            
            <div class="border-t pt-4">
                <p class="text-gray-700 text-lg">
                    សូមស្វាគមន៍, <span class="font-bold text-gray-900">{{ Auth::user()->name }}</span>
                </p>
                <p class="text-gray-500 mt-2 flex items-center">
                    <i class="ri-mail-line mr-2"></i> {{ Auth::user()->email }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="bg-white p-6 rounded shadow">Box 1</div>
            <div class="bg-white p-6 rounded shadow">Box 2</div>
            <div class="bg-white p-6 rounded shadow">Box 3</div>
        </div>
    </div>
@endsection