<script>
    // ==========================================
    // 1. RECEIPT LOGIC (សម្រាប់តែការ Print)
    // ==========================================
    
    function receiptPrinter() {
        return {
            orderDetails: null,
            exchangeRate: 4100,
            groupedItems: [],

            prepareAndPrint(data) {
                this.orderDetails = data.order;
                if(!this.orderDetails.change_amount) {
                    this.orderDetails.change_amount = (this.orderDetails.received_amount || 0) - (this.orderDetails.total_amount || 0);
                }
                this.exchangeRate = data.exchangeRate || 4100;
                
                // Parse Addons មុននឹង Group
                if(this.orderDetails.items && this.orderDetails.items.length > 0) {
                    this.orderDetails.items = this.orderDetails.items.map(item => {
                        return {
                            ...item,
                            addons: this.parseAddons(item.addons)
                        };
                    });
                }

                this.groupItems(); 
                setTimeout(() => { window.print(); }, 500);
            },

            parseAddons(addons) {
                if (!addons) return [];
                if (Array.isArray(addons)) return addons;
                try {
                    return JSON.parse(addons);
                } catch (e) {
                    return [];
                }
            },

            groupItems() {
                if (!this.orderDetails || !this.orderDetails.items) {
                    this.groupedItems = [];
                    return;
                }
                const groups = {};
                
                this.orderDetails.items.forEach(item => {
                    const addonKey = JSON.stringify(item.addons); 
                    const uniqueKey = item.product_id + '-' + addonKey;
                    
                    let itemQty = parseInt(item.quantity) || 1;

                    if (!groups[uniqueKey]) {
                        groups[uniqueKey] = { 
                            ...item, 
                            addons: item.addons.map(a => ({...a, quantity: parseInt(a.quantity) || 1})),
                            uniqueKey: uniqueKey,
                            quantity: itemQty,
                        };
                    } else {
                        groups[uniqueKey].quantity += itemQty;
                        if (groups[uniqueKey].addons && groups[uniqueKey].addons.length > 0) {
                            groups[uniqueKey].addons.forEach((gAddon, index) => {
                                let incomingAddon = item.addons[index];
                                if(incomingAddon) {
                                    gAddon.quantity += (parseInt(incomingAddon.quantity) || 1);
                                }
                            });
                        }
                    }
                });
                this.groupedItems = Object.values(groups);
            },

            formatPrice(price) { return parseFloat(price).toFixed(2); },
            formatNumber(num) { return new Intl.NumberFormat('en-US').format(num); },
            formatRiel(amountUSD) {
                const riel = Math.ceil((parseFloat(amountUSD) * this.exchangeRate) / 100) * 100;
                return new Intl.NumberFormat('en-US').format(riel);
            },
            formatDate(dateString) {
                if(!dateString) return new Date().toLocaleDateString('en-GB');
                const date = new Date(dateString);
                return date.toLocaleDateString('en-GB') + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            },
            formatTimeOnly(dateString) {
                if(!dateString) return new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});
                const date = new Date(dateString);
                return date.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});
            }
        }
    }

    // ==========================================
    // 2. POS LOGIC
    // ==========================================
    function posTables() {
        return {
            tables: [],
            isLoading: false,
            interval: null,
            selectedTargetTable: null,
            
            // Checkout States
            isCheckoutModalOpen: false,
            isLoadingOrder: false,
            isProcessing: false,
            
            // Merge/Move/Split States
            isMergeModalOpen: false,
            isMoveModalOpen: false,
            busyTables: [],
            availableTables: [],
            isSplitMode: false,
            selectedSplitItems: [],
            
            // Order Data
            selectedTable: null,
            paymentMethod: 'cash',
            receivedAmount: '',
            
            // Exchange Rate
            isExchangeModalOpen: false,
            exchangeRate: localStorage.getItem('pos_exchange_rate') || 4100,
            tempExchangeRate: 4100,
            isFetchingRate: false,
            confirmEmpty: false, 
            
            orderDetails: { id: null, table_id: null, items: [], total: 0, invoice_number: '', shop: null },

            init() {
                this.fetchTables();
                this.loadSystemRate();
                this.tempExchangeRate = this.exchangeRate;
                this.interval = setInterval(() => { 
                    if(!this.isCheckoutModalOpen) this.fetchTables(true); 
                }, 5000);
            },

            // 🔥 HELPER: Parse Addons
            parseAddons(addons) {
                if (!addons) return [];
                if (Array.isArray(addons)) return addons;
                try { return JSON.parse(addons); } catch (e) { return []; }
            },

            async fetchTables(silent = false) {
                if (!silent) this.isLoading = true;
                try {
                    const response = await fetch("{{ route('pos.tables.fetch') }}");
                    this.tables = await response.json();
                } catch (error) { console.error(error); } 
                finally { if (!silent) this.isLoading = false; }
            },

            isExtraItem(item) { return item.product && item.product.name.toLowerCase().includes('extra'); },
            get currentTotalUSD() {
                if (this.isSplitMode) {
                    return this.selectedSplitItems.reduce((total, splitItem) => {
                        let originalItem = this.orderDetails.items.find(i => i.id === splitItem.id);
                        if (!originalItem) return total;
                        
                        let itemTotal = parseFloat(originalItem.price) * splitItem.qty;
                        let addonTotal = 0;
                        
                        let addons = this.parseAddons(originalItem.addons);
                        if (addons.length > 0) {
                            addons.forEach(ad => { 
                                addonTotal += (parseFloat(ad.price) * (parseFloat(ad.quantity) || 1)) * splitItem.qty; 
                            });
                        }
                        return total + itemTotal + addonTotal;
                    }, 0);
                }
                return parseFloat(this.orderDetails.total || 0);
            },
            get totalRiel() { return Math.ceil(this.currentTotalUSD * this.exchangeRate).toLocaleString('km-KH'); },

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
            openExchangeModal() { this.tempExchangeRate = this.exchangeRate; this.isExchangeModalOpen = true; },
            formatNumber(num) { return new Intl.NumberFormat('en-US').format(num); },
            
            async saveExchangeRate() {
                if (this.tempExchangeRate > 0) {
                    try {
                        const response = await fetch("{{ route('system.exchange-rate.update') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            body: JSON.stringify({ rate: this.tempExchangeRate })
                        });
                        if(response.ok) {
                            this.exchangeRate = this.tempExchangeRate;
                            localStorage.setItem('pos_exchange_rate', this.exchangeRate);
                            this.isExchangeModalOpen = false;
                            this.showToast("{{ __('messages.exchange_rate_updated') }}", 'success');
                        }
                    } catch (e) { this.showToast("{{ __('messages.failed_save_rate') }}", 'error'); }
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
                    if (khrRate > 0) { this.tempExchangeRate = khrRate; await this.saveExchangeRate(); } 
                    else throw new Error("{{ __('messages.rate_not_found') }}");
                } catch (error) { this.showToast("{{ __('messages.api_error') }}" + error.message, 'error'); } 
                finally { this.isFetchingRate = false; }
            },

            async openQuickCheckout(table) {
                if (table.status === 'available') { return this.showToast("{{ __('messages.table_free_order_first') }}", 'warning'); }
                this.isLoading = true;
                this.isSplitMode = false;
                this.selectedSplitItems = [];
                this.confirmEmpty = false;
                try {
                    const response = await fetch(`/pos/order-details/${table.id}`);
                    if (!response.ok) throw new Error("{{ __('messages.order_not_found') }}");
                    const data = await response.json();
                    
                    let processedItems = (data.items || []).map(item => {
                         item.addons = this.parseAddons(item.addons);
                         return item;
                    });

                    this.orderDetails = { 
                        ...data.order, 
                        items: processedItems, 
                        shop: data.shop || null, 
                        total: parseFloat(data.order.total_amount || 0),
                        formatted_date: data.formatted_date,
                        formatted_check_in: data.formatted_check_in,
                        formatted_check_out: data.formatted_check_out,
                        check_in_time: data.check_in_time,
                        check_out_time: data.check_out_time
                    };
                    
                    this.recalculateTotalLocal();

                    this.selectedTable = table;
                    this.receivedAmount = this.orderDetails.total; 
                    this.paymentMethod = 'cash';
                    this.isCheckoutModalOpen = true;
                } catch (error) { this.showToast("{{ __('messages.error_fetching_order') }}", 'error'); } 
                finally { this.isLoading = false; }
            },

            recalculateTotalLocal() {
                let total = 0;
                this.orderDetails.items.forEach(item => {
                    let basePrice = parseFloat(item.price || 0);
                    let qty = parseInt(item.quantity || 1);
                    
                    let addonTotalPerUnit = 0;
                    let addons = this.parseAddons(item.addons);
                    
                    if (addons.length > 0) { 
                        addons.forEach(ad => { 
                            addonTotalPerUnit += parseFloat(ad.price || 0) * (parseFloat(ad.quantity) || 1); 
                        }); 
                    }
                    total += (basePrice + addonTotalPerUnit) * qty;
                });
                this.orderDetails.total = total;
                if (!this.isSplitMode) this.receivedAmount = total;
            },

            updateItemQty(itemId, action) {
                if (this.isSplitMode) return;
                let index = this.orderDetails.items.findIndex(i => i.id === itemId);
                if (index === -1) return;
                let item = this.orderDetails.items[index];
                if (action === 'increase') item.quantity++;
                else if (action === 'decrease') { if (item.quantity > 1) item.quantity--; else this.orderDetails.items.splice(index, 1); } 
                else if (action === 'remove') this.orderDetails.items.splice(index, 1);
                this.recalculateTotalLocal();
            },

            updateAddonQty(itemId, addonId, action) {
                if (this.isSplitMode) return; 
                let item = this.orderDetails.items.find(i => i.id === itemId);
                if (!item) return;

                item.addons = this.parseAddons(item.addons);

                let addonIndex = item.addons.findIndex(a => a.id === addonId);
                if (addonIndex !== -1) {
                    let addon = item.addons[addonIndex];
                    let currentQty = parseInt(addon.quantity || 1);

                    if (action === 'increase') {
                        addon.quantity = currentQty + 1;
                    } 
                    else if (action === 'decrease') {
                        if (currentQty > 1) {
                            addon.quantity = currentQty - 1;
                        } else {
                            item.addons.splice(addonIndex, 1);
                        }
                    } 
                    else if (action === 'remove') {
                        item.addons.splice(addonIndex, 1);
                    }
                    this.recalculateTotalLocal();
                }
            },

            async confirmPayment() {
                if (this.isSplitMode) { await this.processSplitPayment(); return; }
                if (this.paymentMethod === 'cash' && (parseFloat(this.receivedAmount || 0) < this.currentTotalUSD)) { return this.showToast("{{ __('messages.insufficient_amount') }}", 'error'); }
                if (this.orderDetails.items.length === 0) {
                     if(!confirm("{{ __('messages.confirm_cancel_empty_order') }}")) return;
                     this.confirmEmpty = true;
                }
                this.isProcessing = true;
                try {
                    const response = await fetch('/pos/checkout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({
                            order_id: this.orderDetails.id, table_id: this.orderDetails.table_id,
                            received_amount: this.receivedAmount, payment_method: this.paymentMethod, items: this.orderDetails.items
                        })
                    });
                    const data = await response.json();
                    if (response.ok && data.status === 'success') this.finishTransaction(data);
                    else this.showToast(data.message || "{{ __('messages.payment_failed') }}", 'error');
                } catch (error) { this.showToast("{{ __('messages.system_error') }}", 'error'); } 
                finally { this.isProcessing = false; }
            },

            async openMergeModal() {
                if (!this.orderDetails.id) return;
                try {
                    const res = await fetch(`/pos/tables/busy-list?current=${this.selectedTable.id}`);
                    this.busyTables = await res.json();
                    if (this.busyTables.length === 0) this.showToast("{{ __('messages.no_busy_tables') }}", 'warning');
                    else this.isMergeModalOpen = true;
                } catch (e) { console.error(e); }
            },
            async confirmMerge(targetTableId) {
                try {
                    const response = await fetch(`/pos/order/items-for-merge/${targetTableId}`);
                    const data = await response.json();
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => { 
                            item.addons = this.parseAddons(item.addons);
                            this.orderDetails.items.push(item); 
                        });
                        this.recalculateTotalLocal();
                        this.showToast("{{ __('messages.merge_success') }}", 'info');
                        this.isMergeModalOpen = false;
                    } else this.showToast("{{ __('messages.table_has_no_items') }}", 'warning');
                } catch (e) { this.showToast("{{ __('messages.merge_error') }}", 'error'); }
            },
            
            openMoveModal() { 
                this.availableTables = this.tables.filter(t => t.status === 'available'); 
                this.selectedTargetTable = null; 
                this.isMoveModalOpen = true; 
            }, 
            
            async submitMoveTable() {
                if (!this.selectedTargetTable) {
                    return this.showToast("{{ __('messages.select_new_table_first') }}", 'warning');
                }

                this.isProcessing = true; 

                try {
                    const response = await fetch("{{ route('pos.table.move') }}", {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify({ 
                            current_table_id: this.selectedTable.id, 
                            target_table_id: this.selectedTargetTable.id 
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok && data.status === 'success') {
                        this.showToast(data.message || "{{ __('messages.move_success') }}", 'success');
                        this.isMoveModalOpen = false; 
                        this.isCheckoutModalOpen = false; 
                        this.fetchTables(); 
                    } else {
                        this.showToast(data.message || "{{ __('messages.move_failed') }}", 'error');
                    }
                } catch (e) { 
                    this.showToast("{{ __('messages.system_error') }}", 'error'); 
                } finally {
                    this.isProcessing = false;
                }
            },

            toggleSplitMode() { this.isSplitMode = !this.isSplitMode; this.selectedSplitItems = []; this.receivedAmount = this.isSplitMode ? 0 : this.orderDetails.total; },
            toggleSplitItem(item) {
                let existing = this.selectedSplitItems.find(i => i.id === item.id);
                if (existing) this.selectedSplitItems = this.selectedSplitItems.filter(i => i.id !== item.id);
                else this.selectedSplitItems.push({ id: item.id, qty: item.quantity });
                this.receivedAmount = this.currentTotalUSD;
            },
            isItemSplitted(itemId) { return this.selectedSplitItems.some(i => i.id === itemId); },
            async processSplitPayment() {
                if (this.selectedSplitItems.length === 0) return this.showToast("{{ __('messages.select_items_first') }}", 'warning');
                if (this.paymentMethod === 'cash' && (parseFloat(this.receivedAmount || 0) < this.currentTotalUSD)) return this.showToast("{{ __('messages.insufficient_funds') }}", 'error');
                this.isProcessing = true;
                try {
                    const response = await fetch("{{ route('pos.order.split') }}", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            original_order_id: this.orderDetails.id, split_items: this.selectedSplitItems,
                            payment_method: this.paymentMethod, received_amount: this.receivedAmount
                        })
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.showToast("{{ __('messages.split_bill_success') }}", 'success');
                        
                        const splitItems = this.orderDetails.items.filter(item => this.selectedSplitItems.some(split => split.id === item.id));
                        const printData = {
                            order: {
                                ...this.orderDetails,
                                items: splitItems,
                                total_amount: this.currentTotalUSD,
                                received_amount: this.receivedAmount,
                                payment_method: this.paymentMethod,
                                change_amount: parseFloat(this.receivedAmount) - parseFloat(this.currentTotalUSD),
                                invoice_number: data.invoice_number || this.orderDetails.invoice_number + '-SUB'
                            },
                            exchangeRate: this.exchangeRate
                        };
                        window.dispatchEvent(new CustomEvent('print-receipt', { detail: printData }));
                        
                        if(data.remaining_items_count > 0) this.openQuickCheckout(this.selectedTable);
                        else { this.isCheckoutModalOpen = false; this.fetchTables(); }
                    } else this.showToast(data.message, 'error');
                } catch(e) { console.error(e); } 
                finally { this.isProcessing = false; }
            },

            finishTransaction(data) {
                this.isCheckoutModalOpen = false;
                this.showToast("{{ __('messages.success') }}", 'success');
                this.fetchTables();

                const finalCheckOutTime = new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});

                const printData = {
                    order: {
                        ...this.orderDetails,
                        total_amount: data.total_amount || this.orderDetails.total,
                        received_amount: data.received_amount || this.receivedAmount,
                        change_amount: data.change,
                        invoice_number: data.invoice_number || this.orderDetails.invoice_number,
                        formatted_check_out: finalCheckOutTime
                    },
                    exchangeRate: this.exchangeRate
                };
                window.dispatchEvent(new CustomEvent('print-receipt', { detail: printData }));
            },

            showToast(message, type = 'success') {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: message, type: type } }));
            }
        }
    }
</script>