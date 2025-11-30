<aside id="sidebar" class="bg-sidebar-bg text-sidebar-text w-72 flex flex-col h-screen flex-shrink-0 z-50 relative border-r border-custom-border transition-colors duration-300">
    
    <div class="h-20 flex items-center justify-center bg-sidebar-bg sticky top-0 z-20 border-b border-custom-border transition-colors duration-300">
        <div class="flex items-center gap-3 w-full px-6 transition-all duration-300 menu-item-content">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-lg flex-shrink-0">
                <i class="ri-store-2-fill text-xl"></i>
            </div>
            <div class="flex flex-col sidebar-text overflow-hidden whitespace-nowrap">
                <span class="text-lg font-bold tracking-tight">Ice Cream</span>
                <span class="text-[10px] font-semibold text-primary uppercase tracking-widest">Admin Panel</span>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto no-scrollbar py-6 px-4 space-y-2">

        <div class="group relative">
            <a href="{{ route('admin.dashboard') }}" 
               class="sidebar-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 menu-item-content
                      {{ request()->routeIs('admin.dashboard') ? 'btn-primary shadow-lg' : '' }}">
                <i class="ri-dashboard-line text-xl menu-icon mr-3"></i>
                <span class="sidebar-text font-medium">Dashboard</span>
            </a>
            <div class="tooltip hidden absolute left-[100%] top-2 ml-4 bg-gray-900 text-white text-xs px-3 py-2 rounded shadow-xl z-50 whitespace-nowrap">Dashboard</div>
        </div>

        <div class="px-4 mt-6 mb-2 sidebar-text">
            <span class="text-[11px] font-bold opacity-50 uppercase tracking-wider">Management</span>
        </div>

        @php $isUserActive = request()->routeIs('user.*'); @endphp
        
        <div class="group relative">
            <button onclick="toggleSubmenu(this)" 
                    class="sidebar-item w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 cursor-pointer select-none menu-item-content
                           {{ $isUserActive ? 'bg-black/5 dark:bg-white/10' : '' }}">
                <div class="flex items-center">
                    <i class="ri-user-settings-line text-xl menu-icon mr-3 {{ $isUserActive ? 'text-primary' : '' }}"></i>
                    <span class="sidebar-text font-medium">Users</span>
                </div>
                <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-300 {{ $isUserActive ? 'rotate-180' : '' }}"></i>
            </button>

            <div class="submenu {{ $isUserActive ? '' : 'hidden' }} transition-all duration-300">
                <div class="tree-line absolute left-[26px] top-0 bottom-2 w-px bg-custom-border opacity-50"></div>
                <ul class="space-y-1 mt-1">
                    
                    <li>
                        <a href="{{ route('user.list') }}" 
                           class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all duration-200 pl-12 pr-4
                                  {{ request()->routeIs('user.list') ? 'text-primary font-bold' : 'opacity-80' }}">
                            
                            <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg 
                                         {{ request()->routeIs('user.list') ? 'bg-primary' : 'bg-gray-400' }}"></span>
                            
                            <span>User List</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all pl-12 pr-4 opacity-80">
                            <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg bg-gray-400"></span>
                            <span>Create New</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tooltip hidden absolute left-[100%] top-2 ml-4 bg-gray-900 text-white text-xs px-3 py-2 rounded shadow-xl z-50 whitespace-nowrap">Users</div>
        </div>

        <div class="px-4 mt-6 mb-2 sidebar-text">
            <span class="text-[11px] font-bold opacity-50 uppercase tracking-wider">Settings</span>
        </div>

        <div class="group relative">
            <a href="{{ route('admin.theme') }}" 
               class="sidebar-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 menu-item-content
                      {{ request()->routeIs('admin.theme') ? 'btn-primary shadow-lg' : '' }}">
                <i class="ri-palette-line text-xl menu-icon mr-3"></i>
                <span class="sidebar-text font-medium">Theme & Color</span>
            </a>
            <div class="tooltip hidden absolute left-[100%] top-2 ml-4 bg-gray-900 text-white text-xs px-3 py-2 rounded shadow-xl z-50 whitespace-nowrap">Theme</div>
        </div>

    </nav>

    <div class="p-4 border-t border-custom-border bg-black/5 dark:bg-black/20">
        <div class="sidebar-item flex items-center gap-3 p-2 rounded-xl transition-colors cursor-pointer menu-item-content">
            <img class="h-9 w-9 rounded-full object-cover border-2 border-custom-border flex-shrink-0" 
                 src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'Admin' }}&background=random&color=fff" alt="User">
            <div class="sidebar-text overflow-hidden">
                <p class="text-sm font-semibold truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                <p class="text-xs opacity-60 truncate">Administrator</p>
            </div>
        </div>
    </div>
</aside>