<script>
    /**
     * =========================================================
     * 1. HEADER CONTROLLER
     * =========================================================
     */
    function headerController() {
        return {
            search: '',
            activeCategory: 'all', 
            isAddonMode: false,
            
            isExchangeModalOpen: false,
            exchangeRate: localStorage.getItem('pos_exchange_rate') || 4100,
            tempExchangeRate: 4100,
            isFetchingRate: false,

            init() {
                this.loadSystemRate();
                this.tempExchangeRate = this.exchangeRate;

                this.$watch('search', value => {
                    window.dispatchEvent(new CustomEvent('pos-search-changed', { detail: value }));
                });
            },

            toggleAddonMode() {
                this.isAddonMode = !this.isAddonMode;
                window.dispatchEvent(new CustomEvent('pos-toggle-addon-mode', { detail: this.isAddonMode }));
                
                if (!this.isAddonMode) {
                    this.search = '';
                    this.activeCategory = 'all';
                    window.dispatchEvent(new CustomEvent('pos-category-changed', { detail: 'all' }));
                }
            },

            async loadSystemRate() {
                try {
                    const response = await fetch("{{ route('system.exchange-rate.get') }}");
                    const data = await response.json();
                    if(data.rate) {
                        this.exchangeRate = parseFloat(data.rate);
                        this.tempExchangeRate = this.exchangeRate;
                        localStorage.setItem('pos_exchange_rate', this.exchangeRate);
                    }
                } catch (e) { console.error("Failed to load rate", e); }
            },

            openExchangeModal() {
                this.tempExchangeRate = this.exchangeRate;
                this.isExchangeModalOpen = true;
            },

            async saveExchangeRate() {
                if (this.tempExchangeRate > 0) {
                    try {
                        const response = await fetch("{{ route('system.exchange-rate.update') }}", {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ rate: this.tempExchangeRate })
                        });
                        
                        if(response.ok) {
                            this.exchangeRate = this.tempExchangeRate;
                            localStorage.setItem('pos_exchange_rate', this.exchangeRate);
                            this.isExchangeModalOpen = false;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.exchange_rate_updated') }}" } }));
                        } else {
                            throw new Error("{{ __('messages.update_failed') }}");
                        }
                    } catch (e) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.failed_save_rate') }}" } }));
                    }
                }
            },

            async fetchRateFromApi() {
                this.isFetchingRate = true;
                try {
                    const response = await fetch("{{ route('system.exchange-rate.fetch-nbc') }}");
                    const data = await response.json();

                    if (data.status === 'error') throw new Error(data.message);

                    let khrRate = 0;
                    if (data.data && typeof data.data === 'object' && !Array.isArray(data.data)) {
                        khrRate = parseFloat(data.data.average || data.data.ask || data.data.bid);
                    } else if (data.data && Array.isArray(data.data)) {
                        const usdItem = data.data.find(i => i.currency_id === 'USD' || i.symbol === 'USD/KHR');
                        if (usdItem) khrRate = parseFloat(usdItem.average || usdItem.ask || usdItem.bid);
                    }

                    if (khrRate > 0) {
                        this.tempExchangeRate = khrRate; 
                        await this.saveExchangeRate(); 
                    } else {
                        throw new Error("{{ __('messages.rate_not_found') }}");
                    }
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.api_error') }}" + error.message } }));
                } finally {
                    this.isFetchingRate = false;
                }
            }
        }
    }

    /**
     * =========================================================
     * 2. POS MENU CONTROLLER
     * =========================================================
     */
    function posMenu() {
        return {
            products: [],
            categories: @json($categories ?? []).sort((a, b) => a.name.localeCompare(b.name, 'km')),
            addons: [],
            
            activeCategory: 'all', 
            search: '',
            viewMode: 'menu', 
            
            // --- State សម្រាប់ Lazy Load / Pagination ---
            page: 1,
            hasMorePages: true,
            isLoadingMore: false,
            isLoading: true, 
            searchTimeout: null,
            scrollTimeout: null, // បន្ថែមដើម្បីទប់ស្កាត់ Scroll ជាន់គ្នា
            // ------------------------------------------

            cart: [],
            isCartOpen: false,
            isProductModalOpen: false,
            isSubmitting: false,
            isPolling: false,
            
            tempItem: { id: null, name: '', image: null, base_price: 0, qty: 1, note: '', selectedAddons: [], category_id: null },

            init() {
                this.fetchMenuData(); 
                this.loadProducts();  

                this.startPolling();
                
                // ចាប់ Event ស្វែងរក (Debounce)
                this.$watch('search', value => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.resetAndLoadProducts();
                    }, 500); 
                });

                window.addEventListener('pos-category-changed', (e) => { this.selectCategory(e.detail); });
                window.addEventListener('pos-search-changed', (e) => { this.search = e.detail; });
                window.addEventListener('pos-toggle-addon-mode', (e) => { this.viewMode = e.detail ? 'addon' : 'menu'; });
                window.addEventListener('pos-open-quick-addon', () => { this.openQuickAddon(); });
            },

            async fetchMenuData() {
                try {
                    const response = await fetch("{{ route('pos.menu.data') }}");
                    const data = await response.json();
                    this.addons = data.addons || [];
                } catch (error) { 
                    console.error("Failed to fetch addons data:", error); 
                }
            },

            // --- FUNCTIONS សម្រាប់ LAZY LOAD ---
            resetAndLoadProducts() {
                this.products = [];
                this.page = 1;
                this.hasMorePages = true;
                this.loadProducts();
            },

            async loadProducts() {
                // បើកំពុង Load ឬ អស់ទំព័រហើយ មិនបាច់ធ្វើការទេ
                if (!this.hasMorePages || this.isLoadingMore) return;

                if (this.page === 1) {
                    this.isLoading = true;
                } else {
                    this.isLoadingMore = true;
                }

                try {
                    let url = `{{ route('pos.products.paginated') }}?page=${this.page}&category_id=${this.activeCategory}`;
                    if (this.search) url += `&search=${this.search}`;

                    const response = await fetch(url);
                    const data = await response.json();
                    const incomingProducts = data.data || [];

                    if (this.page === 1) {
                        this.products = incomingProducts;
                    } else {
                        // ដំណោះស្រាយចម្បង: ទប់ស្កាត់ការបញ្ចូល Product ដែលមាន ID ជាន់គ្នា (Filter Duplicates)
                        const newItems = incomingProducts.filter(newItem => 
                            !this.products.some(existingItem => existingItem.id === newItem.id)
                        );
                        this.products = [...this.products, ...newItems];
                    }

                    // ឆែកមើលថាតើនៅសល់ Page បន្តទៀតឬអត់
                    this.hasMorePages = data.current_page < data.last_page;
                    
                    if (this.hasMorePages) {
                        this.page++;
                    }

                } catch (error) {
                    console.error("Load products error:", error);
                } finally {
                    this.isLoading = false;
                    this.isLoadingMore = false;
                }
            },

            handleScroll(e) {
                // ទប់ស្កាត់ Scroll Event កុំអោយចាប់យកញឹកញាប់ពេក (Throttle)
                if (this.scrollTimeout) return;

                this.scrollTimeout = setTimeout(() => {
                    const el = e.target;
                    if (el.scrollHeight - el.scrollTop <= el.clientHeight + 100) {
                        this.loadProducts();
                    }
                    this.scrollTimeout = null;
                }, 150); // រង់ចាំ 150ms រាល់ការអូស ទើបមិនគាំង
            },
            // --------------------------------------

            selectCategory(id) {
                this.activeCategory = id;
                this.search = ''; 
                this.resetAndLoadProducts(); 
            },

            formatNumber(num) {
                return new Intl.NumberFormat('km-KH').format(num);
            },

            // --- FILTER LOGIC ---
            get displayProducts() {
                if (this.viewMode === 'addon') {
                    let items = this.addons.filter(a => a.is_active == 1 || a.is_active == true);
                    if (this.search) {
                        items = items.filter(a => a.name.toLowerCase().includes(this.search.toLowerCase()));
                    }
                    if (this.activeCategory !== null && this.activeCategory !== 'all') {
                        const selectedCat = this.categories.find(c => c.id == this.activeCategory);
                        if (selectedCat && selectedCat.kitchen_destination_id) {
                            items = items.filter(a => a.kitchen_destination_id == selectedCat.kitchen_destination_id);
                        } else {
                            items = items.filter(a => !a.kitchen_destination_id); 
                        }
                    }
                    return items.map(addon => ({
                        id: addon.id, name: addon.name, price: addon.price,
                        image: null, category_id: 'addon', is_active: true, type: 'addon_item' 
                    }));
                }

                return this.products;
            },

            // --- ADDON & MODAL LOGIC ---
            get availableAddons() {
                if (this.tempItem.type === 'addon_item') return [];
                if (!this.tempItem.id) return [];
                if (this.tempItem.name === "Extra / Addon Only") return this.addons.filter(a => a.is_active);
                
                const product = this.products.find(p => p.id == this.tempItem.id);
                if (product && product.addons) return product.addons.filter(a => a.is_active);
                
                return [];
            },

            addStandaloneAddon(addonItem) {
                let wrapperProductId = 999999; 

                let cartItem = {
                    product_id: wrapperProductId, 
                    name: addonItem.name, 
                    image: null,
                    base_price: 0, 
                    qty: 1,
                    note: '',
                    is_addon_item: true, 
                    addons: [{ id: addonItem.id, name: addonItem.name, price: parseFloat(addonItem.price), qty: 1 }],
                    total_price_calculated: parseFloat(addonItem.price) 
                };
                this.cart.push(cartItem);
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.added_prefix') }}" + addonItem.name } }));
            },

            openProductModal(product) {
                if(!product.is_active) return;
                this.tempItem = {
                    id: product.id, name: product.name, image: product.image,
                    base_price: parseFloat(product.price), qty: 1, note: '', 
                    selectedAddons: [], category_id: product.category_id,
                    type: product.type || 'product',
                    category_name: (product.type === 'addon_item') ? "{{ __('messages.label_addon') }}" : (product.category ? product.category.name : "{{ __('messages.label_item') }}")
                };
                this.isProductModalOpen = true;
            },

            openQuickAddon() {
                this.tempItem = {
                    id: 999999, name: "Extra / Addon Only", image: null, base_price: 0, 
                    qty: 1, note: 'Addon Only', selectedAddons: [], category_id: null, category_name: "{{ __('messages.label_special') }}"
                };
                this.isProductModalOpen = true;
            },

            closeProductModal() { this.isProductModalOpen = false; },
            
            isAddonSelected(id) { return this.tempItem.selectedAddons.some(a => a.id === id); },
            getAddonQty(id) { const addon = this.tempItem.selectedAddons.find(a => a.id === id); return addon ? addon.qty : 0; },
            toggleAddon(addon) {
                const index = this.tempItem.selectedAddons.findIndex(a => a.id === addon.id);
                if (index > -1) this.tempItem.selectedAddons.splice(index, 1);
                else this.tempItem.selectedAddons.push({ id: addon.id, name: addon.name, price: parseFloat(addon.price), qty: 1 });
            },
            updateAddonQty(id, change) {
                const addon = this.tempItem.selectedAddons.find(a => a.id === id);
                if (addon) { addon.qty += change; if (addon.qty <= 0) this.toggleAddon({ id: id }); }
            },
            calculateItemTotal() {
                let main = parseFloat(this.tempItem.base_price) * parseInt(this.tempItem.qty);
                let ads = 0; this.tempItem.selectedAddons.forEach(ad => ads += (ad.price * ad.qty));
                return main + ads;
            },

            // --- CART LOGIC ---
            addToCart() {
                try {
                    const finalAddons = this.tempItem.selectedAddons.map(ad => ({
                        id: ad.id, name: ad.name, price: ad.price, qty: ad.qty
                    })).sort((a, b) => a.id - b.id);

                    const newItem = {
                        product_id: this.tempItem.id,
                        name: this.tempItem.name,
                        base_price: parseFloat(this.tempItem.base_price),
                        qty: parseInt(this.tempItem.qty),
                        note: this.tempItem.note || '', 
                        addons: finalAddons,
                        total_price_calculated: this.calculateItemTotal(),
                        is_addon_item: (this.tempItem.type === 'addon_item' || this.tempItem.id === 999999)
                    };

                    const existingIndex = this.cart.findIndex(item => {
                        return item.product_id === newItem.product_id && 
                            (item.note || '') === newItem.note &&
                            JSON.stringify(item.addons) === JSON.stringify(newItem.addons);
                    });

                    if (existingIndex !== -1) {
                        let existingItem = this.cart[existingIndex];
                        existingItem.qty += newItem.qty;
                        existingItem.total_price_calculated += newItem.total_price_calculated;
                        
                        if (existingItem.addons && existingItem.addons.length > 0) {
                            existingItem.addons.forEach((ad, i) => {
                                ad.qty += newItem.addons[i].qty;
                            });
                        }
                    } else {
                        this.cart.push(newItem);
                    }

                    this.closeProductModal();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.added_to_cart') }}" } }));
                } catch (e) { console.error(e); }
            },

            updateCartQty(index, change) {
                let item = this.cart[index];
                item.qty += change;
                if (item.qty <= 0) {
                    if(confirm("{{ __('messages.confirm_remove') }}")) {
                        this.removeFromCart(index);
                        return;
                    } else {
                        item.qty = 1;
                    }
                }
                this.recalculateCartItemTotal(index);
            },

            updateCartAddonQty(cartIndex, addonIndex, change) {
                let item = this.cart[cartIndex];
                let addon = item.addons[addonIndex];
                
                addon.qty += change;
                if (addon.qty <= 0) {
                    this.removeAddonFromCart(cartIndex, addonIndex);
                    return; 
                }
                this.recalculateCartItemTotal(cartIndex);
            },

            removeAddonFromCart(cartIndex, addonIndex) {
                this.cart[cartIndex].addons.splice(addonIndex, 1);
                this.recalculateCartItemTotal(cartIndex);
            },

            recalculateCartItemTotal(index) {
                let item = this.cart[index];
                let baseTotal = parseFloat(item.base_price) * parseInt(item.qty);
                let addonsTotal = 0;

                if (item.addons && item.addons.length > 0) {
                    item.addons.forEach(ad => { addonsTotal += (parseFloat(ad.price) * parseInt(ad.qty)); });
                }
                item.total_price_calculated = baseTotal + addonsTotal;
            },

            removeFromCart(index) { this.cart.splice(index, 1); if(this.cart.length === 0) this.isCartOpen = false; },
            
            get cartTotalPrice() {
                return this.cart.reduce((sum, item) => sum + (item.total_price_calculated || 0), 0);
            },

            // --- SUBMIT ORDER LOGIC ---
            async submitOrder() {
                if (this.cart.length === 0) return;
                this.isSubmitting = true;
                
                const payload = {
                    table_id: {{ $table->id }},
                    exchange_rate: localStorage.getItem('pos_exchange_rate') || 4100, 
                    items: this.cart.map(item => ({
                        product_id: item.product_id, 
                        qty: item.qty, 
                        price: item.base_price, 
                        note: item.note, 
                        addons: item.addons, 
                        is_addon: item.is_addon_item
                    }))
                };

                try {
                    const response = await fetch("{{ route('pos.order.store') }}", {
                        method: "POST",
                        headers: { 
                            "Content-Type": "application/json", 
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: "{{ __('messages.order_sent') }}" } }));
                        this.cart = []; 
                        this.isCartOpen = false; 
                        window.location.href = "{{ route('pos.tables') }}";
                    } else {
                        let msg = data.message || "{{ __('messages.validation_error') }}";
                        if(data.errors) msg = Object.values(data.errors)[0][0];
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: msg } }));
                    }
                } catch (e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "{{ __('messages.system_error_prefix') }}" + e.message } })); 
                } finally { 
                    this.isSubmitting = false; 
                }
            },

            // --- POLLING LOGIC ---
            startPolling() {
                this.isPolling = true;
                setInterval(async () => {
                    try {
                        const response = await fetch("{{ route('pos.products.status') }}");
                        if (response.ok) {
                            const data = await response.json();
                            data.forEach(up => {
                                const p = this.products.find(x => x.id == up.id);
                                if(p) { p.is_active = up.is_active; p.price = up.price; }
                            });
                        }
                    } catch (e) { console.error("Polling error", e); }
                }, 5000);
            }
        }
    }
</script>