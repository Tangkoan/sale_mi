<div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-4 gap-4">
    <h1 class="text-xl sm:text-2xl font-bold text-text-color flex items-center gap-2">
        <i class="ri-shopping-bag-3-line"></i>
        {{ __('messages.product_management') }}
    </h1>
    
    <div class="hidden md:flex gap-2">
        <a href="{{ url('/pos/kitchen') }}" target="_blank" class="font-bold py-2.5 px-4 rounded-xl flex items-center gap-2 border border-input-border bg-card-bg text-text-color hover:bg-input-bg shadow-sm">
            <i class="ri-fire-line text-orange-500 text-xl"></i> <span>Kitchen</span>
        </a>
        <button @can('product-create') @click="openModal('create')" @endcan class="bg-primary text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex items-center gap-2">
            <i class="ri-add-circle-line text-xl"></i><span>{{ __('messages.add_product') }}</span>
        </button>
    </div>
</div>

<div class="flex flex-col md:flex-row gap-3 mb-4 sm:mb-6">
    <div class="flex items-center gap-2 w-full md:w-auto flex-1">
        
        {{-- Category --}}
        <div class="w-1/3 md:w-48">
            <select x-model="filterCategory" @change="fetchProducts()" class="w-full px-2 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color text-xs sm:text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20 truncate">
                <option value="">{{ __('messages.all_categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Search --}}
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-secondary"><i class="ri-search-line"></i></span>
            <input type="text" x-model="search" @keyup.debounce.500ms="fetchProducts()" class="w-full pl-8 pr-3 py-2.5 rounded-xl border border-input-border bg-card-bg text-text-color text-xs sm:text-sm shadow-sm outline-none focus:ring-2 focus:ring-primary/20" placeholder="{{ __('messages.search_placeholder') }}">
        </div>

        {{-- Column Button --}}
        <div class="relative shrink-0" x-data="{ openCol: false }">
            <button @click="openCol = !openCol" @click.outside="openCol = false" class="h-[42px] px-3 bg-card-bg border border-input-border rounded-xl text-text-color hover:bg-input-bg transition text-sm font-medium shadow-sm flex items-center justify-center">
                <i class="ri-layout-column-line text-lg"></i> 
                <span class="hidden md:inline ml-2">{{ __('messages.columns') }}</span>
            </button>
            <div x-show="openCol" class="absolute right-0 mt-2 w-48 bg-card-bg border border-border-color rounded-xl shadow-xl z-50 p-2" style="display: none;" x-transition>
                <div class="space-y-1">
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                        <input type="checkbox" x-model="showCols.image" class="rounded text-primary focus:ring-primary border-input-border">
                        <span class="text-sm text-text-color">{{ __('messages.image') }}</span>
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                        <input type="checkbox" x-model="showCols.category" class="rounded text-primary focus:ring-primary border-input-border">
                        <span class="text-sm text-text-color">{{ __('messages.category') }}</span>
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-page-bg rounded cursor-pointer select-none">
                        <input type="checkbox" x-model="showCols.price" class="rounded text-primary focus:ring-primary border-input-border">
                        <span class="text-sm text-text-color">{{ __('messages.price') }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Action Buttons --}}
    <div class="flex gap-2 md:hidden">
        <a href="{{ url('/pos/kitchen') }}" target="_blank" class="flex-1 font-bold py-2.5 px-4 rounded-xl flex justify-center items-center gap-2 border border-input-border bg-card-bg text-text-color hover:bg-input-bg shadow-sm">
            <i class="ri-fire-line text-orange-500 text-lg"></i>
        </a>
        <button @can('product-create') @click="openModal('create')" @endcan class="flex-[3] bg-primary text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-primary/30 hover:opacity-90 flex justify-center items-center gap-2">
            <i class="ri-add-circle-line text-xl"></i><span>{{ __('messages.add_product') }}</span>
        </button>
    </div>

    {{-- Selected Items --}}
    <div x-show="selectedIds.length > 0" x-transition class="flex items-center gap-2 w-full md:w-auto justify-between bg-primary/10 border border-primary/20 p-2 rounded-xl">
            <span class="text-xs font-bold text-primary px-2" x-text="selectedIds.length + ' {{ __('messages.selected_items') }}'"></span>
        <div class="flex gap-1">
            @can('product-edit')
            <button @click="startSequentialEdit()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition"><i class="ri-edit-circle-line"></i></button>
            @endcan
            @can('product-delete')
            <button @click="confirmBulkDelete()" class="h-8 w-8 flex items-center justify-center rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition"><i class="ri-delete-bin-line"></i></button>
            @endcan
        </div>
    </div>
</div>