@extends('admin.dashboard')

<style>
    /* សម្រាប់ Mobile (Default) */
    #actionBar {
        left: 1.5rem;
        /* left-6 */
        right: 1.5rem;
        /* right-6 */
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        /* Animation រលូន */
    }

    /* សម្រាប់ Desktop */
    @media (min-width: 768px) {

        /* ពេល Sidebar បើកធម្មតា (w-72 = 18rem, ទុកចន្លោះ 1rem = 19rem) */
        #actionBar {
            left: 19rem;
            right: 2.5rem;
        }

        /* ពេល Sidebar បង្រួម (Collapsed) (w-20 = 5rem, ទុកចន្លោះ 1rem = 6rem) */
        body.collapsed #actionBar {
            left: 6rem;
        }
    }
</style>

@section('content')
    <div class="max-w-5xl mx-auto pb-32" x-data="{ activeTab: $store.theme.darkMode ? 'dark' : 'light' }"
        x-effect="activeTab = $store.theme.darkMode ? 'dark' : 'light'">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Theme Customizer</h1>
                <p class="text-gray-500 mt-2">Manage your color palette for both Light and Dark modes.</p>
            </div>

            <div class="bg-gray-100 dark:bg-gray-800 p-1.5 rounded-xl flex shadow-inner">
                <button @click="$store.theme.setMode('light')"
                    :class="activeTab === 'light' ? 'bg-white text-blue-600 shadow-sm' :
                        'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                    <i class="ri-sun-line text-lg"></i> Light
                </button>

                <button @click="$store.theme.setMode('dark')"
                    :class="activeTab === 'dark' ? 'bg-gray-700 text-blue-400 shadow-sm' :
                        'text-gray-500 hover:text-gray-300'"
                    class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all flex items-center gap-2">
                    <i class="ri-moon-line text-lg"></i> Dark
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
                <h3
                    class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
                    <span class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <i class="ri-flag-fill"></i>
                    </span>
                    Brand Identity
                </h3>

                @include('components.color-input', ['label' => 'Primary Background', 'key' => 'primary'])
                @include('components.color-input', [
                    'label' => 'Primary Text Color',
                    'key' => 'primaryText',
                ])
                @include('components.color-input', ['label' => 'General Text Color', 'key' => 'textColor'])
                @include('components.color-input', ['label' => 'Secondary Color', 'key' => 'secondary'])
            </div>

            <div class="bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
                <h3
                    class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
                    <span class="p-2 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                        <i class="ri-layout-masonry-fill"></i>
                    </span>
                    Layout Structure
                </h3>
                @include('components.color-input', ['label' => 'Sidebar Background', 'key' => 'sidebarBg'])
                @include('components.color-input', ['label' => 'Sidebar Text', 'key' => 'sidebarText'])

                <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-3">Sidebar Hover State</p>
                    @include('components.color-input', [
                        'label' => 'Hover Background',
                        'key' => 'sidebarHoverBg',
                    ])
                    @include('components.color-input', [
                        'label' => 'Hover Text',
                        'key' => 'sidebarHoverText',
                    ])
                </div>

                @include('components.color-input', ['label' => 'Header Background', 'key' => 'headerBg'])
            </div>

            <div
                class="md:col-span-2 bg-card-bg border border-border-color rounded-2xl p-6 shadow-custom transition-all duration-300">
                <h3
                    class="font-bold text-lg mb-6 flex items-center gap-3 text-gray-800 dark:text-white pb-4 border-b border-border-color">
                    <span class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                        <i class="ri-file-list-fill"></i>
                    </span>
                    Content & Forms
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    @include('components.color-input', ['label' => 'Page Background', 'key' => 'pageBg'])
                    @include('components.color-input', ['label' => 'Card Background', 'key' => 'cardBg'])

                    <div
                        class="col-span-1 md:col-span-2 mt-2 mb-2 pt-2 border-t border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-3">Form Inputs</p>
                    </div>

                    @include('components.color-input', [
                        'label' => 'Input Background',
                        'key' => 'inputBg',
                    ])

                    @include('components.color-input', [
                        'label' => 'Input Border',
                        'key' => 'inputBorder',
                    ])

                    @include('components.color-input', ['label' => 'Layout Borders', 'key' => 'border'])
                </div>
            </div>

        </div>

        <div id="actionBar"
            class="fixed bottom-6 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 p-4 rounded-2xl shadow-2xl z-40 flex items-center justify-between transform hover:scale-[1.005] transition-transform duration-300">

            {{-- ផ្នែកខាងឆ្វេង (ទុកចន្លោះ ឬដាក់ព័ត៌មាន) --}}
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <i class="ri-information-line text-lg text-blue-500"></i>
                    <span x-show="!$store.theme.isSaving">Ready to update</span>
                    <span x-show="$store.theme.isSaving" class="text-blue-500 font-medium">Processing...</span>
                </div>
            </div>

            {{-- ផ្នែកប៊ូតុងខាងស្តាំ --}}
            <div class="flex gap-3 w-full sm:w-auto justify-end">

                <button type="button" @click="$store.theme.reset()"
                    class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 group">
                    <i
                        class="ri-restart-line group-hover:-rotate-180 transition-transform duration-500 text-gray-500 dark:text-gray-400"></i>
                    <span>Reset</span>
                </button>

                <button type="button" @click="$store.theme.save()" :disabled="$store.theme.isSaving"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold shadow-lg shadow-blue-500/30 flex items-center gap-2 transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed"
                    :class="$store.theme.isSaving ? 'scale-100' : 'hover:scale-105 hover:shadow-blue-500/50'">

                    <i x-show="!$store.theme.isSaving" class="ri-save-3-fill text-lg"></i>

                    <svg x-show="$store.theme.isSaving" class="animate-spin h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>

                    <span x-text="$store.theme.isSaving ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </div>

    </div>
@endsection
