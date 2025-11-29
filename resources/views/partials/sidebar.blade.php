<aside id="sidebar" class="bg-slate-900 border-r border-slate-800 text-slate-300 w-64 flex flex-col h-screen flex-shrink-0 relative">
    
    <div class="h-16 flex items-center justify-center border-b border-slate-800 bg-slate-900 sticky top-0 z-20">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/50">
                <i class="ri-dashboard-3-fill text-lg"></i>
            </div>
            <span class="sidebar-text text-lg font-bold text-white tracking-wide">Ice Cream Shop</span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto no-scrollbar py-4 space-y-2">
        
        <div class="px-3 group relative">
            <a href="{{ route('admin.dashboard') }}" 
               class="menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 
                      {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-slate-800 hover:text-white' }}">
                <i class="ri-home-4-line text-xl min-w-[1.5rem] text-center"></i>
                <span class="sidebar-text ml-3 font-medium">Dashboard</span>
            </a>
            
            <div class="tooltip hidden absolute left-[105%] top-2 bg-slate-800 text-white text-xs px-2 py-1.5 rounded shadow-lg whitespace-nowrap z-50 pointer-events-none">
                Dashboard
            </div>
        </div>

        <div class="px-4 mt-4 mb-2 sidebar-text">
            <span class="text-xs font-bold text-slate-500 uppercase">Management</span>
        </div>

        <div class="px-3 group relative">
            
            <button onclick="toggleSubmenu(this)" 
                    class="menu-item w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-all hover:bg-slate-800 hover:text-white cursor-pointer text-slate-300">
                <div class="flex items-center">
                    <i class="ri-user-settings-line text-xl min-w-[1.5rem] text-center"></i>
                    <span class="sidebar-text ml-3 font-medium">Users</span>
                </div>
                <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-200"></i>
            </button>

            <ul class="submenu hidden flex-col space-y-1 mt-1">
                
                <li>
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-colors pl-4"> <i class="ri-checkbox-blank-circle-line text-[8px] mr-2 sidebar-text"></i>
                        <span>User List</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center px-4 py-2 text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-colors pl-4">
                        <i class="ri-checkbox-blank-circle-line text-[8px] mr-2 sidebar-text"></i>
                        <span>Create New</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="px-3 group relative">
            <a href="#" class="menu-item flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 hover:bg-slate-800 hover:text-white text-slate-300">
                <i class="ri-settings-3-line text-xl min-w-[1.5rem] text-center"></i>
                <span class="sidebar-text ml-3 font-medium">Settings</span>
            </a>
            <div class="tooltip hidden absolute left-[105%] top-2 bg-slate-800 text-white text-xs px-2 py-1.5 rounded shadow-lg whitespace-nowrap z-50 pointer-events-none">
                Settings
            </div>
        </div>

    </nav>
    
    <div class="border-t border-slate-800 p-4 bg-slate-900 sticky bottom-0 z-20">
        <div class="flex items-center gap-3 justify-center md:justify-start">
            <img class="h-9 w-9 rounded-full object-cover border border-slate-600" 
                 src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=random" alt="User">
            <div class="sidebar-text overflow-hidden">
                <p class="text-sm font-medium text-white truncate w-32">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-500 truncate">Administrator</p>
            </div>
        </div>
    </div>
</aside>