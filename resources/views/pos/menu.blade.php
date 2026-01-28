@extends('layouts.blank')

@section('content')
{{-- 
    CONTAINER: ប្រើ h-screen ដើម្បីអោយពេញអេក្រង់ និងការពារការ Scroll របស់ Page ធំ
    bg-gray-50/dark:bg-gray-900: ពណ៌ផ្ទៃខាងក្រោយស្រាល
--}}
<div class="h-[calc(100vh-60px)] md:h-screen w-full bg-gray-50 dark:bg-gray-900 flex flex-col relative overflow-hidden font-sans" x-data="posMenu()">
    
    {{-- =========================================== --}}
    {{-- 1. STICKY HEADER (Glass Effect)             --}}
    {{-- =========================================== --}}
    <div class="z-30 shrink-0 bg-white/80 dark:bg-gray-800/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0">
        <div class="px-4 py-3 flex items-center justify-between gap-4">
            {{-- Back Button & Table Info --}}
            <div class="flex items-center gap-3 overflow-hidden">
                <a href="{{ route('pos.tables') }}" class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 hover:scale-105 transition-all duration-200">
                    <i class="ri-arrow-left-line text-xl"></i>
                </a>
                
                <div class="flex flex-col min-w-0">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white truncate leading-tight">{{ $table->name }}</h2>
                    <div class="flex items-center gap-2 text-xs font-medium">
                        @if($currentOrder) 
                            <span class="bg-blue-100 text-blue-600 px-2 py-0.5 rounded-md">#{{ $currentOrder->id }}</span>
                        @else
                            <span class="bg-green-100 text-green-600 px-2 py-0.5 rounded-md">{{ __('messages.new_order') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Search Trigger --}}
            <button @click="isSearchOpen = !isSearchOpen" 
                    class="flex-shrink-0 w-10 h-10 rounded-full border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                    :class="isSearchOpen ? 'bg-primary text-white border-primary ring-2 ring-primary/30' : 'bg-white dark:bg-gray-800'">
                <i class="ri-search-line text-xl" :class="isSearchOpen ? 'text-white' : ''"></i>
            </button>
        </div>

        {{-- Search Bar (Smooth Collapse) --}}
        <div x-show="isSearchOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="px-4 pb-3">
            <div class="relative">
                <i class="ri-search-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="search" placeholder="{{ __('messages.search_placeholder') }}..." 
                       class="w-full pl-11 pr-4 py-2.5 rounded-xl border-0 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary focus:bg-white dark:focus:bg-gray-800 transition-all duration-200 shadow-inner">
            </div>
        </div>

        {{-- 2. CATEGORY TABS (Pill Style) --}}
        <div class="px-4 pb-3 overflow-x-auto no-scrollbar flex gap-3 snap-x">
            <button @click="activeCategory = 'all'" 
                    class="snap-start flex-shrink-0 px-5 py-2 rounded-full text-sm font-bold transition-all duration-300 border shadow-sm select-none"
                    :class="activeCategory === 'all' 
                        ? 'bg-primary border-primary text-white shadow-primary/30 scale-105' 
                        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'">
                All
            </button>
            @foreach($categories as $cat)
                <button @click="activeCategory = {{ $cat->id }}" 
                        class="snap-start flex-shrink-0 px-5 py-2 rounded-full text-sm font-bold transition-all duration-300 border shadow-sm select-none"
                        :class="activeCategory === {{ $cat->id }} 
                            ? 'bg-primary border-primary text-white shadow-primary/30 scale-105' 
                            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 3. PRODUCT GRID (Scrollable Area)            --}}
    {{-- =========================================== --}}
    <div class="flex-1 overflow-y-auto overflow-x-hidden p-4 pb-32 custom-scrollbar">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="openProductModal(product)" 
                     class="group bg-white dark:bg-gray-800 rounded-2xl p-2.5 shadow-sm hover:shadow-lg border border-gray-100 dark:border-gray-700 cursor-pointer transition-all duration-300 hover:-translate-y-1 active:scale-95">
                    
                    {{-- Image Container --}}
                    <div class="aspect-square rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-700 relative mb-3">
                        <template x-if="product.image">
                            <img :src="'/storage/' + product.image" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        </template>
                        <template x-if="!product.image">
                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-600">
                                <i class="ri-image-2-line text-3xl mb-1"></i>
                                <span class="text-[10px] uppercase font-bold tracking-widest">No Image</span>
                            </div>
                        </template>

                        {{-- Floating Add Icon --}}
                        <div class="absolute bottom-2 right-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full w-8 h-8 flex items-center justify-center shadow-md text-primary opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                            <i class="ri-add-line font-bold text-lg"></i>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="px-1">
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm leading-snug line-clamp-2 min-h-[2.5em]" x-text="product.name"></h3>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-primary font-black text-base" x-text="'$' + parseFloat(product.price).toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="filteredProducts.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 text-gray-400">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <i class="ri-search-2-line text-3xl"></i>
                </div>
                <p class="font-medium">No products found matching your criteria.</p>
            </div>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- 4. FLOATING CART BAR (Glassmorphism)        --}}
    {{-- =========================================== --}}
    <div x-show="cart.length > 0" 
         x-transition:enter="transition cubic-bezier(0.175, 0.885, 0.32, 1.275) duration-400"
         x-transition:enter-start="translate-y-24 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="translate-y-24 opacity-0 scale-95"
         class="absolute bottom-6 left-4 right-4 md:left-1/2 md:-translate-x-1/2 md:w-96 z-40">
        
        <button @click="isCartOpen = true" 
                class="w-full bg-gray-900/90 dark:bg-primary/90 backdrop-blur-md text-white p-2 pr-4 pl-2 rounded-[20px] shadow-2xl shadow-gray-400/50 dark:shadow-black/50 border border-white/10 flex items-center justify-between group hover:scale-[1.02] transition-transform duration-200">
            
            <div class="flex items-center gap-3">
                <div class="bg-white text-gray-900 w-12 h-12 rounded-2xl flex items-center justify-center font-black text-lg shadow-sm" x-text="cartTotalQty"></div>
                <div class="flex flex-col items-start">
                    <span class="text-xs text-gray-300 uppercase tracking-wider font-semibold">Total</span>
                    <span class="font-bold text-xl" x-text="'$' + cartTotalPrice.toFixed(2)"></span>
                </div>
            </div>
            
            <div class="flex items-center gap-2 pr-2">
                <span class="font-bold text-sm">View Cart</span>
                <i class="ri-arrow-right-line bg-white/20 rounded-full p-1 transition-transform group-hover:translate-x-1"></i>
            </div>
        </button>
    </div>

    {{-- =========================================== --}}
    {{-- MODAL: PRODUCT DETAILS (Important Fixes)    --}}
    {{-- =========================================== --}}
    {{-- FIX: ប្រើ z-[100] ដើម្បីអោយប្រាកដថាវានៅលើ BottomNavigationBar --}}
    <div x-show="isProductModalOpen" class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-0 sm:p-4" style="display: none;" x-cloak>
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-[3px] transition-opacity duration-300" 
             x-show="isProductModalOpen"
             x-transition:enter="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="opacity-100" x-transition:leave-end="opacity-0"
             @click="closeProductModal()"></div>

        {{-- Modal Content --}}
        <div class="relative w-full sm:max-w-md bg-white dark:bg-gray-800 sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
             x-show="isProductModalOpen"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-500" 
             x-transition:enter-start="translate-y-full sm:translate-y-10 sm:opacity-0 scale-95" 
             x-transition:enter-end="translate-y-0 sm:translate-y-0 sm:opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-y-0 opacity-100 scale-100"
             x-transition:leave-end="translate-y-full opacity-0 scale-95">
            
            {{-- Image Header --}}
            <div class="relative h-56 bg-gray-100 dark:bg-gray-700 shrink-0 group">
                <template x-if="tempItem.image">
                    <img :src="'/storage/' + tempItem.image" class="w-full h-full object-cover">
                </template>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                
                <button @click="closeProductModal()" class="absolute top-4 right-4 bg-black/20 text-white rounded-full p-2 hover:bg-black/40 backdrop-blur-md transition-colors">
                    <i class="ri-close-line text-2xl"></i>
                </button>

                <div class="absolute bottom-4 left-6 right-6">
                    <h2 class="text-2xl font-bold text-white leading-tight shadow-black drop-shadow-md" x-text="tempItem.name"></h2>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                {{-- Price & Qty --}}
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Base Price</p>
                        <p class="text-3xl font-black text-primary" x-text="'$' + parseFloat(tempItem.base_price).toFixed(2)"></p>
                    </div>
                    
                    <div class="flex items-center gap-4 bg-gray-100 dark:bg-gray-700 rounded-2xl p-1.5 shadow-inner">
                        <button @click="if(tempItem.qty > 1) tempItem.qty--" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-800 dark:text-white hover:text-primary active:scale-90 transition-all">
                            <i class="ri-subtract-line font-bold"></i>
                        </button>
                        <span class="font-bold text-xl min-w-[1.5em] text-center text-gray-800 dark:text-white" x-text="tempItem.qty"></span>
                        <button @click="tempItem.qty++" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-gray-800 dark:text-white hover:text-primary active:scale-90 transition-all">
                            <i class="ri-add-line font-bold"></i>
                        </button>
                    </div>
                </div>

                {{-- Addons --}}
                <div class="mb-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ __('messages.addon_list') }}</h3>
                    <div class="space-y-2.5">
                        <template x-for="addon in availableAddons" :key="addon.id">
                            <label class="flex items-center justify-between p-3.5 rounded-2xl border cursor-pointer transition-all duration-200 group"
                                   :class="tempItem.selectedAddons.includes(addon.id) 
                                       ? 'border-primary bg-primary/5 shadow-inner' 
                                       : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex items-center">
                                        {{-- Fix: Checkbox Value --}}
                                        <input type="checkbox" :value="addon.id" x-model="tempItem.selectedAddons" 
                                               class="peer appearance-none w-5 h-5 rounded border border-gray-300 checked:bg-primary checked:border-primary transition-colors">
                                        <i class="ri-check-line absolute inset-0 text-white opacity-0 peer-checked:opacity-100 flex items-center justify-center text-sm pointer-events-none"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 group-hover:text-primary transition-colors" x-text="addon.name"></span>
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="'+$' + parseFloat(addon.price).toFixed(2)"></span>
                            </label>
                        </template>
                        <div x-show="availableAddons.length === 0" class="text-sm text-gray-400 italic text-center py-2 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">No addons available.</div>
                    </div>
                </div>

                {{-- Note --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Note</label>
                    <textarea x-model="tempItem.note" rows="2" 
                              class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none transition-all resize-none text-sm" 
                              placeholder="Ex: Less sugar, No spicy..."></textarea>
                </div>
            </div>

            {{-- Footer (Fixed at bottom of Modal) --}}
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 pb-8 sm:pb-4 shadow-[0_-5px_15px_rgba(0,0,0,0.02)] z-20">
                <button @click="addToCart()" type="button"
                        class="w-full bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:brightness-110 active:scale-[0.98] transition-all flex justify-between px-6 shadow-lg shadow-primary/30 relative overflow-hidden">
                    <span class="relative z-10">Add to Order</span>
                    <div class="absolute inset-0 bg-white/20 translate-y-full hover:translate-y-0 transition-transform duration-300"></div>
                    <span class="relative z-10" x-text="'$' + calculateItemTotal().toFixed(2)"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- =========================================== --}}
    {{-- MODAL: CART SUMMARY (Slide Over)            --}}
    {{-- =========================================== --}}
    {{-- FIX: z-[100] ដូចគ្នា --}}
    <div x-show="isCartOpen" class="fixed inset-0 z-[100] flex justify-end" style="display: none;" x-cloak>
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity duration-300" 
             x-show="isCartOpen"
             x-transition:enter="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="opacity-100" x-transition:leave-end="opacity-0"
             @click="isCartOpen = false"></div>

        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 h-full shadow-2xl flex flex-col transition-transform duration-300"
             x-show="isCartOpen"
             x-transition:enter="translate-x-full" 
             x-transition:enter-end="translate-x-0"
             x-transition:leave="translate-x-full">
            
            {{-- Header --}}
            <div class="p-6 pb-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-white dark:bg-gray-800 z-10">
                <div>
                    <h2 class="text-2xl font-black text-gray-800 dark:text-white">Current Order</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1">Table: {{ $table->name }}</p>
                </div>
                <button @click="isCartOpen = false" class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-600 dark:text-white flex items-center justify-center transition-colors">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-900 custom-scrollbar">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex gap-4 group relative overflow-hidden">
                        
                        {{-- Qty Controls --}}
                        <div class="flex flex-col items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-xl w-10 py-1 shrink-0">
                            <button @click="item.qty++;" class="w-full h-8 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-primary transition"><i class="ri-add-line text-sm"></i></button>
                            <span class="font-bold text-sm text-gray-800 dark:text-white" x-text="item.qty"></span>
                            <button @click="if(item.qty > 1) item.qty--; else removeFromCart(index)" 
                                    class="w-full h-8 flex items-center justify-center transition"
                                    :class="item.qty === 1 ? 'text-red-500 hover:bg-red-50' : 'text-gray-600 dark:text-gray-300 hover:text-primary'">
                                <i :class="item.qty === 1 ? 'ri-delete-bin-line' : 'ri-subtract-line'" class="text-sm"></i>
                            </button>
                        </div>

                        {{-- Item Info --}}
                        <div class="flex-1 min-w-0 py-1">
                            <div class="flex justify-between items-start gap-2">
                                <h4 class="font-bold text-gray-800 dark:text-white leading-tight" x-text="item.name"></h4>
                                <span class="font-bold text-primary whitespace-nowrap" x-text="'$' + (item.total_price * item.qty).toFixed(2)"></span>
                            </div>
                            
                            {{-- Addons --}}
                            <template x-if="item.addons && item.addons.length > 0">
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <template x-for="ad in item.addons">
                                        <span class="text-[10px] bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800" x-text="'+ ' + ad.name"></span>
                                    </template>
                                </div>
                            </template>

                            {{-- Note --}}
                            <template x-if="item.note">
                                <div class="flex items-start gap-1 mt-1.5">
                                    <i class="ri-sticky-note-line text-orange-400 text-xs mt-0.5"></i>
                                    <p class="text-xs text-gray-500 italic line-clamp-2" x-text="item.note"></p>
                                </div>
                            </template>
                        </div>
                        
                        {{-- Delete Swipe/Button --}}
                        <button @click="removeFromCart(index)" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 p-1 rounded transition-colors opacity-0 group-hover:opacity-100">
                            <i class="ri-close-circle-fill text-xl"></i>
                        </button>
                    </div>
                </template>

                 {{-- Empty Cart State --}}
                 <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 opacity-50">
                    <i class="ri-shopping-cart-2-line text-6xl mb-2"></i>
                    <p>Your cart is empty</p>
                </div>
            </div>

            {{-- Checkout Footer --}}
            <div class="p-6 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] z-20">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-gray-500 font-medium">Subtotal</span>
                    <span class="text-2xl font-black text-gray-800 dark:text-white" x-text="'$' + cartTotalPrice.toFixed(2)"></span>
                </div>
                
                <button @click="submitOrder" 
                        :disabled="isSubmitting || cart.length === 0" 
                        class="w-full bg-gray-900 dark:bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-xl hover:-translate-y-1 active:scale-[0.98] active:translate-y-0 transition-all flex justify-center items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i x-show="isSubmitting" class="ri-loader-4-line animate-spin text-xl"></i>
                    <span x-text="isSubmitting ? 'Processing...' : 'Send Order to Kitchen'"></span>
                    <i x-show="!isSubmitting" class="ri-send-plane-fill"></i>
                </button>
            </div>
        </div>
    </div>

</div>

{{-- SCRIPT: ប្រើ Logic ដែលមានសុវត្ថិភាពជាងមុន (parseInt/parseFloat) --}}
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
                
                // 1. Filter by Category (Safe Comparison)
                if (this.activeCategory !== 'all') {
                    items = items.filter(p => p.category_id == this.activeCategory);
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
                const product = this.products.find(p => p.id == this.tempItem.id);
                if(product && product.category) {
                    const type = product.category.type;
                    return this.addons.filter(a => a.type == type);
                }
                return this.addons;
            },

            get cartTotalQty() {
                return this.cart.reduce((sum, item) => sum + parseInt(item.qty), 0);
            },

            get cartTotalPrice() {
                return this.cart.reduce((sum, item) => sum + (parseFloat(item.total_price) * parseInt(item.qty)), 0);
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
                    selectedAddons: [], 
                    category_id: product.category_id
                };
                this.isProductModalOpen = true;
            },

            closeProductModal() {
                this.isProductModalOpen = false;
            },

            calculateItemTotal() {
                let total = parseFloat(this.tempItem.base_price);
                
                if (this.tempItem.selectedAddons.length > 0) {
                    this.tempItem.selectedAddons.forEach(addonId => {
                        const addon = this.addons.find(a => a.id == addonId);
                        if(addon) total += parseFloat(addon.price);
                    });
                }
                return total * parseInt(this.tempItem.qty);
            },

            addToCart() {
                try {
                    let finalAddons = [];
                    let addonsTotal = 0;

                    if (this.tempItem.selectedAddons.length > 0) {
                        finalAddons = this.tempItem.selectedAddons.map(id => {
                            const a = this.addons.find(x => x.id == id);
                            if(a) {
                                addonsTotal += parseFloat(a.price);
                                return { id: a.id, name: a.name, price: parseFloat(a.price) };
                            }
                            return null;
                        }).filter(a => a);
                    }

                    const unitPrice = parseFloat(this.tempItem.base_price) + addonsTotal;

                    const cartItem = {
                        product_id: this.tempItem.id,
                        name: this.tempItem.name,
                        base_price: parseFloat(this.tempItem.base_price),
                        total_price: unitPrice, 
                        qty: parseInt(this.tempItem.qty),
                        note: this.tempItem.note,
                        addons: finalAddons
                    };

                    this.cart.push(cartItem);
                    this.closeProductModal();
                    
                    if(typeof window.dispatchEvent !== 'undefined') {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Added to cart' } }));
                    }

                } catch (e) {
                    console.error(e);
                    alert("Error adding to cart: " + e.message);
                }
            },

            removeFromCart(index) {
                this.cart.splice(index, 1);
                if(this.cart.length === 0) this.isCartOpen = false;
            },

            async submitOrder() {
                if (this.cart.length === 0) return;
                this.isSubmitting = true;

                const payload = {
                    table_id: {{ $table->id }},
                    items: this.cart.map(item => ({
                        product_id: item.product_id,
                        qty: item.qty,
                        price: item.base_price, 
                        note: item.note,
                        addons: item.addons 
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

                    if (!response.ok) {
                         // Error Handling
                         console.error(data);
                         alert(data.message || "Server Error");
                    } else {
                        // Success
                        this.cart = []; 
                        this.isCartOpen = false; 
                        window.location.href = "{{ route('pos.tables') }}";
                    }
                } catch (e) {
                    console.error("Network Error:", e);
                    alert("Network Error");
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>
@endsection