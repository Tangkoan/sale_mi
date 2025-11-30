@extends('admin.dashboard')

@section('content')
<div class="max-w-5xl mx-auto pb-32" 
     x-data="{ activeTab: $store.theme.darkMode ? 'dark' : 'light' }"
     x-effect="activeTab = $store.theme.darkMode ? 'dark' : 'light'">

    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Theme Customizer</h1>
            <p class="text-gray-500 mt-2">Manage your color palette for both Light and Dark modes.</p>
        </div>
        
        <div class="bg-gray-100 dark:bg-gray-800 p-1.5 rounded-xl flex shadow-inner">
            <button @click="$store.theme.setMode('light')" 
                :class="activeTab === 'light' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                <i class="ri-sun-line text-lg"></i> Light
            </button>

            <button @click="$store.theme.setMode('dark')" 
                :class="activeTab === 'dark' ? 'bg-gray-700 text-blue-400 shadow-sm' : 'text-gray-500 hover:text-gray-300'"
                class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                <i class="ri-moon-line text-lg"></i> Dark
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
            <h3 class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
                <span class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <i class="ri-flag-fill"></i>
                </span>
                Brand Identity
            </h3>
            
            @include('components.color-input', ['label' => 'Primary Background', 'key' => 'primary'])
            @include('components.color-input', ['label' => 'Primary Text Color', 'key' => 'primaryText'])
            @include('components.color-input', ['label' => 'Secondary Color', 'key' => 'secondary'])
        </div>

        <div class="bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
            <h3 class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
                <span class="p-2 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <i class="ri-layout-masonry-fill"></i>
                </span>
                Layout Structure
            </h3>
            @include('components.color-input', ['label' => 'Sidebar Background', 'key' => 'sidebarBg'])
            @include('components.color-input', ['label' => 'Sidebar Text', 'key' => 'sidebarText'])
            
            <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                <p class="text-xs font-bold text-gray-400 uppercase mb-3">Sidebar Hover State</p>
                @include('components.color-input', ['label' => 'Hover Background', 'key' => 'sidebarHoverBg'])
                @include('components.color-input', ['label' => 'Hover Text', 'key' => 'sidebarHoverText'])
            </div>

            @include('components.color-input', ['label' => 'Header Background', 'key' => 'headerBg'])
        </div>

        <div class="md:col-span-2 bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
            <h3 class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
                <span class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                    <i class="ri-file-list-fill"></i>
                </span>
                Content & Forms
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                @include('components.color-input', ['label' => 'Page Background', 'key' => 'pageBg'])
                @include('components.color-input', ['label' => 'Card Background', 'key' => 'cardBg'])
                @include('components.color-input', ['label' => 'Input/Form Fill', 'key' => 'inputBg'])
                @include('components.color-input', ['label' => 'Borders', 'key' => 'border'])
            </div>
        </div>

    </div>

    <div class="fixed bottom-6 left-6 right-6 md:left-80 md:right-10 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 p-4 rounded-2xl shadow-2xl z-50 flex items-center justify-between transform transition-all hover:scale-[1.01]">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                <i class="ri-information-line text-xl"></i>
            </div>
            <div class="text-sm">
                <p class="font-bold text-gray-800 dark:text-white" x-text="$store.theme.isSaving ? 'Saving changes...' : 'Ready to save'">Ready to save</p>
                <p class="text-gray-500 text-xs">Changes apply instantly as preview.</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button @click="$store.theme.reset()" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-bold hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                Reset
            </button>
            <button @click="$store.theme.save()" :disabled="$store.theme.isSaving" :class="$store.theme.isSaving ? 'opacity-75 cursor-not-allowed' : 'hover:shadow-lg hover:-translate-y-0.5'" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold shadow-md transition-all flex items-center gap-2">
                <i x-show="!$store.theme.isSaving" class="ri-save-3-fill text-lg"></i>
                <svg x-show="$store.theme.isSaving" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="$store.theme.isSaving ? 'Saving...' : 'Save Changes'"></span>
            </button>
        </div>
    </div>

</div>
@endsection