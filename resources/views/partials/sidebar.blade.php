<aside id="sidebar" class="bg-sidebar-bg text-sidebar-text w-72 flex flex-col h-screen flex-shrink-0 z-50 relative border-r border-custom-border transition-colors duration-300">
    
    <div class="h-20 flex items-center justify-center bg-sidebar-bg sticky top-0 z-20 border-b border-custom-border">
        <div class="flex items-center gap-3 px-6 w-full">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                <i class="ri-store-2-fill text-xl"></i>
            </div>
            <div class="flex flex-col overflow-hidden">
                <span class="text-lg font-bold tracking-tight">Ice Cream</span>
                <span class="text-[10px] font-semibold text-primary uppercase tracking-widest">Admin Panel</span>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto no-scrollbar py-6 px-4 space-y-1">

        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group mb-6
                  {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-sidebar-text hover:bg-black/5 dark:hover:bg-white/5' }}">
            <i class="ri-dashboard-line text-xl mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-primary' }}"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        @if(auth()->user()->canany(['user-list', 'role-list', 'permission-list']) || auth()->user()->hasRole('Super Admin'))
            
            <div class="px-4 mt-6 mb-2">
                <span class="text-[11px] font-bold opacity-50 uppercase tracking-wider">Management</span>
            </div>

            @php 
                $isActiveUserMgmt = request()->routeIs('user.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*'); 
            @endphp
            
            <div x-data="{ open: {{ $isActiveUserMgmt ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 cursor-pointer 
                               {{ $isActiveUserMgmt ? 'bg-black/5 dark:bg-white/5' : 'hover:bg-black/5 dark:hover:bg-white/5' }}">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 {{ $isActiveUserMgmt ? 'bg-primary/10 text-primary' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">
                            <i class="ri-user-settings-line text-lg"></i>
                        </div>
                        <span class="font-medium">Users & Access</span>
                    </div>
                    <i class="ri-arrow-down-s-line transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-collapse class="pl-4 mt-1 space-y-1">
                    
                    @can('user-list')
                    <a href="{{ route('user.list') }}" 
                       class="flex items-center px-4 py-2.5 rounded-lg text-sm transition-all duration-200 relative group/item
                              {{ request()->routeIs('user.list') ? 'text-primary font-bold bg-primary/5' : 'text-gray-500 hover:text-sidebar-text' }}">
                        <span class="absolute left-0 w-1 h-1 rounded-full bg-gray-300 group-hover/item:bg-primary transition-colors {{ request()->routeIs('user.list') ? 'bg-primary h-4 rounded-r-full' : '' }}"></span>
                        <span class="ml-4">User List</span>
                    </a>
                    @endcan

                    @can('role-list')
                    <a href="{{ route('admin.roles.index') }}" 
                       class="flex items-center px-4 py-2.5 rounded-lg text-sm transition-all duration-200 relative group/item
                              {{ request()->routeIs('admin.roles.*') ? 'text-primary font-bold bg-primary/5' : 'text-gray-500 hover:text-sidebar-text' }}">
                        <span class="absolute left-0 w-1 h-1 rounded-full bg-gray-300 group-hover/item:bg-primary transition-colors {{ request()->routeIs('admin.roles.*') ? 'bg-primary h-4 rounded-r-full' : '' }}"></span>
                        <span class="ml-4">Roles & Permissions</span>
                    </a>
                    @endcan

                    @can('permission-list')
                    <a href="{{ route('admin.permissions.index') }}" 
                       class="flex items-center px-4 py-2.5 rounded-lg text-sm transition-all duration-200 relative group/item
                              {{ request()->routeIs('admin.permissions.*') ? 'text-primary font-bold bg-primary/5' : 'text-gray-500 hover:text-sidebar-text' }}">
                        <span class="absolute left-0 w-1 h-1 rounded-full bg-gray-300 group-hover/item:bg-primary transition-colors {{ request()->routeIs('admin.permissions.*') ? 'bg-primary h-4 rounded-r-full' : '' }}"></span>
                        <span class="ml-4">Permissions List</span>
                    </a>
                    @endcan

                </div>
            </div>
        @endif

        <div class="px-4 mt-6 mb-2">
            <span class="text-[11px] font-bold opacity-50 uppercase tracking-wider">System</span>
        </div>

        <a href="{{ route('admin.theme') }}" 
           class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group
                  {{ request()->routeIs('admin.theme') ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'text-sidebar-text hover:bg-black/5 dark:hover:bg-white/5' }}">
            <i class="ri-palette-line text-xl mr-3 {{ request()->routeIs('admin.theme') ? 'text-white' : 'text-gray-400 group-hover:text-primary' }}"></i>
            <span class="font-medium">Theme & Color</span>
        </a>

    </nav>

    <div class="p-4 border-t border-custom-border bg-black/5 dark:bg-black/20">
        <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/10 transition-colors cursor-pointer">
            @if(Auth::user()->avatar)
                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="h-10 w-10 rounded-full object-cover border-2 border-white/20 shadow-sm">
            @else
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            @endif
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate text-sidebar-text">{{ Auth::user()->name }}</p>
                <p class="text-[11px] text-primary truncate font-medium bg-primary/10 px-2 py-0.5 rounded-full inline-block mt-0.5">
                    {{ Auth::user()->roles->pluck('name')->first() ?? 'User' }}
                </p>
            </div>
        </div>
    </div>

</aside>