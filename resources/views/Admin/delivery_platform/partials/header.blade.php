<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-4 gap-4">
    <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
        <i class="ri-motorbike-line"></i>
        Delivery Platforms
    </h1>
    
    <div class="hidden md:flex gap-2">
        <button @click="openModal('create')" class="btn-primary font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex items-center gap-2">
            <i class="ri-add-circle-line text-xl"></i><span>បន្ថែមថ្មី</span>
        </button>
    </div>
</div>

<div class="flex flex-col md:flex-row gap-3 mb-4 sm:mb-6">
    <div class="relative flex-1 w-full min-w-[200px]">
        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-secondary"><i class="ri-search-line"></i></span>
        <input type="text" x-model="search" @keyup.debounce.500ms="fetchPlatforms()" class="w-full pl-8 pr-3 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color text-xs sm:text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20" placeholder="ស្វែងរកឈ្មោះ...">
    </div>

    <div class="flex gap-2 md:hidden">
        <button @click="openModal('create')" class="flex-[3] bg-primary text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex justify-center items-center gap-2">
            <i class="ri-add-circle-line text-xl"></i><span>បន្ថែមថ្មី</span>
        </button>
    </div>
</div>