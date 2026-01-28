@extends('admin.dashboard')

@section('content')
<div class="h-[calc(100vh-80px)] flex flex-col relative" x-data="posMenu()">
    
    {{-- 1. HEADER: Table Info & Search --}}
    <div class="flex items-center justify-between gap-3 mb-4 px-1">
        <a href="{{ route('pos.tables') }}" class="bg-gray-200 dark:bg-gray-700 p-2.5 rounded-xl text-text-color hover:bg-gray-300">
            <i class="ri-arrow-left-line text-xl"></i>
        </a>
        
        <div class="flex-1">
            <h2 class="text-lg font-bold text-text-color leading-tight">{{ $table->name }}</h2>
            <p class="text-xs text-secondary">
                @if($currentOrder) 
                    <span class="text-blue-500 font-bold">#Order-{{ $currentOrder->id }}</span>
                @else
                    <span class="text-green-500">{{ __('messages.new_order') }}</span>
                @endif
            </p>
        </div>

        {{-- Search Icon --}}
        <div class="relative">
            <button @click="isSearchOpen = !isSearchOpen" class="p-2.5 rounded-xl bg-card-bg border border-input-border text-text-color">
                <i class="ri-search-line text-xl"></i>
            </button>
        </div>
    </div>

    {{-- Search Bar (Toggle) --}}
    <div x-show="isSearchOpen" x-transition class="mb-4">
        <input type="text" x-model="search" placeholder="{{ __('messages.search_placeholder') }}" 
               class="w-full px-4 py-3 rounded-xl border border-input-border bg-card-bg text-text-color outline-none focus:ring-2 focus:ring-primary">
    </div>

    {{-- 2. CATEGORY TABS (Horizontal Scroll) --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-2 no-scrollbar">
        <button @click="activeCategory = 'all'" 
                class="whitespace-nowrap px-5 py-2 rounded-full text-sm font-bold transition-all border"
                :class="activeCategory === 'all' ? 'bg-primary text-white border-primary' : 'bg-card-bg text-text-color border-input-border'">
            All Items
        </button>
        @foreach($categories as $cat)
            <button @click="activeCategory = {{ $cat->id }}" 
                    class="whitespace-nowrap px-5 py-2 rounded-full text-sm font-bold transition-all border"
                    :class="activeCategory === {{ $cat->id }} ? 'bg-primary text-white border-primary' : 'bg-card-bg text-text-color border-input-border'">
                {{ $cat->name }}
            </button>
        @endforeach
    </div>

    {{-- 3. PRODUCT GRID --}}
    <div class="flex-1 overflow-y-auto pb-24 custom-scrollbar pr-1">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="openProductModal(product)" 
                     class="bg-card-bg rounded-2xl p-3 shadow-sm border border-border-color cursor-pointer active:scale-95 transition-transform relative overflow-hidden group">
                    
                    {{-- Image --}}
                    <div class="aspect-square rounded-xl bg-gray-100 overflow-hidden mb-2 relative">
                        <template x-if="product.image">
                            <img :src="'/storage/' + product.image" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!product.image">
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <i class="ri-image-line text-3xl"></i>
                            </div>
                        </template>
                        
                        {{-- Add Button Overlay --}}
                        <div class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="bg-white rounded-full p-2 text-primary shadow-lg">
                                <i class="ri-add-line text-xl font-bold"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Info --}}
                    <h3 class="font-bold text-text-color text-sm line-clamp-1" x-text="product.name"></h3>
                    <p class="text-primary font-bold mt-1" x-text="'$' + parseFloat(product.price).toFixed(2)"></p>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="filteredProducts.length === 0" class="col-span-full flex flex-col items-center justify-center py-10 text-secondary">
                <i class="ri-inbox-line text-4xl mb-2"></i>
                <p>No products found.</p>
            </div>
        </div>
    </div>

    {{-- 4. FLOATING CART BAR (Bottom Sheet Trigger) --}}
    <div x-show="cart.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="fixed bottom-4 left-4 right-4 md:left-[calc(50%-10rem)] md:right-auto md:w-80 z-40">
        
        <button @click="isCartOpen = true" 
                class="w-full bg-primary text-white p-4 rounded-2xl shadow-xl shadow-primary/30 flex justify-between items-center hover:bg-opacity-90 transition-all">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm" x-text="cartTotalQty"></div>
                <span class="font-bold text-lg">View Cart</span>
            </div>
            <span class="font-bold text-xl" x-text="'$' + cartTotalPrice.toFixed(2)"></span>
        </button>
    </div>

    {{-- =========================================== --}}
    {{-- MODAL: PRODUCT DETAILS (Addon & Note)       --}}
    {{-- =========================================== --}}
    <div x-show="isProductModalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:px-4" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeProductModal()"></div>

        <div class="relative w-full sm:max-w-md bg-card-bg sm:rounded-2xl rounded-t-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="translate-y-full sm:translate-y-10 opacity-0" 
             x-transition:enter-end="translate-y-0 opacity-100">
            
            {{-- Image Header --}}
            <div class="relative h-48 bg-gray-100">
                <template x-if="tempItem.image">
                    <img :src="'/storage/' + tempItem.image" class="w-full h-full object-cover">
                </template>
                <button @click="closeProductModal()" class="absolute top-4 right-4 bg-black/30 text-white rounded-full p-2 hover:bg-black/50 backdrop-blur-md">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <div class="p-6 flex-1 overflow-y-auto">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-text-color" x-text="tempItem.name"></h2>
                        <p class="text-primary font-bold text-lg" x-text="'$' + parseFloat(tempItem.base_price).toFixed(2)"></p>
                    </div>
                    
                    {{-- Quantity Stepper --}}
                    <div class="flex items-center gap-3 bg-page-bg rounded-lg p-1">
                        <button @click="if(tempItem.qty > 1) tempItem.qty--" class="w-8 h-8 rounded-md bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center text-text-color hover:text-primary transition">
                            <i class="ri-subtract-line"></i>
                        </button>
                        <span class="font-bold w-6 text-center text-text-color" x-text="tempItem.qty"></span>
                        <button @click="tempItem.qty++" class="w-8 h-8 rounded-md bg-white dark:bg-gray-700 shadow-sm flex items-center justify-center text-text-color hover:text-primary transition">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                </div>

                {{-- Addons --}}
                <div class="mb-6">
                    <h3 class="text-sm font-bold text-secondary uppercase tracking-wider mb-3">{{ __('messages.addon_list') }}</h3>
                    <div class="space-y-2">
                        <template x-for="addon in availableAddons" :key="addon.id">
                            <label class="flex items-center justify-between p-3 rounded-xl border border-input-border cursor-pointer transition-all hover:bg-page-bg"
                                   :class="tempItem.selectedAddons.includes(addon.id) ? 'border-primary bg-primary/5' : ''">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" :value="addon.id" x-model="tempItem.selectedAddons" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm font-medium text-text-color" x-text="addon.name"></span>
                                </div>
                                <span class="text-sm font-bold text-text-color" x-text="'+$' + parseFloat(addon.price).toFixed(2)"></span>
                            </label>
                        </template>
                        <div x-show="availableAddons.length === 0" class="text-sm text-secondary italic">No addons available for this item.</div>
                    </div>
                </div>

                {{-- Note --}}
                <div>
                    <label class="block text-sm font-bold text-secondary uppercase tracking-wider mb-2">Note (Optional)</label>
                    <textarea x-model="tempItem.note" rows="2" class="w-full px-4 py-3 rounded-xl border border-input-border bg-page-bg text-text-color focus:ring-2 focus:ring-primary outline-none text-sm" placeholder="Ex: Less sugar, No spicy..."></textarea>
                </div>
            </div>

            {{-- Footer Total & Add --}}
            <div class="p-4 border-t border-border-color bg-card-bg pb-8 sm:pb-4">
                <button @click="addToCart()" class="w-full bg-primary text-white py-3.5 rounded-xl font-bold text-lg hover:opacity-90 transition-all flex justify-between px-6 shadow-lg shadow-primary/20">
                    <span>Add to Order</span>
                    <span x-text="'$' + calculateItemTotal().toFixed(2)"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- MODAL: CART SUMMARY (Checkout)              --}}
    {{-- =========================================== --}}
    <div x-show="isCartOpen" class="fixed inset-0 z-50 flex justify-end" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="isCartOpen = false"></div>

        <div class="relative w-full max-w-md bg-card-bg h-full shadow-2xl flex flex-col transition-transform duration-300"
             x-transition:enter="translate-x-full" 
             x-transition:enter-end="translate-x-0"
             x-transition:leave="translate-x-full">
            
            <div class="p-5 border-b border-border-color flex justify-between items-center bg-page-bg/50">
                <h2 class="text-xl font-bold text-text-color">Current Order</h2>
                <button @click="isCartOpen = false" class="text-secondary hover:text-text-color p-2"><i class="ri-close-line text-2xl"></i></button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="flex gap-3 relative group">
                        {{-- Qty Control --}}
                        <div class="flex flex-col items-center justify-between bg-page-bg rounded-lg py-1 px-1 h-20">
                            <button @click="item.qty++;" class="text-text-color hover:text-primary"><i class="ri-add-line"></i></button>
                            <span class="font-bold text-sm" x-text="item.qty"></span>
                            <button @click="if(item.qty > 1) item.qty--; else removeFromCart(index)" class="text-text-color hover:text-red-500"><i class="ri-subtract-line"></i></button>
                        </div>

                        <div class="flex-1 border-b border-border-color pb-4">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-text-color" x-text="item.name"></h4>
                                <span class="font-bold text-text-color" x-text="'$' + (item.total_price * item.qty).toFixed(2)"></span>
                            </div>
                            
                            {{-- Show Addons --}}
                            <template x-if="item.addons && item.addons.length > 0">
                                <div class="text-xs text-secondary mt-1 flex flex-wrap gap-1">
                                    <template x-for="ad in item.addons">
                                        <span class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded" x-text="'+ ' + ad.name"></span>
                                    </template>
                                </div>
                            </template>

                            {{-- Show Note --}}
                            <template x-if="item.note">
                                <p class="text-xs text-orange-500 mt-1 italic" x-text="'Note: ' + item.note"></p>
                            </template>

                            <button @click="removeFromCart(index)" class="absolute top-0 right-0 -mr-2 -mt-2 text-red-500 opacity-0 group-hover:opacity-100 transition-opacity p-2">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-6 border-t border-border-color bg-page-bg/30">
                <div class="flex justify-between items-center mb-4 text-xl font-bold text-text-color">
                    <span>Total</span>
                    <span x-text="'$' + cartTotalPrice.toFixed(2)"></span>
                </div>
                <button @click="submitOrder" :disabled="isSubmitting" class="w-full bg-primary text-white py-4 rounded-xl font-bold text-lg hover:opacity-90 shadow-lg shadow-primary/20 flex justify-center items-center gap-2">
                    <i x-show="isSubmitting" class="ri-loader-4-line animate-spin"></i>
                    <span>Send Order</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    function posMenu() {
        return {
            // Data
            products: @json($products),
            categories: @json($categories),
            addons: @json($addons),
            
            // State
            activeCategory: 'all',
            search: '',
            isSearchOpen: false,
            
            // Cart & Modals
            cart: [],
            isCartOpen: false,
            isProductModalOpen: false,
            isSubmitting: false,

            // Temp Item (For Adding/Editing)
            tempItem: {
                id: null, name: '', image: null, base_price: 0, qty: 1, 
                note: '', selectedAddons: [], category_id: null
            },

            // Computed Properties Logic
            get filteredProducts() {
                let items = this.products;
                
                // 1. Filter by Category
                if (this.activeCategory !== 'all') {
                    // Filter items where product.category_id matches
                    items = items.filter(p => p.category_id === this.activeCategory);
                }

                // 2. Filter by Search
                if (this.search) {
                    const q = this.search.toLowerCase();
                    items = items.filter(p => p.name.toLowerCase().includes(q));
                }

                return items;
            },

            get availableAddons() {
                if (!this.tempItem.id) return [];
                // Find the product to get its category type (food/drink)
                // Or simply show all addons? Better to filter by type if possible.
                // Assuming addons have 'type' and Products have 'category.type'
                
                const product = this.products.find(p => p.id === this.tempItem.id);
                if(product && product.category) {
                    const type = product.category.type;
                    return this.addons.filter(a => a.type === type);
                }
                return this.addons;
            },

            get cartTotalQty() {
                return this.cart.reduce((sum, item) => sum + item.qty, 0);
            },

            get cartTotalPrice() {
                return this.cart.reduce((sum, item) => sum + (item.total_price * item.qty), 0);
            },

            // Methods
            openProductModal(product) {
                this.tempItem = {
                    id: product.id,
                    name: product.name,
                    image: product.image,
                    base_price: parseFloat(product.price),
                    qty: 1,
                    note: '',
                    selectedAddons: [], // IDs only
                    category_id: product.category_id
                };
                this.isProductModalOpen = true;
            },

            closeProductModal() {
                this.isProductModalOpen = false;
            },

            calculateItemTotal() {
                let total = this.tempItem.base_price;
                
                // Add Addon Prices
                this.tempItem.selectedAddons.forEach(addonId => {
                    const addon = this.addons.find(a => a.id === addonId);
                    if(addon) total += parseFloat(addon.price);
                });

                return total * this.tempItem.qty;
            },

            addToCart() {
                // Prepare Cart Item
                const finalAddons = this.tempItem.selectedAddons.map(id => {
                    const a = this.addons.find(x => x.id === id);
                    return { id: a.id, name: a.name, price: parseFloat(a.price) };
                });

                const unitPrice = this.tempItem.base_price + finalAddons.reduce((sum, a) => sum + a.price, 0);

                const cartItem = {
                    product_id: this.tempItem.id,
                    name: this.tempItem.name,
                    base_price: this.tempItem.base_price,
                    total_price: unitPrice, // Price per unit including addons
                    qty: this.tempItem.qty,
                    note: this.tempItem.note,
                    addons: finalAddons
                };

                this.cart.push(cartItem);
                this.closeProductModal();
                
                // Show notification
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Added to cart' } }));
            },

            removeFromCart(index) {
                this.cart.splice(index, 1);
                if(this.cart.length === 0) this.isCartOpen = false;
            },

            async submitOrder() {
    if (this.cart.length === 0) return;
    this.isSubmitting = true;

    // 1. រៀបចំទិន្នន័យ (Payload)
    const payload = {
        table_id: {{ $table->id }},
        items: this.cart.map(item => ({
            product_id: item.product_id,
            qty: item.qty,
            price: item.base_price, // តម្លៃដើម (Controller អាចយកទៅគណនាបន្ថែម)
            note: item.note,
            // យកតែ ID របស់ Addon ទៅបានហើយ (Backend នឹងទាញតម្លៃតាមក្រោយ)
            addons: item.addons 
        }))
    };

    try {
        // 2. បាញ់ទិន្នន័យទៅ Controller (Uncommented)
        const response = await fetch("{{ route('pos.order.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
            // ករណីមាន Error (Validation ឬ Server Error)
            if (response.status === 422) {
                console.error("Validation Error:", data.errors);
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { type: 'error', message: 'សូមពិនិត្យទិន្នន័យម្តងទៀត (Validation Error)' } 
                }));
            } else {
                console.error("Server Error:", data);
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { type: 'error', message: data.message || 'Server Error' } 
                }));
            }
        } else {
            // ករណីជោគជ័យ (Success)
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { type: 'success', message: 'ការកុម្ម៉ង់បានជោគជ័យ!' } 
            }));
            
            this.cart = []; // លុបកន្ត្រក
            this.isCartOpen = false; // បិទ Modal
            
            // Reload ដើម្បី Update Status តុ ឬ Redirect
            setTimeout(() => {
                window.location.href = "{{ route('pos.tables') }}";
            }, 1000);
        }

    } catch (e) {
        console.error("Network Error:", e);
        window.dispatchEvent(new CustomEvent('notify', { 
            detail: { type: 'error', message: 'បញ្ហាបណ្តាញ (Network Error)' } 
        }));
    } finally {
        this.isSubmitting = false;
    }
}
        }
    }
</script>
@endsection