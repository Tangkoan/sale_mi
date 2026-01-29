@extends('layouts.blank')

@section('content')
<div class="h-screen w-full bg-[#F6F8FC] dark:bg-[#0f172a] flex flex-col relative overflow-hidden font-sans" x-data="posMenu()" x-cloak>
    
    {{-- =========================================== --}}
    {{-- 1. HEADER                                   --}}
    {{-- =========================================== --}}
    <div class="z-30 shrink-0 bg-white/80 dark:bg-gray-800/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0">
        <div class="px-4 py-3 flex items-center justify-between gap-4">
            
            {{-- LEFT: Table Info & Back --}}
            <div class="flex items-center gap-3 overflow-hidden">
                <a href="{{ route('pos.tables') }}" class="flex-shrink-0 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="ri-arrow-left-line"></i>
                    <span class="hidden sm:inline">Tables</span>
                </a>
                
                <div class="flex flex-col min-w-0 border-l pl-3 border-gray-300 dark:border-gray-600">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white truncate leading-tight">{{ $table->name }}</h2>
                    <div class="flex items-center gap-2 text-xs font-medium">
                        @if($currentOrder) 
                            <span class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded-md">#{{ $currentOrder->invoice_number }}</span>
                        @else
                            <span class="bg-green-100 text-green-600 px-2 py-0.5 rounded-md">{{ __('messages.new_order') }}</span>
                        @endif
                        
                        {{-- Polling Status Indicator --}}
                        <span class="flex items-center gap-1 text-[10px] text-gray-400" x-show="isPolling">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            Live
                        </span>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Actions --}}
            <div class="flex items-center gap-2">
                {{-- Quick Addon Button --}}
                <button @click="openQuickAddon()" class="hidden md:flex bg-purple-100 text-purple-700 hover:bg-purple-200 px-4 py-2 rounded-xl font-bold transition items-center gap-2">
                    <i class="ri-add-circle-fill text-xl"></i> <span>Addon</span>
                </button>

                {{-- Kitchen Link --}}
                <a href="{{ route('pos.kitchen.view') }}" target="_blank" class="hidden md:flex items-center justify-center w-10 h-10 rounded-full bg-orange-100 text-orange-600 hover:bg-orange-200 transition" title="Kitchen Screen">
                    <i class="ri-fire-line text-xl"></i>
                </a>

                {{-- Search Trigger --}}
                <button @click="isSearchOpen = !isSearchOpen" 
                        class="flex-shrink-0 w-10 h-10 rounded-full border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                        :class="isSearchOpen ? 'bg-primary text-white border-primary ring-2 ring-primary/30' : 'bg-white dark:bg-gray-800'">
                    <i class="ri-search-line text-xl" :class="isSearchOpen ? 'text-white' : ''"></i>
                </button>
            </div>
        </div>

        {{-- Search Bar --}}
        <div x-show="isSearchOpen" x-transition class="px-4 pb-3">
            <div class="relative">
                <i class="ri-search-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="search" placeholder="Search menu..." class="w-full pl-11 pr-4 py-2.5 rounded-xl border-0 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary outline-none transition-all shadow-inner">
            </div>
        </div>

        {{-- Category Tabs --}}
        <div class="px-4 pb-3 overflow-x-auto no-scrollbar flex gap-3 snap-x">
            <button @click="activeCategory = 'all'" class="snap-start flex-shrink-0 px-5 py-2 rounded-full text-sm font-bold transition-all duration-300 border shadow-sm select-none" :class="activeCategory === 'all' ? 'bg-primary border-primary text-white shadow-primary/30 scale-105' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'">All</button>
            @foreach($categories as $cat)
                <button @click="activeCategory = {{ $cat->id }}" class="snap-start flex-shrink-0 px-5 py-2 rounded-full text-sm font-bold transition-all duration-300 border shadow-sm select-none" :class="activeCategory === {{ $cat->id }} ? 'bg-primary border-primary text-white shadow-primary/30 scale-105' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'">{{ $cat->name }}</button>
            @endforeach
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 2. PRODUCT GRID                             --}}
    {{-- =========================================== --}}
    <div class="flex-1 overflow-y-auto overflow-x-hidden p-4 pb-32 custom-scrollbar">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="product.is_active ? openProductModal(product) : null" 
                     class="group bg-white dark:bg-gray-800 rounded-2xl p-2.5 shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-300 relative overflow-hidden"
                     :class="product.is_active ? 'hover:shadow-lg cursor-pointer hover:-translate-y-1 active:scale-95' : 'opacity-60 cursor-not-allowed grayscale'">
                    
                    {{-- OUT OF STOCK OVERLAY --}}
                    <div x-show="!product.is_active" class="absolute inset-0 z-10 flex items-center justify-center bg-gray-900/10 backdrop-blur-[1px]">
                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg transform -rotate-12 border-2 border-white">
                            OUT OF STOCK
                        </span>
                    </div>

                    {{-- Image --}}
                    <div class="aspect-square rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-700 relative mb-3">
                        <template x-if="product.image">
                            <img :src="'/storage/' + product.image" class="w-full h-full object-cover transition-transform duration-500" :class="product.is_active ? 'group-hover:scale-110' : ''">
                        </template>
                        <template x-if="!product.image">
                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-600">
                                <i class="ri-image-2-line text-3xl mb-1"></i>
                                <span class="text-[10px] uppercase font-bold tracking-widest">No Image</span>
                            </div>
                        </template>
                        
                        {{-- Add Icon --}}
                        <div x-show="product.is_active" class="absolute bottom-2 right-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full w-8 h-8 flex items-center justify-center shadow-md text-primary opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                            <i class="ri-add-line font-bold text-lg"></i>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="px-1">
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm leading-snug line-clamp-2 min-h-[2.5em]" x-text="product.name"></h3>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="font-black text-base" :class="product.is_active ? 'text-primary' : 'text-gray-500'" x-text="'$' + parseFloat(product.price).toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="filteredProducts.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 text-gray-400">
                <i class="ri-search-2-line text-4xl mb-2"></i>
                <p class="font-medium">No products found.</p>
            </div>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 3. FLOATING CART BUTTON                     --}}
    {{-- =========================================== --}}
    <div x-show="cart.length > 0" x-transition class="absolute bottom-6 left-4 right-4 md:left-1/2 md:-translate-x-1/2 md:w-96 z-40">
        <button @click="isCartOpen = true" class="w-full bg-gray-900/90 dark:bg-primary/90 backdrop-blur-md text-white p-2 pr-4 pl-2 rounded-[20px] shadow-2xl border border-white/10 flex items-center justify-between group hover:scale-[1.02] transition-transform duration-200">
            <div class="flex items-center gap-3">
                <div class="bg-white text-gray-900 w-12 h-12 rounded-2xl flex items-center justify-center font-black text-lg shadow-sm" x-text="cartTotalQty"></div>
                <div class="flex flex-col items-start"><span class="text-xs text-gray-300 uppercase tracking-wider font-semibold">Total</span><span class="font-bold text-xl" x-text="'$' + cartTotalPrice.toFixed(2)"></span></div>
            </div>
            <div class="flex items-center gap-2 pr-2"><span class="font-bold text-sm">View Cart</span><i class="ri-arrow-right-line bg-white/20 rounded-full p-1 transition-transform group-hover:translate-x-1"></i></div>
        </button>
    </div>

    {{-- =========================================== --}}
    {{-- 4. MODAL: PRODUCT DETAILS                   --}}
    {{-- =========================================== --}}
    <div x-show="isProductModalOpen" class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-[3px]" @click="closeProductModal()"></div>
        <div class="relative w-full sm:max-w-md bg-white dark:bg-gray-800 sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]" x-show="isProductModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full sm:scale-95 opacity-0" x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100">
            
            {{-- Image Header --}}
            <div class="relative h-56 bg-gray-100 dark:bg-gray-700 shrink-0">
                <template x-if="tempItem.image"><img :src="'/storage/' + tempItem.image" class="w-full h-full object-cover"></template>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <button @click="closeProductModal()" class="absolute top-4 right-4 bg-black/20 text-white rounded-full p-2 hover:bg-black/40 backdrop-blur-md"><i class="ri-close-line text-xl"></i></button>
                <div class="absolute bottom-4 left-6 right-6 text-white"><h2 class="text-2xl font-bold leading-tight shadow-black drop-shadow-md" x-text="tempItem.name"></h2></div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                {{-- Price & Qty --}}
                <div class="flex items-center justify-between mb-8">
                    <div><p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Base Price</p><p class="text-3xl font-black text-primary" x-text="'$' + parseFloat(tempItem.base_price).toFixed(2)"></p></div>
                    <div class="flex items-center gap-4 bg-gray-100 dark:bg-gray-700 rounded-2xl p-1.5 shadow-inner">
                        <button @click="if(tempItem.qty > 1) tempItem.qty--" class="w-10 h-10 rounded-xl bg-white text-gray-800 hover:text-primary"><i class="ri-subtract-line font-bold"></i></button>
                        <span class="font-bold text-xl min-w-[1.5em] text-center text-gray-800 dark:text-white" x-text="tempItem.qty"></span>
                        <button @click="tempItem.qty++" class="w-10 h-10 rounded-xl bg-white text-gray-800 hover:text-primary"><i class="ri-add-line font-bold"></i></button>
                    </div>
                </div>

                {{-- Addons List --}}
                <div class="mb-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ __('messages.addon_list') }}</h3>
                    <div class="space-y-2.5">
                        <template x-for="addon in availableAddons" :key="addon.id">
                            <div class="flex items-center justify-between p-3 rounded-2xl border transition-all duration-200" :class="isAddonSelected(addon.id) ? 'border-primary bg-primary/5 shadow-inner' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                <div class="flex items-center gap-3 cursor-pointer flex-1" @click="toggleAddon(addon)">
                                    <div class="relative flex items-center justify-center w-6 h-6 rounded-md border transition-colors shrink-0" :class="isAddonSelected(addon.id) ? 'bg-primary border-primary' : 'border-gray-300 bg-white'">
                                        <i class="ri-check-line text-white text-sm" x-show="isAddonSelected(addon.id)"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200" x-text="addon.name"></p>
                                        <p class="text-xs text-primary font-bold" x-text="'+$' + parseFloat(addon.price).toFixed(2)"></p>
                                    </div>
                                </div>
                                <div x-show="isAddonSelected(addon.id)" x-transition class="flex items-center bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 h-8 ml-2">
                                    <button @click.stop="updateAddonQty(addon.id, -1)" class="px-2 text-gray-500 hover:text-red-500"><i class="ri-subtract-line"></i></button>
                                    <span class="text-sm font-bold w-6 text-center text-gray-800 dark:text-white" x-text="getAddonQty(addon.id)"></span>
                                    <button @click.stop="updateAddonQty(addon.id, 1)" class="px-2 text-gray-500 hover:text-primary"><i class="ri-add-line"></i></button>
                                </div>
                            </div>
                        </template>
                        <div x-show="availableAddons.length === 0" class="text-sm text-gray-400 italic text-center">No addons available for this item.</div>
                    </div>
                </div>

                {{-- Note --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Note</label>
                    <textarea x-model="tempItem.note" rows="2" class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary/50 outline-none text-sm" placeholder="Less sugar, no spicy..."></textarea>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 pb-8 sm:pb-4 shadow-[0_-5px_15px_rgba(0,0,0,0.02)] z-20">
                <button @click="addToCart()" class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:brightness-110 active:scale-[0.98] transition-all flex justify-between px-6 shadow-lg shadow-primary/30 relative overflow-hidden">
                    <span class="relative z-10">Add to Order</span>
                    <span class="relative z-10" x-text="'$' + calculateItemTotal().toFixed(2)"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 5. MODAL: CART DETAILS                      --}}
    {{-- =========================================== --}}
    <div x-show="isCartOpen" class="fixed inset-0 z-[100] flex items-end justify-center" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity duration-300" @click="isCartOpen = false"></div>
        <div class="relative w-full bg-white dark:bg-gray-800 rounded-t-[32px] shadow-2xl flex flex-col max-h-[85vh] transition-transform duration-300" x-show="isCartOpen" x-transition:enter="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="translate-y-full">
            
            <div class="w-full flex justify-center pt-4 pb-2" @click="isCartOpen = false"><div class="w-16 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full opacity-50"></div></div>
            
            <div class="px-6 py-4 flex justify-between items-center border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-2xl font-black text-gray-800 dark:text-white">Your Cart</h2>
                <button @click="cart = []; isCartOpen = false" class="text-red-500 font-bold bg-red-50 px-4 py-2 rounded-xl text-sm hover:bg-red-100 transition">Clear</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar bg-gray-50 dark:bg-gray-900">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex gap-4 group relative overflow-hidden">
                        <div class="flex flex-col items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-xl w-10 py-1 shrink-0 h-24">
                            <button @click="item.qty++;" class="w-full flex-1 text-gray-600 hover:text-primary"><i class="ri-add-line"></i></button>
                            <span class="font-bold text-sm text-gray-800 dark:text-white" x-text="item.qty"></span>
                            <button @click="if(item.qty > 1) item.qty--; else removeFromCart(index)" class="w-full flex-1 text-gray-600 hover:text-red-500"><i class="ri-subtract-line"></i></button>
                        </div>
                        <div class="flex-1 min-w-0 py-1">
                            <div class="flex justify-between items-start gap-2">
                                <h4 class="font-bold text-gray-800 dark:text-white leading-tight text-base" x-text="item.name"></h4>
                                <span class="font-bold text-primary whitespace-nowrap" x-text="'$' + (item.total_price_calculated || (item.base_price * item.qty)).toFixed(2)"></span>
                            </div>
                            <template x-if="item.addons && item.addons.length > 0">
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <template x-for="ad in item.addons">
                                        <div class="text-[10px] bg-blue-50 text-blue-600 px-2 py-1 rounded border border-blue-100 flex items-center gap-1">
                                            <span x-text="'+ ' + ad.name"></span>
                                            <span class="font-bold" x-text="'x' + (ad.qty || 1)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="item.note">
                                <div class="flex items-start gap-1 mt-2 bg-orange-50 p-1.5 rounded-lg w-fit">
                                    <i class="ri-sticky-note-line text-orange-400 text-xs mt-0.5"></i>
                                    <p class="text-[10px] text-gray-500 italic line-clamp-1" x-text="item.note"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="h-40 flex flex-col items-center justify-center text-gray-400 opacity-60"><i class="ri-shopping-cart-2-line text-5xl mb-3"></i><p class="text-sm font-medium">Cart is empty</p></div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 pb-8 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] z-20">
                <div class="flex justify-between items-center mb-6"><span class="text-gray-500 font-bold text-lg">Total Amount</span><span class="text-3xl font-black text-gray-900 dark:text-white" x-text="'$' + cartTotalPrice.toFixed(2)"></span></div>
                
                <button @click="submitOrder" 
                        :disabled="isSubmitting || cart.length === 0" 
                        class="w-full bg-gray-900 dark:bg-primary text-white py-4 rounded-2xl font-bold text-xl shadow-lg flex justify-center items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i x-show="isSubmitting" class="ri-loader-4-line animate-spin text-2xl"></i>
                    <span x-text="isSubmitting ? 'Processing...' : 'Confirm Order'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    function posMenu() {
        return {
            products: @json($products ?? []),
            categories: @json($categories ?? []),
            addons: @json($addons ?? []),
            activeCategory: 'all',
            search: '',
            cart: [],
            isCartOpen: false,
            isProductModalOpen: false,
            isSearchOpen: false,
            isSubmitting: false,
            isPolling: false,
            tempItem: { id: null, name: '', image: null, base_price: 0, qty: 1, note: '', selectedAddons: [], category_id: null },

            init() {
                // ចាប់ផ្តើម Polling ដើម្បីទាញយក Status ផលិតផលរៀងរាល់ 5 វិនាទី
                this.startPolling();
            },

            // ✅ Real-time Polling: ឆែក Status ពី Server
            startPolling() {
                this.isPolling = true;
                setInterval(async () => {
                    try {
                        const response = await fetch("{{ route('pos.products.status') }}");
                        if (response.ok) {
                            const data = await response.json();
                            data.forEach(updatedProd => {
                                const localProd = this.products.find(p => p.id == updatedProd.id);
                                if (localProd) {
                                    localProd.is_active = updatedProd.is_active;
                                    localProd.price = updatedProd.price;
                                }
                            });
                        }
                    } catch (e) {
                        console.error("Polling error:", e);
                    }
                }, 5000);
            },

            // Function ដើម្បី Force Check ភ្លាមៗ (ហៅពេល Error)
            async checkProductStatusNow() {
                try {
                    const response = await fetch("{{ route('pos.products.status') }}");
                    if (response.ok) {
                        const data = await response.json();
                        data.forEach(updatedProd => {
                            const localProd = this.products.find(p => p.id == updatedProd.id);
                            if (localProd) {
                                localProd.is_active = updatedProd.is_active;
                                localProd.price = updatedProd.price;
                            }
                        });
                    }
                } catch (e) { console.error(e); }
            },

            get filteredProducts() {
                let items = this.products;
                if (this.activeCategory !== 'all') items = items.filter(p => p.category_id == this.activeCategory);
                if (this.search) items = items.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                return items;
            },

            get availableAddons() {
                if (!this.tempItem.id) return [];
                const product = this.products.find(p => p.id == this.tempItem.id);
                if (product) {
                    if (product.addons && product.addons.length > 0) {
                        return product.addons; 
                    }
                    return []; 
                }
                return [];
            },

            get cartTotalQty() { return this.cart.reduce((sum, item) => sum + parseInt(item.qty), 0); },
            get cartTotalPrice() {
                return this.cart.reduce((sum, item) => {
                    if (item.total_price_calculated) return sum + item.total_price_calculated;
                    let itemTotal = item.base_price * item.qty;
                    let addonsTotal = 0;
                    if(item.addons) item.addons.forEach(ad => addonsTotal += (ad.price * (ad.qty || 1)));
                    return sum + itemTotal + addonsTotal;
                }, 0);
            },
            isAddonSelected(id) { return this.tempItem.selectedAddons.some(a => a.id === id); },
            getAddonQty(id) { const addon = this.tempItem.selectedAddons.find(a => a.id === id); return addon ? addon.qty : 0; },
            toggleAddon(addon) {
                const index = this.tempItem.selectedAddons.findIndex(a => a.id === addon.id);
                if (index > -1) this.tempItem.selectedAddons.splice(index, 1);
                else this.tempItem.selectedAddons.push({ id: addon.id, name: addon.name, price: parseFloat(addon.price), qty: 1 });
            },
            updateAddonQty(id, change) {
                const addon = this.tempItem.selectedAddons.find(a => a.id === id);
                if (addon) {
                    addon.qty += change;
                    if (addon.qty <= 0) this.toggleAddon({ id: id }); 
                }
            },
            calculateItemTotal() {
                let mainTotal = parseFloat(this.tempItem.base_price) * parseInt(this.tempItem.qty);
                let addonsTotal = 0;
                this.tempItem.selectedAddons.forEach(ad => { addonsTotal += (ad.price * ad.qty); });
                return mainTotal + addonsTotal;
            },
            openProductModal(product) {
                if(!product.is_active) return;
                this.tempItem = {
                    id: product.id, name: product.name, image: product.image,
                    base_price: parseFloat(product.price), qty: 1, note: '', selectedAddons: [], category_id: product.category_id
                };
                this.isProductModalOpen = true;
            },
            closeProductModal() { this.isProductModalOpen = false; },
            addToCart() {
                try {
                    const finalAddons = this.tempItem.selectedAddons.map(ad => ({
                        id: ad.id, name: ad.name, price: ad.price, qty: ad.qty
                    }));
                    const totalForCart = this.calculateItemTotal(); 
                    this.cart.push({
                        product_id: this.tempItem.id, name: this.tempItem.name,
                        base_price: parseFloat(this.tempItem.base_price), qty: parseInt(this.tempItem.qty),
                        note: this.tempItem.note, addons: finalAddons, total_price_calculated: totalForCart 
                    });
                    this.closeProductModal();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Added to cart' } }));
                } catch (e) { console.error(e); }
            },
            removeFromCart(index) { this.cart.splice(index, 1); if(this.cart.length === 0) this.isCartOpen = false; },
            
            openQuickAddon() {
                let extraProduct = this.products.find(p => p.name.toLowerCase().includes('extra') || p.name.toLowerCase().includes('addon') || p.price == 0);
                if (!extraProduct) {
                    extraProduct = this.products.filter(p => p.is_active).sort((a,b) => a.price - b.price)[0];
                    if(!extraProduct) return alert("No products available");
                }
                this.tempItem = {
                    id: extraProduct.id, name: "Extra / Addon Only", image: null, base_price: 0, qty: 1, note: 'Addon Only', selectedAddons: [], category_id: extraProduct.category_id
                };
                this.isProductModalOpen = true;
            },

            async submitOrder() {
                if (this.cart.length === 0) return;
                this.isSubmitting = true;
                const payload = {
                    table_id: {{ $table->id }},
                    items: this.cart.map(item => ({
                        product_id: item.product_id, qty: item.qty, price: item.base_price, note: item.note, addons: item.addons 
                    }))
                };

                try {
                    const response = await fetch("{{ route('pos.order.store') }}", {
                        method: "POST",
                        headers: { 
                            "Content-Type": "application/json", 
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    // 🛑 1. Handle Out of Stock
                    if (response.status === 422 && data.status === 'out_of_stock') {
                        
                        const removedItems = data.out_of_stock_items;
                        const removedIds = removedItems.map(i => i.id);
                        const removedNames = removedItems.map(i => i.name).join(', ');

                        // Filter Cart: Keep only items NOT in removedIds
                        this.cart = this.cart.filter(cartItem => !removedIds.includes(cartItem.product_id));

                        // Show Toast Error
                        window.dispatchEvent(new CustomEvent('notify', { 
                            detail: { type: 'error', message: `Removed "${removedNames}" as they are out of stock!` } 
                        }));

                        if (this.cart.length === 0) {
                            this.isCartOpen = false;
                        } 
                        
                        // Force Refresh UI
                        this.checkProductStatusNow();
                    } 
                    // 🛑 2. Other Errors
                    else if (!response.ok) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || "Error processing order" } }));
                    } 
                    // ✅ 3. Success
                    else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Order sent successfully!' } }));
                        this.cart = []; 
                        this.isCartOpen = false; 
                        window.location.href = "{{ route('pos.tables') }}";
                    }

                } catch (e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "Network Error" } }));
                } finally { 
                    this.isSubmitting = false; 
                }
            }
        }
    }
</script>
@endsection