<script>
    // ==========================================
    // 1. RECEIPT PRINTER LOGIC
    // ==========================================
    function receiptPrinter() {
        return {
            orderDetails: null,
            groupedItems: [],

            parseAddons(addons) {
                if (!addons) return [];
                if (Array.isArray(addons)) return addons;
                try { return JSON.parse(addons); } catch (e) { return []; }
            },

            formatPrice(price) { return parseFloat(price).toFixed(2); },
            formatNumber(num) { return new Intl.NumberFormat('en-US').format(num); },
            
            formatRiel(amount) {
                return new Intl.NumberFormat('km-KH').format(amount);
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
            searchQuery: '', 
            tables: [],
            isLoading: false,
            interval: null,
            selectedTargetTable: null,

            isChangeItemListModalOpen: false,
            isLoadingChangeItemList: false,
            changeItemListItems: [],

            isChangeItemModalOpen: false,
            changeItemSearch: '',
            searchTimeout: null,
            changeItemProducts: [],
            changeItemData: { old_item_id: null, old_item_name: '', max_qty: 1, exchange_qty: 1, new_product_id: null },
            
            // Checkout States
            isCheckoutModalOpen: false,
            isLoadingOrder: false,
            isProcessing: false,
            
            // Merge/Move/Split States
            isMergeModalOpen: false,
            isMoveModalOpen: false,
            busyTables: [],
            availableTables: [],
            selectedMergeTables: [],

            isSplitMode: false,
            selectedSplitItems: [],
            
            // Order Data
            selectedTable: null,
            paymentMethod: 'cash',
            receivedAmount: '',
            confirmEmpty: false, 
            
            orderDetails: { id: null, table_id: null, items: [], total: 0, invoice_number: '', shop: null },

            // State សម្រាប់ Delivery
            isDeliveryModalOpen: false,
            deliveryPlatforms: [],
            isLoadingPlatforms: false,

            // Function បើក Modal និងទាញទិន្នន័យ Platform
            async openDeliveryModal() {
                this.isDeliveryModalOpen = true;
                if (this.deliveryPlatforms.length === 0) {
                    this.isLoadingPlatforms = true;
                    try {
                        const response = await fetch('/pos/delivery-platforms/active', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        this.deliveryPlatforms = await response.json();
                    } catch (error) { 
                        console.error("Error fetching platforms:", error); 
                        this.showToast("មានបញ្ហាក្នុងការទាញយកទិន្នន័យ សូមពិនិត្យមើលបណ្ដាញ", "error");
                    } finally { 
                        this.isLoadingPlatforms = false; 
                    }
                }
            },

            // Function ពេលគេចុចយក Platform ណាមួយ
            async selectDeliveryPlatform(platform) {
                try {
                    // ១. ទៅយក ID តុ Delivery (ពិតប្រាកដក្នុង DB) ដើម្បីកុំអោយលោត 404
                    const res = await fetch('/pos/delivery-table', {
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    if (!res.ok) throw new Error("មិនអាចរៀបចំតុ Delivery បានទេ");
                    
                    const data = await res.json();
                    
                    // ២. បញ្ជូនទៅ Menu ដោយប្រើ ID តុពិត ព្រមទាំងភ្ជាប់ Parameter
                    window.location.href = `/pos/menu/${data.id}?is_delivery=true&platform=${encodeURIComponent(platform.name)}`;
                } catch (error) {
                    this.showToast("មានបញ្ហាក្នុងការរៀបចំ Delivery Table សូមពិនិត្យមើល Route និង Controller", "error");
                    console.error(error);
                }
            },

            get filteredTables() {
                // លាក់តុ Delivery កុំអោយបង្ហាញក្នុង Grid ធម្មតា
                let normalTables = this.tables.filter(table => table.name !== 'Delivery Table');
                
                if (this.searchQuery.trim() === '') {
                    return normalTables;
                }
                
                let query = this.searchQuery.toLowerCase();
                return normalTables.filter(table => {
                    return table.name.toLowerCase().includes(query);
                });
            },

            init() {
                this.fetchTables();
                this.interval = setInterval(() => { 
                    if(!this.isCheckoutModalOpen) this.fetchTables(true); 
                }, 5000);
            },

            parseAddons(addons) {
                if (!addons) return [];
                if (Array.isArray(addons)) return addons;
                try { return JSON.parse(addons); } catch (e) { return []; }
            },

            async fetchTables(silent = false) {
                if (!silent) this.isLoading = true;
                try {
                    const response = await fetch("{{ route('pos.tables.fetch') }}", {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.tables = await response.json();
                } catch (error) { console.error(error); } 
                finally { if (!silent) this.isLoading = false; }
            },

            isExtraItem(item) { return item.product && item.product.name.toLowerCase().includes('extra'); },
            
            get currentTotal() {
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

            formatRiel(amount) {
                return new Intl.NumberFormat('km-KH').format(amount);
            },

            async openQuickCheckout(table) {
                if (table.status === 'available') { return this.showToast("{{ __('messages.table_free_order_first') }}", 'warning'); }
                this.isLoading = true;
                this.isSplitMode = false;
                this.selectedSplitItems = [];
                this.confirmEmpty = false;
                try {
                    const response = await fetch(`/pos/order-details/${table.id}`, { headers: { 'Accept': 'application/json' }});
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
                        check_out_time: data.check_out_time,
                        invoice_number: data.order.invoice_number
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
                    if (action === 'increase') { addon.quantity = currentQty + 1; } 
                    else if (action === 'decrease') {
                        if (currentQty > 1) { addon.quantity = currentQty - 1; } else { item.addons.splice(addonIndex, 1); }
                    } 
                    else if (action === 'remove') { item.addons.splice(addonIndex, 1); }
                    this.recalculateTotalLocal();
                }
            },

            async confirmPayment() {
                if (this.isSplitMode) { await this.processSplitPayment(); return; }
                if (this.paymentMethod === 'cash' && (parseFloat(this.receivedAmount || 0) < this.currentTotal)) { return this.showToast("{{ __('messages.insufficient_amount') }}", 'error'); }
                if (this.orderDetails.items.length === 0) {
                     if(!confirm("{{ __('messages.confirm_cancel_empty_order') }}")) return;
                     this.confirmEmpty = true;
                }
                this.isProcessing = true;
                try {
                    const response = await fetch('/pos/checkout', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content 
                        },
                        body: JSON.stringify({
                            order_id: this.orderDetails.id, table_id: this.orderDetails.table_id,
                            received_amount: this.receivedAmount, payment_method: this.paymentMethod, items: this.orderDetails.items
                        })
                    });
                    const data = await response.json();
                    if (response.ok && data.status === 'success') {
                        this.finishTransaction(data);
                    } else {
                        this.showToast(data.message || "{{ __('messages.payment_failed') }}", 'error');
                    }
                } catch (error) { this.showToast("{{ __('messages.system_error') }}", 'error'); } 
                finally { this.isProcessing = false; }
            },

            async openMergeModal() {
                if (!this.orderDetails.id) return;
                this.selectedMergeTables = []; 
                try {
                    const res = await fetch(`/pos/tables/busy-list?current=${this.selectedTable.id}`, { headers: { 'Accept': 'application/json' }});
                    this.busyTables = await res.json();
                    if (this.busyTables.length === 0) this.showToast("{{ __('messages.no_busy_tables') }}", 'warning');
                    else this.isMergeModalOpen = true;
                } catch (e) { console.error(e); }
            },

            toggleMergeTable(table) {
                if (this.isTableSelectedForMerge(table.id)) {
                    this.selectedMergeTables = this.selectedMergeTables.filter(t => t.id !== table.id);
                } else {
                    this.selectedMergeTables.push(table);
                }
            },

            isTableSelectedForMerge(tableId) {
                return this.selectedMergeTables.some(t => t.id === tableId);
            },
            
            async submitMergeTable() {
                if (this.selectedMergeTables.length === 0) {
                    return this.showToast("{{ __('messages.select_table_first') }}", 'warning');
                }
                this.isProcessing = true;
                let successCount = 0;
                for (const table of this.selectedMergeTables) {
                    const result = await this.confirmMerge(table.id, false); 
                    if(result) successCount++;
                }
                this.isProcessing = false;
                if(successCount > 0) {
                    this.showToast("{{ __('messages.merge_success') }}", 'success');
                    this.isMergeModalOpen = false;
                }
            },

            async confirmMerge(targetTableId, autoClose = true) {
                try {
                    const response = await fetch(`/pos/order/items-for-merge/${targetTableId}`, { headers: { 'Accept': 'application/json' }});
                    const data = await response.json();
                    
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => { 
                            item.addons = this.parseAddons(item.addons);
                            this.orderDetails.items.push(item); 
                        });
                        this.recalculateTotalLocal();
                        
                        if(autoClose) {
                            this.showToast("{{ __('messages.merge_success') }}", 'info');
                            this.isMergeModalOpen = false;
                        }
                        return true; 
                    } else {
                        if(autoClose) this.showToast("{{ __('messages.table_has_no_items') }}", 'warning');
                        return false;
                    }
                } catch (e) { 
                    if(autoClose) this.showToast("{{ __('messages.merge_error') }}", 'error'); 
                    return false;
                }
            },
            
            openMoveModal() { 
                this.availableTables = this.tables.filter(t => t.status === 'available'); 
                this.selectedTargetTable = null; 
                this.isMoveModalOpen = true; 
            }, 

            async openChangeItemList(table) {
                this.selectedTable = table;
                this.isChangeItemListModalOpen = true;
                this.isLoadingChangeItemList = true;
                try {
                    const response = await fetch(`/pos/order-details/${table.id}`, { headers: { 'Accept': 'application/json' }});
                    if (!response.ok) throw new Error("មិនមានទិន្នន័យ");
                    const data = await response.json();
                    this.changeItemListItems = data.items || [];
                } catch (error) { this.changeItemListItems = []; }
                finally { this.isLoadingChangeItemList = false; }
            },

            openChangeItemModal(item) {
                this.changeItemData = {
                    old_item_id: item.id,
                    old_item_name: item.product ? item.product.name : 'មុខម្ហូប',
                    max_qty: item.quantity,
                    exchange_qty: 1,
                    new_product_id: null
                };
                this.changeItemSearch = '';
                this.changeItemProducts = [];
                this.isChangeItemModalOpen = true;
                this.fetchChangeItemProducts(); 
            },

            async fetchChangeItemProducts() {
                try {
                    const res = await fetch(`/pos/products/paginated?search=${this.changeItemSearch}`, { headers: { 'Accept': 'application/json' }});
                    const data = await res.json();
                    this.changeItemProducts = data.data || data; 
                } catch(e) {}
            },

            debounceChangeItemSearch() {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => { this.fetchChangeItemProducts(); }, 400);
            },

            selectNewChangeItemProduct(product) { 
                this.changeItemData.new_product_id = product.id; 
            },

            goToMenuForExchange() {
                let tableId = this.selectedTable.id;
                let oldItemId = this.changeItemData.old_item_id;
                let qty = this.changeItemData.exchange_qty;
                
                window.location.href = `/pos/menu/${tableId}?exchange_item_id=${oldItemId}&exchange_qty=${qty}`;
            },
            
            async submitMoveTable() {
                if (!this.selectedTargetTable) { return this.showToast("{{ __('messages.select_new_table_first') }}", 'warning'); }
                this.isProcessing = true; 
                try {
                    const response = await fetch("{{ route('pos.table.move') }}", {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json',
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
                    } else { this.showToast(data.message || "{{ __('messages.move_failed') }}", 'error'); }
                } catch (e) { this.showToast("{{ __('messages.system_error') }}", 'error'); } 
                finally { this.isProcessing = false; }
            },

            toggleSplitMode() { this.isSplitMode = !this.isSplitMode; this.selectedSplitItems = []; this.receivedAmount = this.isSplitMode ? 0 : this.orderDetails.total; },
            
            toggleSplitItem(item) {
                let existing = this.selectedSplitItems.find(i => i.id === item.id);
                if (existing) this.selectedSplitItems = this.selectedSplitItems.filter(i => i.id !== item.id);
                else this.selectedSplitItems.push({ id: item.id, qty: item.quantity });
                this.receivedAmount = this.currentTotal;
            },
            
            isItemSplitted(itemId) { return this.selectedSplitItems.some(i => i.id === itemId); },
            
            async processSplitPayment() {
                if (this.selectedSplitItems.length === 0) return this.showToast("{{ __('messages.select_items_first') }}", 'warning');
                if (this.paymentMethod === 'cash' && (parseFloat(this.receivedAmount || 0) < this.currentTotal)) return this.showToast("{{ __('messages.insufficient_funds') }}", 'error');
                this.isProcessing = true;
                try {
                    const response = await fetch("{{ route('pos.order.split') }}", {
                        method: "POST",
                        headers: { 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                        },
                        body: JSON.stringify({
                            original_order_id: this.orderDetails.id, split_items: this.selectedSplitItems,
                            payment_method: this.paymentMethod, received_amount: this.receivedAmount
                        })
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.showToast("{{ __('messages.split_bill_success') }}", 'success');
                        
                        if(data.remaining_items_count > 0) this.openQuickCheckout(this.selectedTable);
                        else { this.isCheckoutModalOpen = false; this.fetchTables(); }
                    } else this.showToast(data.message, 'error');
                } catch(e) { console.error(e); } 
                finally { this.isProcessing = false; }
            },

            finishTransaction(data) {
                this.isCheckoutModalOpen = false;
                this.showToast("ការទូទាត់ប្រាក់ និងបោះពុម្ពជោគជ័យ!", 'success');
                this.fetchTables();
            },

            showToast(message, type = 'success') {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: message, type: type } }));
            }
        }
    }
</script>