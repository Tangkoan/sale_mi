<script>
    /**
     * =========================================================
     * 1. HEADER CONTROLLER
     * =========================================================
     */
    function headerController() {
        return {
            // --- State ---
            isSearchOpen: false,
            search: '',
            activeCategory: 'all',
            isAddonMode: false,
            
            // Exchange Rate
            isExchangeModalOpen: false,
            exchangeRate: localStorage.getItem('pos_exchange_rate') || 4100,
            tempExchangeRate: 4100,
            isFetchingRate: false,

            // --- Init ---
            init() {
                this.loadSystemRate();
                this.tempExchangeRate = this.exchangeRate;

                // Watch Search
                this.$watch('search', value => {
                    window.dispatchEvent(new CustomEvent('pos-search-changed', { detail: value }));
                });
            },

            // --- Navigation Functions ---
            toggleSearch() {
                this.isSearchOpen = !this.isSearchOpen;
                if (!this.isSearchOpen) this.search = '';
            },

            setCategory(id) {
                this.activeCategory = id;
                window.dispatchEvent(new CustomEvent('pos-category-changed', { detail: id }));
            },

            toggleAddonMode() {
                this.isAddonMode = !this.isAddonMode;
                window.dispatchEvent(new CustomEvent('pos-toggle-addon-mode', { detail: this.isAddonMode }));
                
                // Reset UI
                this.search = '';
                this.isSearchOpen = false;
                if (!this.isAddonMode) {
                    this.activeCategory = 'all';
                    window.dispatchEvent(new CustomEvent('pos-category-changed', { detail: 'all' }));
                }
            },

            openQuickAddon() {
                window.dispatchEvent(new CustomEvent('pos-open-quick-addon'));
            },

            // --- Exchange Rate Functions ---
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
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Exchange rate updated!' } }));
                        } else {
                            throw new Error('Update failed');
                        }
                    } catch (e) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Failed to save rate.' } }));
                    }
                }
            },

            // ✅ FIX: កែសម្រួល Auto Fetch ឱ្យខ្លាំងជាងមុន
            async fetchRateFromApi() {
                this.isFetchingRate = true;
                try {
                    const response = await fetch("{{ route('system.exchange-rate.fetch-nbc') }}");
                    const data = await response.json();

                    console.log("NBC API Response:", data); // Debug មើលក្នុង Console

                    if (data.status === 'error') {
                        throw new Error(data.message);
                    }

                    let khrRate = 0;

                    // ករណី 1: data.data គឺជា Object (ដូចរូបដែលបងផ្ញើ)
                    if (data.data && typeof data.data === 'object' && !Array.isArray(data.data)) {
                        khrRate = parseFloat(data.data.average || data.data.ask || data.data.bid);
                    } 
                    // ករណី 2: data.data គឺជា Array (ក្រែងលោ API ប្តូរទម្រង់)
                    else if (data.data && Array.isArray(data.data)) {
                        const usdItem = data.data.find(i => i.currency_id === 'USD' || i.symbol === 'USD/KHR');
                        if (usdItem) {
                            khrRate = parseFloat(usdItem.average || usdItem.ask || usdItem.bid);
                        }
                    }

                    if (khrRate > 0) {
                        this.tempExchangeRate = khrRate; 
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Rate fetched: ' + khrRate } }));
                    } else {
                        throw new Error("Rate not found in API data");
                    }

                } catch (error) {
                    console.error("Fetch Error:", error);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'API Error: ' + error.message } }));
                } finally {
                    this.isFetchingRate = false;
                }
            },

            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num);
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
            products: @json($products ?? []),
            categories: @json($categories ?? []),
            addons: @json($addons ?? []),
            
            activeCategory: 'all',
            search: '',
            viewMode: 'menu', 
            
            cart: [],
            isCartOpen: false,
            isProductModalOpen: false,
            isSubmitting: false,
            isPolling: false,
            
            tempItem: { id: null, name: '', image: null, base_price: 0, qty: 1, note: '', selectedAddons: [], category_id: null },

            init() {
                this.startPolling();
                window.addEventListener('pos-category-changed', (e) => { this.activeCategory = e.detail; });
                window.addEventListener('pos-search-changed', (e) => { this.search = e.detail; });
                window.addEventListener('pos-toggle-addon-mode', (e) => { this.viewMode = e.detail ? 'addon' : 'menu'; });
                window.addEventListener('pos-open-quick-addon', () => { this.openQuickAddon(); });
            },

            // --- FILTER LOGIC ---
            get filteredProducts() {
                let items = [];

                // MODE: ADDON
                if (this.viewMode === 'addon') {
                    items = this.addons.filter(a => a.is_active == 1 || a.is_active == true);

                    if (this.search) {
                        items = items.filter(a => a.name.toLowerCase().includes(this.search.toLowerCase()));
                    }

                    // Smart Filter: Show Addons based on Category Destination
                    if (this.activeCategory !== 'all') {
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

                // MODE: MENU
                items = this.products;
                if (this.activeCategory !== 'all') items = items.filter(p => p.category_id == this.activeCategory);
                if (this.search) items = items.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));
                items = items.filter(p => !p.name.toLowerCase().includes('extra'));

                return items;
            },

            // ... (Helpers) ...
            get availableAddons() {
                if (this.tempItem.type === 'addon_item') return [];
                if (!this.tempItem.id) return [];
                if (this.tempItem.name === "Extra / Addon Only") return this.addons.filter(a => a.is_active);
                const product = this.products.find(p => p.id == this.tempItem.id);
                if (product && product.addons) return product.addons.filter(a => a.is_active);
                return [];
            },

            openProductModal(product) {
                if(!product.is_active) return;
                this.tempItem = {
                    id: product.id, name: product.name, image: product.image,
                    base_price: parseFloat(product.price), qty: 1, note: '', 
                    selectedAddons: [], category_id: product.category_id,
                    type: product.type || 'product',
                    category_name: (product.type === 'addon_item') ? 'Add-on' : (product.category ? product.category.name : 'Item')
                };
                this.isProductModalOpen = true;
            },

            openQuickAddon() {
                let extraProduct = this.products.find(p => p.name.toLowerCase().includes('extra') || parseFloat(p.price) == 0);
                if (!extraProduct) return alert("Please create 'Extra' product first.");
                
                this.tempItem = {
                    id: extraProduct.id, name: "Extra / Addon Only", image: null, 
                    base_price: parseFloat(extraProduct.price), qty: 1, note: 'Addon Only', 
                    selectedAddons: [], category_id: extraProduct.category_id, category_name: 'Special'
                };
                this.isProductModalOpen = true;
            },

            closeProductModal() { this.isProductModalOpen = false; },
            
            // Cart Logic
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
            addToCart() {
                try {
                    const finalAddons = this.tempItem.selectedAddons.map(ad => ({
                        id: ad.id, name: ad.name, price: ad.price, qty: ad.qty
                    }));
                    this.cart.push({
                        product_id: this.tempItem.id, name: this.tempItem.name,
                        base_price: parseFloat(this.tempItem.base_price), qty: parseInt(this.tempItem.qty),
                        note: this.tempItem.note, addons: finalAddons, 
                        total_price_calculated: this.calculateItemTotal(),
                        is_addon_item: (this.tempItem.type === 'addon_item')
                    });
                    this.closeProductModal();
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Added to cart' } }));
                } catch (e) { console.error(e); }
            },
            removeFromCart(index) { this.cart.splice(index, 1); if(this.cart.length === 0) this.isCartOpen = false; },
            get cartTotalQty() { return this.cart.reduce((sum, item) => sum + parseInt(item.qty), 0); },
            get cartTotalPrice() {
                return this.cart.reduce((sum, item) => {
                    if (item.total_price_calculated) return sum + item.total_price_calculated;
                    let ads = 0; if(item.addons) item.addons.forEach(ad => ads += (ad.price * (ad.qty || 1)));
                    return sum + (item.base_price * item.qty) + ads;
                }, 0);
            },

            async submitOrder() {
                if (this.cart.length === 0) return;
                this.isSubmitting = true;
                const payload = {
                    table_id: {{ $table->id }},
                    exchange_rate: localStorage.getItem('pos_exchange_rate') || 4100, 
                    items: this.cart.map(item => ({
                        product_id: item.product_id, qty: item.qty, price: item.base_price, note: item.note, addons: item.addons, is_addon: item.is_addon_item
                    }))
                };
                try {
                    const response = await fetch("{{ route('pos.order.store') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(payload)
                    });
                    const data = await response.json();
                    if (response.ok) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Order sent!' } }));
                        this.cart = []; this.isCartOpen = false; window.location.href = "{{ route('pos.tables') }}";
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || "Error" } }));
                    }
                } catch (e) { console.error(e); window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "Network Error" } })); } 
                finally { this.isSubmitting = false; }
            },

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