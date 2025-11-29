<nav class="bg-white shadow px-6 py-4 flex justify-between items-center z-40 relative">
    <div class="flex items-center space-x-4">
        <button id="sidebarToggle" class="text-gray-600 hover:text-blue-600 focus:outline-none transition-colors p-1 rounded hover:bg-gray-100">
            <i class="ri-menu-2-line text-2xl"></i>
        </button>
        
        <h1 class="font-bold text-xl text-gray-800 hidden sm:block">Admin Dashboard</h1>
    </div>

    <div class="flex items-center space-x-4">
        <span class="text-gray-600 text-sm hidden sm:block">Hello, <strong>{{ Auth::user()->name }}</strong></span>
        
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-bold py-2 px-4 rounded transition duration-200 flex items-center shadow-md">
                <i class="ri-logout-box-r-line mr-2"></i> Logout
            </button>
        </form>
    </div>
</nav>