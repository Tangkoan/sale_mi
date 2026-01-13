<aside id="sidebar" class="bg-sidebar-bg text-sidebar-text w-72 h-screen flex flex-col flex-shrink-0 z-50 
              fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition-all duration-300
              border-r border-custom-border">


    <div class="h-20 flex items-center justify-center bg-sidebar-bg sticky top-0 z-20 border-b border-custom-border transition-colors duration-300">
        <div class="flex items-center gap-3 w-full px-6 transition-all duration-300 menu-item-content">
            
            {{-- ១. ផ្នែក Logo --}}
            @if(isset($shop) && $shop->logo)
                {{-- បើមាន Logo ក្នុង Database --}}
                <img src="{{ asset('storage/' . $shop->logo) }}" 
                     class="w-10 h-10 rounded-xl object-cover shadow-lg border border-white/10 flex-shrink-0 bg-white" 
                     alt="Shop Logo">
            @else
                {{-- បើអត់មាន Logo ប្រើ Icon ដើម --}}
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-lg flex-shrink-0">
                    <i class="ri-store-2-fill text-xl"></i>
                </div>
            @endif

            {{-- ២. ផ្នែកឈ្មោះហាង --}}
            <div class="flex flex-col sidebar-text overflow-hidden whitespace-nowrap">
                {{-- បង្ហាញឈ្មោះហាង ឬដាក់ Default បើអត់ទាន់មាន --}}
                <span class="text-lg font-bold tracking-tight text-text-color truncate">
                    {{ $shop->shop_en ?? 'POS System' }}
                </span>
                {{-- បង្ហាញ Role របស់អ្នកប្រើប្រាស់ --}}
                    <span class="text-[10px] font-bold text-primary uppercase tracking-widest truncate" 
                        title="{{ auth()->user()->getRoleNames()->implode(', ') }}">
                        
                        {{-- បង្ហាញ Role ទីមួយដែលគេមាន ឬដាក់ថា Staff បើអត់មាន Role --}}
                        {{ auth()->user()->getRoleNames()->first() ?? 'Staff Member' }}
                    
                    </span>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto no-scrollbar py-6 px-4 space-y-2">

        <div class="group relative">
            <a href="{{ route('admin.dashboard') }}" 
               class="sidebar-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 menu-item-content
                      {{ request()->routeIs('admin.dashboard') ? 'btn-primary shadow-lg' : '' }}">
                {{-- <i class="ri-dashboard-line text-xl menu-icon mr-3"></i> --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span class="sidebar-text font-medium​ px-2">Dashboard</span>
            </a>
            <div class="tooltip hidden absolute left-[100%] top-2 ml-4 bg-gray-900 text-white text-xs px-3 py-2 rounded shadow-xl z-50 whitespace-nowrap">Dashboard</div>
        </div>
 
        {{-- ពិនិត្យមើលសិនថា តើ User មានសិទ្ធិមើល Menu ណាមួយក្នុង Group នេះឬអត់? --}} 
        @if(auth()->user()->can('user-list') || auth()->user()->can('role-list') || auth()->user()->can('permission-list') || auth()->user()->hasRole('Super Admin'))
            
            <div class="px-4 mt-6 mb-2 sidebar-text">
                <span class="text-[11px] font-bold opacity-50 uppercase tracking-wider">User Management</span>
            </div>

            @php $isUserActive = request()->routeIs('user.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.rules.*') || request()->routeIs('admin.activity_logs.*') ; @endphp 
            
            <div class="group relative">
                <button onclick="toggleSubmenu(this)" 
                        class="sidebar-item w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 cursor-pointer select-none menu-item-content
                               {{ $isUserActive ? 'bg-black/5 dark:bg-white/10' : '' }}">
                    <div class="flex items-center">
                        {{-- <i class="ri-user-settings-line text-xl menu-icon mr-3 {{ $isUserActive ? 'text-primary' : '' }}"></i> --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>

                        <span class="sidebar-text font-medium px-2">Users & Access</span>
                    </div>
                    <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-300 {{ $isUserActive ? 'rotate-180' : '' }}"></i>
                </button>

                <div class="submenu {{ $isUserActive ? '' : 'hidden' }} transition-all duration-300">
                    <div class="tree-line absolute left-[26px] top-0 bottom-2 w-px bg-custom-border opacity-50"></div>
                    <ul class="space-y-1 mt-1">
                        
                        {{-- 1. User List --}}
                        @can('user-list')
                            <li>
                                <a href="{{ route('user.list') }}" 
                                class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all duration-200 pl-12 pr-4
                                        {{ request()->routeIs('user.list') ? 'text-primary font-bold' : 'opacity-80' }}">
                                    <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg 
                                                {{ request()->routeIs('user.list') ? 'bg-primary' : 'bg-gray-400' }}"></span>
                                    <span>User List</span>
                                </a>
                            </li>
                        @endcan

                        {{-- 2. Role & Permission --}}
                        @can('role-list')
                            <li>
                                <a href="{{ route('admin.roles.index') }}" 
                                class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all duration-200 pl-12 pr-4
                                        {{ request()->routeIs('admin.roles.*') ? 'text-primary font-bold' : 'opacity-80' }}">
                                    <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg 
                                                {{ request()->routeIs('admin.roles.*') ? 'bg-primary' : 'bg-gray-400' }}"></span>
                                    <span>Role & Permission</span>
                                </a>
                            </li>
                        @endcan

                        {{-- 3. Permission List --}}
                        @can('permission-list')
                            <li>
                                <a href="{{ route('admin.permissions.index') }}" 
                                class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all duration-200 pl-12 pr-4
                                        {{ request()->routeIs('admin.permissions.*') ? 'text-primary font-bold' : 'opacity-80' }}">
                                    <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg 
                                                {{ request()->routeIs('admin.permissions.*') ? 'bg-primary' : 'bg-gray-400' }}"></span>
                                    <span>Permissions List</span>
                                </a>
                            </li>
                        @endcan


                        {{-- 4. Permission Assing --}}
                        @can('rule-list')
                            <li>
                                <a href="{{ route('admin.rules.index') }}" 
                                class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all duration-200 pl-12 pr-4
                                        {{ request()->routeIs('admin.rules.*') ? 'text-primary font-bold' : 'opacity-80' }}">
                                    <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg 
                                                {{ request()->routeIs('admin.rules.*') ? 'bg-primary' : 'bg-gray-400' }}"></span>
                                    <span>Rule List</span>
                                </a>
                            </li>
                        @endcan


                        {{-- 4. Permission Assing --}}
                        @can('activity-list')
                            <li>
                                <a href="{{ route('admin.activity_logs.index') }}" 
                                class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all duration-200 pl-12 pr-4
                                        {{ request()->routeIs('admin.activity_logs.*') ? 'text-primary font-bold' : 'opacity-80' }}">
                                    <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg 
                                                {{ request()->routeIs('admin.activity_logs.*') ? 'bg-primary' : 'bg-gray-400' }}"></span>
                                    <span>User Action</span>
                                </a>
                            </li>
                        @endcan

                    </ul>
                </div>
                <div class="tooltip hidden absolute left-[100%] top-2 ml-4 bg-gray-900 text-white text-xs px-3 py-2 rounded shadow-xl z-50 whitespace-nowrap">Users</div>
            </div>
        @endif


        {{-- <div class="px-4 mt-6 mb-2 sidebar-text">
            <span class="text-[11px] font-bold opacity-50 uppercase tracking-wider">Settings</span>
        </div> --}}


        {{-- ពិនិត្យមើលសិនថា តើ User មានសិទ្ធិមើល Menu ណាមួយក្នុង Group នេះឬអត់? --}} 
        {{-- ============================================= --}}
        {{--                SETTINGS SECTION               --}}
        {{-- ============================================= --}}

        {{-- ១. កំណត់ Logic ថាពេលណាត្រូវអោយ Menu នេះ Active --}}
        @php 
            $isSettingsActive = request()->routeIs('admin.theme') || request()->routeIs('admin.shop_info.index'); 
        @endphp

        <div class="px-4 mt-6 mb-2 sidebar-text">
            <span class="text-[11px] font-bold opacity-50 uppercase tracking-wider">System</span>
        </div>

        <div class="group relative">
            {{-- ២. ប៊ូតុងមេ (Parent Button) --}}
            <button onclick="toggleSubmenu(this)" 
                    class="sidebar-item w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 cursor-pointer select-none menu-item-content
                           {{ $isSettingsActive ? 'bg-black/5 dark:bg-white/10' : '' }}">
                
                <div class="flex items-center">
                    {{-- Icon Settings --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <span class="sidebar-text font-medium px-2">Settings</span>
                </div>
                <i class="ri-arrow-down-s-line arrow-icon transition-transform duration-300 {{ $isSettingsActive ? 'rotate-180' : '' }}"></i>
            </button>

            {{-- ៣. Sub-menu List --}}
            <div class="submenu {{ $isSettingsActive ? '' : 'hidden' }} transition-all duration-300">
                <div class="tree-line absolute left-[26px] top-0 bottom-2 w-px bg-custom-border opacity-50"></div>
                <ul class="space-y-1 mt-1">
                    
                    {{-- Sub-menu 1: Theme & Color --}}
                    @can('theme-color') {{-- ដាក់ Permission បើមាន --}}
                    <li>
                        <a href="{{ route('admin.theme') }}" 
                           class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all duration-200 pl-12 pr-4
                                  {{ request()->routeIs('admin.theme') ? 'text-primary font-bold' : 'opacity-80' }}">
                            
                            {{-- ចំណុចមូលតូច (Active Indicator) --}}
                            <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg 
                                         {{ request()->routeIs('admin.theme') ? 'bg-primary' : 'bg-gray-400' }}"></span>
                            
                            <span>Theme & Color</span>
                        </a>
                    </li>
                    @endcan

                    {{-- Sub-menu 2: General (ដាក់ជាគំរូសិន) --}}
                    @can('setting-shop_info') {{-- ដាក់ Permission បើមាន --}}
                    <li>
                        <a href="{{ route('admin.shop_info.index') }}" 
                           class="sidebar-item relative flex items-center py-2.5 rounded-lg text-sm transition-all duration-200 pl-12 pr-4
                                  {{ request()->routeIs('admin.shop_info.index') ? 'text-primary font-bold' : 'opacity-80' }}">
                            
                            {{-- ចំណុចមូលតូច (Active Indicator) --}}
                            <span class="tree-line absolute left-[22px] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full border-2 border-sidebar-bg 
                                         {{ request()->routeIs('admin.shop_info.index') ? 'bg-primary' : 'bg-gray-400' }}"></span>
                            
                            <span>Shop Info</span>
                        </a>
                    </li>
                    @endcan

                   
                </ul>
            </div>
            
            {{-- Tooltip ពេលបង្រួម Sidebar --}}
            <div class="tooltip hidden absolute left-[100%] top-2 ml-4 bg-gray-900 text-white text-xs px-3 py-2 rounded shadow-xl z-50 whitespace-nowrap">Settings</div>
        </div>

        

        
        
        {{-- @can('theme-color')
        <div class="group relative">
            <a href="{{ route('admin.theme') }}" 
               class="sidebar-item flex items-center px-4 py-3 rounded-xl transition-all duration-200 menu-item-content
                      {{ request()->routeIs('admin.theme') ? 'btn-primary shadow-lg' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" class="size-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                </svg>

                <span class="sidebar-text font-medium px-2">Theme & Color</span>
            </a>
            <div class="tooltip hidden absolute left-[100%] top-2 ml-4 bg-gray-900 text-white text-xs px-3 py-2 rounded shadow-xl z-50 whitespace-nowrap">Theme</div>
        </div>
        @endcan --}}

    </nav>

    

    <div class="p-1 border-t border-custom-border bg-black/5 dark:bg-black/20">
        <a href="https://t.me/Vannchinh11" target="_blank" class="sidebar-item flex items-center gap-3 p-2 rounded-xl transition-colors cursor-pointer menu-item-content hover:bg-black/5 dark:hover:bg-white/5">
            
            <img src="{{ asset('storage/creater/kuytangkoan.jpg') }}" 
                class="h-10 w-10 rounded-full object-cover border-2 border-primary flex-shrink-0 shadow-sm"
                alt="Creator Profile">
            
            <div class="sidebar-text overflow-hidden">
                <p class="text-sm font-semibold truncate text-text-color">Created By</p>
                <p class="text-xs text-primary truncate font-medium flex items-center gap-1">
                    Kuy Tangkoan
                </p>
            </div>
        </a>
    </div>

</aside>

<div id="sidebarOverlay" 
     class="fixed inset-0 bg-black/50 z-40 hidden md:hidden glass transition-opacity opacity-0"
     onclick="toggleMobileSidebar()">
</div>