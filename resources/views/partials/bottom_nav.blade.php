<nav class="md:hidden fixed bottom-0 left-0 right-0 z-[100] h-16 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] transition-colors duration-300"
     style="background-color: rgb(var(--sidebar-bg)); border-top: 1px solid rgb(var(--custom-border));">
    
    <div class="grid h-full max-w-lg grid-cols-5 mx-auto font-medium">
        
        {{-- 1. Dashboard --}}
        <a href="{{ route('admin.dashboard') }}" 
           class="inline-flex flex-col items-center justify-center px-5 group hover:bg-[rgb(var(--sidebar-hover-bg))]/10 transition-colors">
            <i class="ri-dashboard-line text-2xl mb-1 {{ request()->routeIs('admin.dashboard') ? 'text-[rgb(var(--color-primary))]' : 'text-[rgb(var(--sidebar-text))]' }}"></i>
            <span class="text-[10px] {{ request()->routeIs('admin.dashboard') ? 'text-[rgb(var(--color-primary))]' : 'text-[rgb(var(--sidebar-text))]' }}">
                Home
            </span>
        </a>

        {{-- 2. Menu --}}
        <a href="#" 
           class="inline-flex flex-col items-center justify-center px-5 group hover:bg-[rgb(var(--sidebar-hover-bg))]/10 transition-colors">
            <i class="ri-list-check text-2xl mb-1 text-[rgb(var(--sidebar-text))]"></i>
            <span class="text-[10px] text-[rgb(var(--sidebar-text))]">
                Menu
            </span>
        </a>

        {{-- 3. Center Action (Add) --}}
        <a href="#" 
           class="inline-flex flex-col items-center justify-center px-5 group relative">
            <div class="absolute bottom-4 p-3 rounded-full shadow-lg border-4 transition-transform group-hover:scale-110" 
                 style="background-color: rgb(var(--color-primary)); border-color: rgb(var(--page-bg)); color: rgb(var(--color-primary-text));">
                <i class="ri-add-line text-2xl font-bold"></i>
            </div>
            <span class="text-[10px] mt-8 text-[rgb(var(--sidebar-text))]">Add</span>
        </a>

        {{-- 4. Report --}}
        <a href="#" 
           class="inline-flex flex-col items-center justify-center px-5 group hover:bg-[rgb(var(--sidebar-hover-bg))]/10 transition-colors">
            <i class="ri-file-chart-line text-2xl mb-1 text-[rgb(var(--sidebar-text))]"></i>
            <span class="text-[10px] text-[rgb(var(--sidebar-text))]">
                Report
            </span>
        </a>

        {{-- 5. Account --}}
        <a href="#" 
           class="inline-flex flex-col items-center justify-center px-5 group hover:bg-[rgb(var(--sidebar-hover-bg))]/10 transition-colors">
            <i class="ri-user-line text-2xl mb-1 text-[rgb(var(--sidebar-text))]"></i>
            <span class="text-[10px] text-[rgb(var(--sidebar-text))]">
                Account
            </span>
        </a>

    </div>
</nav>