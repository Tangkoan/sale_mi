<div class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar" x-show="tables.length > 0" x-cloak>
    
    {{-- Tables Grid (រួមបញ្ចូលទាំង Delivery Card ក្នុងនេះផងដែរ) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
        
        {{-- Delivery Card ពណ៌លឿង/ទឹកក្រូច (ដាក់ក្នុង Grid ដើម្បីឲ្យទំហំប៉ុនកាតតុធម្មតា) --}}
        <div class="relative group cursor-pointer" @click="openDeliveryModal()">
            <div class="block aspect-square rounded-[24px] sm:rounded-[32px] p-4 flex flex-col items-center justify-center transition-all duration-300 border-[3px] shadow-sm hover:shadow-xl hover:-translate-y-1 active:scale-95 relative overflow-hidden z-0 bg-orange-50 dark:bg-orange-900/10 border-orange-200 dark:border-orange-800 hover:border-orange-400">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full flex items-center justify-center mb-2 sm:mb-3 transition-colors duration-300 bg-orange-100 dark:bg-orange-500/20 text-orange-500">
                    <i class="ri-e-bike-2-fill text-2xl sm:text-3xl"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-black text-gray-800 dark:text-white mb-1 text-center leading-tight px-1">Delivery</h3>
                
                {{-- Badge តូចនៅខាងក្រោម (ជាជម្រើស ដើម្បីឲ្យមើលទៅរចនាបទដូចតុ) --}}
                <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest px-2 py-1 rounded-md text-orange-600 bg-orange-100/50 mt-1">
                    Order
                </span>
            </div>
        </div>
        
        {{-- Table Cards --}}
        <template x-for="table in filteredTables" :key="table.id">
            <div class="relative group">
                
                {{-- Table Card --}}
                <a :href="table.status === 'available' ? '/pos/menu/' + table.id : '#'"
                   @click="if(table.status === 'busy') { $event.preventDefault(); openChangeItemList(table); }" 
                   class="block aspect-square rounded-[24px] sm:rounded-[32px] p-4 flex flex-col items-center justify-center transition-all duration-300 border-[3px] shadow-sm hover:shadow-xl hover:-translate-y-1 active:scale-95 relative overflow-hidden z-0"
                   :class="table.status === 'available' ? 'bg-card-bg border-bor-color hover:border-emerald-400/50' : 'bg-rose-50 dark:bg-rose-900/10 border-rose-100 dark:border-rose-900/30 hover:border-rose-400/50'">
                    
                    <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full flex items-center justify-center mb-2 sm:mb-3 transition-colors duration-300"
                         :class="table.status === 'available' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500' : 'bg-rose-100 dark:bg-rose-900/20 text-rose-500'">
                        <i class="ri-restaurant-2-fill text-2xl sm:text-3xl"></i>
                    </div>
                    
                    <h3 class="text-lg sm:text-xl font-black text-gray-800 dark:text-white mb-1 text-center leading-tight px-1" x-text="table.name"></h3>
                    
                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest px-2 py-1 rounded-md"
                          :class="table.status === 'available' ? 'text-emerald-600 bg-emerald-100/50' : 'text-rose-600 bg-rose-100/50'"
                          x-text="table.status === 'available' ? '{{ __('messages.available') }}' : '{{ __('messages.busy') }}'">
                    </span>
                </a>

                {{-- Action Buttons --}}
                <template x-if="table.status === 'busy'">
                    <div class="absolute top-2 right-2 sm:top-2 sm:right-2 flex flex-col gap-2 z-20">
                        @can('pos-checkout') 
                            <button @click.prevent="openQuickCheckout(table)"
                                    class="w-10 h-10 sm:w-12 sm:h-12 bg-card-bg text-rose-500 rounded-full shadow-lg hover:bg-rose-500 hover:text-white hover:scale-110 active:scale-90 transition-all flex items-center justify-center border border-rose-100 dark:border-rose-900"
                                    title="Quick Checkout">
                                <i class="ri-money-dollar-circle-line text-xl sm:text-2xl"></i>
                            </button>
                        @endcan
                        
                        @can('pos-exchange-item')
                            <button @click.prevent="openChangeItemList(table)"
                                    class="w-10 h-10 sm:w-12 sm:h-12 bg-card-bg text-blue-500 rounded-full shadow-lg hover:bg-blue-500 hover:text-white hover:scale-110 active:scale-90 transition-all flex items-center justify-center border border-blue-100 dark:border-blue-900"
                                    title="បញ្ជីម្ហូប / ប្ដូរម្ហូប">
                                <i class="ri-edit-box-line text-xl sm:text-2xl"></i>
                            </button>
                        @endcan
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Modal Delivery Platform --}}
    <div x-show="isDeliveryModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-cloak>
        <div @click.away="isDeliveryModalOpen = false" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-11/12 sm:w-[500px] p-6 relative">
            <button @click="isDeliveryModalOpen = false" class="absolute top-4 right-4 text-gray-500 hover:text-red-500">
                <i class="ri-close-line text-2xl"></i>
            </button>
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">ជ្រើសរើស Delivery Platform</h2>
            
            <div x-show="isLoadingPlatforms" class="flex justify-center py-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4" x-show="!isLoadingPlatforms">
                <template x-if="deliveryPlatforms.length === 0 && !isLoadingPlatforms">
                     <p class="col-span-2 text-center text-gray-500 py-4">មិនមានទិន្នន័យ Delivery Platform ទេ (ឬ Error 500)</p>
                </template>
                <template x-for="platform in deliveryPlatforms" :key="platform.id">
                    <button @click="selectDeliveryPlatform(platform)" class="flex flex-col items-center justify-center p-4 border rounded-xl hover:bg-orange-50 hover:border-orange-400 transition-colors">
                        <img x-show="platform.logo" :src="'/storage/' + platform.logo" class="w-12 h-12 object-contain mb-2" alt="Logo">
                        <span x-text="platform.name" class="font-bold text-gray-700 dark:text-gray-200"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>