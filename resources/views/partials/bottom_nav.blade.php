<nav class="md:hidden fixed bottom-0 left-0 right-0 z-[100] h-16 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] transition-colors duration-300"
     style="background-color: rgb(var(--sidebar-bg)); border-top: 1px solid rgb(var(--custom-border));">
    
    {{-- ប្រើ Flex ដើម្បីតម្រឹមស្វ័យប្រវត្តិ ទោះបីបាត់ប៊ូតុងខ្លះដោយសារ Permission ក៏ដោយ --}}
    <div class="flex items-center justify-around h-full max-w-lg mx-auto font-medium">
        
        {{-- 1. Dashboard (Home) --}}
        @can('view_dashboard')
        <a href="{{ route('admin.dashboard') }}" 
           class="flex-1 inline-flex flex-col items-center justify-center h-full px-2 group hover:bg-[rgb(var(--sidebar-hover-bg))]/10 transition-colors">
            <i class="ri-dashboard-line text-2xl mb-1 {{ request()->routeIs('admin.dashboard') ? 'text-[rgb(var(--color-primary))]' : 'text-[rgb(var(--sidebar-text))]' }}"></i>
            <span class="text-[10px] {{ request()->routeIs('admin.dashboard') ? 'text-[rgb(var(--color-primary))]' : 'text-[rgb(var(--sidebar-text))]' }}">
                {{ __('messages.dashboard') }}
            </span>
        </a>
        @endcan

        {{-- 2. Menu / Products --}}
        @can('view_products')
        {{-- ចំណាំ៖ សូមដាក់ route ឲ្យត្រូវនឹងរបស់អ្នក ឧទាហរណ៍ admin.products.index --}}
        <a href="{{ route('admin.dashboard') }}" 
           class="flex-1 inline-flex flex-col items-center justify-center h-full px-2 group hover:bg-[rgb(var(--sidebar-hover-bg))]/10 transition-colors">
            <i class="ri-list-check text-2xl mb-1 text-[rgb(var(--sidebar-text))]"></i>
            <span class="text-[10px] text-[rgb(var(--sidebar-text))]">
                {{ __('messages.menu') }}
            </span>
        </a>
        @endcan

        {{-- 3. Center Action (Sale/POS) --}}
        @can('create_sales')
        <a href="#" 
           class="flex-1 inline-flex flex-col items-center justify-center h-full px-2 group relative">
            <div class="absolute bottom-4 p-3 rounded-full shadow-lg border-4 transition-transform group-hover:scale-110" 
                 style="background-color: rgb(var(--color-primary)); border-color: rgb(var(--page-bg)); color: rgb(var(--color-primary-text));">
                <i class="ri-add-line text-2xl font-bold"></i>
            </div>
            {{-- បន្ថែម mt-8 ដើម្បីកុំឲ្យអក្សរបាំង Icon ដែលផុសឡើង --}}
            <span class="text-[10px] mt-8 text-[rgb(var(--sidebar-text))]">
                {{ __('messages.sale') }}
            </span>
        </a>
        @endcan

        {{-- 4. Report --}}
        @can('view_reports')
        <a href="#" 
           class="flex-1 inline-flex flex-col items-center justify-center h-full px-2 group hover:bg-[rgb(var(--sidebar-hover-bg))]/10 transition-colors">
            <i class="ri-file-chart-line text-2xl mb-1 text-[rgb(var(--sidebar-text))]"></i>
            <span class="text-[10px] text-[rgb(var(--sidebar-text))]">
                {{ __('messages.report') }}
            </span>
        </a>
        @endcan

        {{-- 5. Account / Profile --}}
        @can('view_profile')
        <a href="#" 
           class="flex-1 inline-flex flex-col items-center justify-center h-full px-2 group hover:bg-[rgb(var(--sidebar-hover-bg))]/10 transition-colors">
            <i class="ri-user-line text-2xl mb-1 text-[rgb(var(--sidebar-text))]"></i>
            <span class="text-[10px] text-[rgb(var(--sidebar-text))]">
                {{ __('messages.account') }}
            </span>
        </a>
        @endcan

    </div>
</nav>