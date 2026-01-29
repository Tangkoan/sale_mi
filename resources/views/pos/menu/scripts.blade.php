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
                this.startPolling();
            },

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
                        const addonResponse = await fetch("{{ route('pos.addons.status') }}");
                        if (addonResponse.ok) {
                            const addonData = await addonResponse.json();
                            addonData.forEach(updatedAddon => {
                                const localAddon = this.addons.find(a => a.id == updatedAddon.id);
                                if (localAddon) {
                                    localAddon.is_active = (updatedAddon.is_active == 1 || updatedAddon.is_active == true);
                                    localAddon.price = updatedAddon.price;
                                }
                            });
                        }
                    } catch (e) { console.error("Polling error:", e); }
                }, 5000);
            },

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

            // ក្នុង file: resources/views/pos/menu/scripts.blade.php

            get filteredProducts() {
                let items = this.products;

                // 1. Filter តាម Category
                if (this.activeCategory !== 'all') items = items.filter(p => p.category_id == this.activeCategory);

                // 2. Filter តាម Search
                if (this.search) items = items.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()));

                // 3. លាក់តែ Product ឈ្មោះ "Extra" (កុំលាក់ product.is_active=0)
                items = items.filter(p => !p.name.toLowerCase().includes('extra'));

                return items;
            },
            get availableAddons() {
                if (!this.tempItem.id) return [];
                const product = this.products.find(p => p.id == this.tempItem.id);
                if (this.tempItem.name === "Extra / Addon Only" || (product && product.name.toLowerCase().includes("extra"))) {
                    return this.addons.filter(a => a.is_active == 1 || a.is_active == true);
                }
                if (product) {
                    if (product.addons && product.addons.length > 0) {
                        return product.addons.filter(a => a.is_active == 1 || a.is_active == true);
                    }
                    return []; 
                }
                return [];
            },

            openQuickAddon() {
                let extraProduct = this.products.find(p => p.name.toLowerCase().includes('extra') || p.name.toLowerCase().includes('addon') || p.price == 0);
                if (!extraProduct) {
                        extraProduct = this.products.find(p => parseFloat(p.price) === 0);
                }
                if (!extraProduct) {
                    return alert("Please create a product named 'Extra' with price $0 in Admin first to use this feature.");
                }
                this.tempItem = {
                    id: extraProduct.id, name: "Extra / Addon Only", image: null, base_price: parseFloat(extraProduct.price), qty: 1, note: 'Addon Only', selectedAddons: [], category_id: extraProduct.category_id
                };
                this.isProductModalOpen = true;
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
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify(payload)
                    });
                    const data = await response.json();
                    if (response.status === 422 && data.status === 'out_of_stock') {
                        const removedItems = data.out_of_stock_items;
                        const removedIds = removedItems.map(i => i.id);
                        const removedNames = removedItems.map(i => i.name).join(', ');
                        this.cart = this.cart.filter(cartItem => !removedIds.includes(cartItem.product_id));
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: `Removed "${removedNames}" as they are out of stock!` } }));
                        if (this.cart.length === 0) { this.isCartOpen = false; } 
                        this.checkProductStatusNow();
                    } else if (!response.ok) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: data.message || "Error processing order" } }));
                    } else {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Order sent successfully!' } }));
                        this.cart = []; this.isCartOpen = false; window.location.href = "{{ route('pos.tables') }}";
                    }
                } catch (e) { 
                    console.error(e); 
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: "Network Error" } }));
                } finally { this.isSubmitting = false; }
            }
        }
    }
</script>