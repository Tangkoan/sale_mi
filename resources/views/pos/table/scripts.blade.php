<script>
    function posTables() {
        return {
            // ==========================================
            // 1. DATA (STATE)
            // ==========================================
            tables: [],
            isLoading: false,
            interval: null,
            
            // Checkout Modal State
            isCheckoutModalOpen: false,
            isLoadingOrder: false,
            isProcessing: false,
            
            // Merge, Move & Split State
            isMergeModalOpen: false,
            isMoveModalOpen: false, // 🔥 New
            busyTables: [],
            availableTables: [], // 🔥 New
            isSplitMode: false,
            selectedSplitItems: [],
            
            // Order Data
            selectedTable: null,
            paymentMethod: 'cash',
            receivedAmount: '',
            
            // Exchange Rate State
            isExchangeModalOpen: false,
            exchangeRate: localStorage.getItem('pos_exchange_rate') || 4100,
            tempExchangeRate: 4100,
            isFetchingRate: false,

            confirmEmpty: false, 
            
            orderDetails: {
                id: null,
                table_id: null,
                items: [],
                total: 0,
                invoice_number: '',
                shop: null 
            },

            // ==========================================
            // 2. INIT & TABLE LOADING
            // ==========================================
            init() {
                this.fetchTables();
                this.loadSystemRate(); // Load Rate on Init
                this.tempExchangeRate = this.exchangeRate;

                // Refresh តុម្តងរាល់ 5 វិនាទី (បើមិនកំពុង Check bill)
                this.interval = setInterval(() => { 
                    if(!this.isCheckoutModalOpen) this.fetchTables(true); 
                }, 5000);
            },

            async fetchTables(silent = false) {
                if (!silent) this.isLoading = true;
                try {
                    const response = await fetch("{{ route('pos.tables.fetch') }}");
                    this.tables = await response.json();
                } catch (error) { console.error(error); } 
                finally { if (!silent) this.isLoading = false; }
            },

            // ==========================================
            // 3. COMPUTED PROPERTIES
            // ==========================================
            isExtraItem(item) {
                return item.product && item.product.name.toLowerCase().includes('extra');
            },

            get currentTotalUSD() {
                if (this.isSplitMode) {
                    return this.selectedSplitItems.reduce((total, splitItem) => {
                        let originalItem = this.orderDetails.items.find(i => i.id === splitItem.id);
                        if (!originalItem) return total;

                        let itemTotal = parseFloat(originalItem.price) * splitItem.qty;
                        let addonTotal = 0;
                        if (originalItem.addons) {
                            originalItem.addons.forEach(ad => {
                                addonTotal += parseFloat(ad.price) * (ad.quantity || 1); 
                            });
                        }
                        return total + itemTotal + addonTotal;
                    }, 0);
                }
                return parseFloat(this.orderDetails.total || 0);
            },

            get totalRiel() {
                return Math.ceil(this.currentTotalUSD * this.exchangeRate).toLocaleString('km-KH');
            },

            // ==========================================
            // EXCHANGE RATE FUNCTIONS
            // ==========================================
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

            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num);
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
                            this.showToast('Exchange rate updated!', 'success');
                        } else {
                            throw new Error('Update failed');
                        }
                    } catch (e) {
                        this.showToast('Failed to save rate.', 'error');
                    }
                }
            },

            async fetchRateFromApi() {
                this.isFetchingRate = true;
                try {
                    // 1. ហៅ API
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
                        throw new Error("Rate not found in API data");
                    }
                } catch (error) {
                    this.showToast('API Error: ' + error.message, 'error');
                } finally {
                    this.isFetchingRate = false;
                }
            },

            // ==========================================
            // 4. OPEN CHECKOUT & DATA LOADING
            // ==========================================
            async openQuickCheckout(table) {
                if (table.status === 'available') {
                    this.showToast('តុនេះទំនេរ សូមធ្វើការកម្មង់ជាមុនសិន', 'warning');
                    return;
                }

                this.isLoading = true;
                this.isSplitMode = false;
                this.selectedSplitItems = [];
                this.confirmEmpty = false;
                
                try {
                    const response = await fetch(`/pos/order-details/${table.id}`);
                    if (!response.ok) throw new Error("Order not found");
                    const data = await response.json();

                    this.orderDetails = {
                        ...data.order,
                        items: data.items,
                        shop: data.shop || null,
                        total: parseFloat(data.order.total_amount || 0)
                    };

                    this.selectedTable = table;
                    this.receivedAmount = this.orderDetails.total; 
                    this.paymentMethod = 'cash';
                    this.isCheckoutModalOpen = true;

                } catch (error) {
                    console.error("Cannot load order:", error);
                    this.showToast("មានបញ្ហាក្នុងការទាញទិន្នន័យ Order", 'error');
                } finally {
                    this.isLoading = false;
                }
            },

            recalculateTotalLocal() {
                let total = 0;
                this.orderDetails.items.forEach(item => {
                    let itemTotal = parseFloat(item.price) * parseInt(item.quantity);
                    let addonTotal = 0;
                    if (item.addons) {
                        item.addons.forEach(ad => {
                            addonTotal += parseFloat(ad.price) * parseInt(ad.quantity || 1);
                        });
                    }
                    total += itemTotal + addonTotal;
                });
                
                this.orderDetails.total = total;
                if (!this.isSplitMode) this.receivedAmount = total;
            },

            // ==========================================
            // 5. ITEM CONTROLS (LOCAL MODIFICATION ONLY)
            // ==========================================
            updateItemQty(itemId, action) {
                if (this.isSplitMode) return;

                let index = this.orderDetails.items.findIndex(i => i.id === itemId);
                if (index === -1) return;
                let item = this.orderDetails.items[index];

                if (action === 'increase') {
                    item.quantity++;
                } else if (action === 'decrease') {
                    if (item.quantity > 1) item.quantity--;
                    else this.orderDetails.items.splice(index, 1);
                } else if (action === 'remove') {
                    this.orderDetails.items.splice(index, 1);
                }
                
                this.recalculateTotalLocal();
            },

            updateAddonQty(addonId, action) {
                if (this.isSplitMode) return; 

                for (let item of this.orderDetails.items) {
                    if (item.addons) {
                        let addonIndex = item.addons.findIndex(a => a.id === addonId);
                        if (addonIndex !== -1) {
                            let addon = item.addons[addonIndex];
                            
                            if (this.isExtraItem(item) && item.addons.length === 1 && (action === 'decrease' && addon.quantity === 1 || action === 'remove')) {
                                this.updateItemQty(item.id, 'remove');
                                return;
                            }

                            if (action === 'increase') addon.quantity++;
                            else if (action === 'decrease') {
                                if (addon.quantity > 1) addon.quantity--;
                                else item.addons.splice(addonIndex, 1);
                            } else if (action === 'remove') item.addons.splice(addonIndex, 1);
                            
                            this.recalculateTotalLocal();
                            return;
                        }
                    }
                }
            },

            // ==========================================
            // 6. PAYMENT LOGIC (MAIN)
            // ==========================================
            async confirmPayment() {
                if (this.isSplitMode) {
                    await this.processSplitPayment();
                    return;
                }

                if (this.paymentMethod === 'cash' && (parseFloat(this.receivedAmount || 0) < this.currentTotalUSD)) {
                    this.showToast('ទឹកប្រាក់ទទួលបានមិនគ្រប់គ្រាន់!', 'error');
                    return;
                }
                
                if (this.orderDetails.items.length === 0) {
                     if(!confirm('ការបញ្ជាទិញគ្មានទិន្នន័យ (បានលុបអស់)។ តើអ្នកចង់ Cancel Order នេះទេ?')) return;
                     this.confirmEmpty = true;
                }

                this.isProcessing = true;
                try {
                    const response = await fetch('/pos/checkout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({
                            order_id: this.orderDetails.id,
                            table_id: this.orderDetails.table_id,
                            received_amount: this.receivedAmount,
                            payment_method: this.paymentMethod,
                            items: this.orderDetails.items
                        })
                    });
                    const data = await response.json();
                    if (response.ok && data.status === 'success') {
                        this.finishTransaction(data);
                    } else {
                        this.showToast(data.message || 'Payment Failed', 'error');
                    }
                } catch (error) { 
                    console.error(error);
                    this.showToast('System Error', 'error'); 
                } finally { 
                    this.isProcessing = false; 
                }
            },

            // ==========================================
            // 7. MERGE & MOVE TABLE FEATURES
            // ==========================================
            async openMergeModal() {
                if (!this.orderDetails.id) return;
                try {
                    const res = await fetch(`/pos/tables/busy-list?current=${this.selectedTable.id}`);
                    this.busyTables = await res.json();
                    if (this.busyTables.length === 0) {
                        this.showToast('មិនមានតុផ្សេងកំពុងដំណើរការទេ', 'warning');
                    } else {
                        this.isMergeModalOpen = true;
                    }
                } catch (e) { console.error(e); }
            },

            async confirmMerge(targetTableId) {
                try {
                    const response = await fetch(`/pos/order/items-for-merge/${targetTableId}`);
                    const data = await response.json();

                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            this.orderDetails.items.push(item);
                        });
                        this.recalculateTotalLocal();
                        this.showToast('បញ្ចូលតុ (Visual) ជោគជ័យ! សូមចុច Confirm ដើម្បីរក្សាទុក។', 'info');
                        this.isMergeModalOpen = false;
                    } else {
                        this.showToast('តុនោះគ្មានមុខម្ហូបទេ', 'warning');
                    }
                } catch (e) { 
                    console.error(e); 
                    this.showToast('Merge Error', 'error');
                }
            },

            // 🔥 MOVE TABLE FEATURE (NEW)
            openMoveModal() {
                // Filter យកតែតុដែលទំនេរ (Status = available) ពីក្នុង List ដែលមានស្រាប់
                this.availableTables = this.tables.filter(t => t.status === 'available');
                this.isMoveModalOpen = true;
            },

            async confirmMove(targetTableId) {
                if (!confirm('តើអ្នកពិតជាចង់ប្ដូរតុនេះមែនទេ?')) return;
                
                try {
                    const response = await fetch("{{ route('pos.table.move') }}", {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            current_table_id: this.selectedTable.id,
                            target_table_id: targetTableId
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.status === 'success') {
                        this.showToast(data.message, 'success');
                        this.isMoveModalOpen = false;
                        this.isCheckoutModalOpen = false; // បិទ Modal Check Bill
                        this.fetchTables(); // Refresh List តុខាងក្រៅ
                    } else {
                        this.showToast(data.message || 'Move failed', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast('System Error', 'error');
                }
            },

            // ==========================================
            // 8. SPLIT BILL FEATURES
            // ==========================================
            toggleSplitMode() {
                this.isSplitMode = !this.isSplitMode;
                this.selectedSplitItems = [];
                this.receivedAmount = this.isSplitMode ? 0 : this.orderDetails.total;
            },

            toggleSplitItem(item) {
                let existing = this.selectedSplitItems.find(i => i.id === item.id);
                if (existing) {
                    this.selectedSplitItems = this.selectedSplitItems.filter(i => i.id !== item.id);
                } else {
                    this.selectedSplitItems.push({ id: item.id, qty: item.quantity });
                }
                this.receivedAmount = this.currentTotalUSD;
            },

            isItemSplitted(itemId) {
                return this.selectedSplitItems.some(i => i.id === itemId);
            },

            async processSplitPayment() {
                if (this.selectedSplitItems.length === 0) return this.showToast('សូមជ្រើសរើសមុខម្ហូបដើម្បីបំបែក', 'warning');
                if (this.paymentMethod === 'cash' && (parseFloat(this.receivedAmount || 0) < this.currentTotalUSD)) return this.showToast('ទឹកប្រាក់មិនគ្រប់គ្រាន់', 'error');

                this.isProcessing = true;
                try {
                    const response = await fetch("{{ route('pos.order.split') }}", {
                        method: "POST",
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({
                            original_order_id: this.orderDetails.id,
                            split_items: this.selectedSplitItems,
                            payment_method: this.paymentMethod,
                            received_amount: this.receivedAmount
                        })
                    });
                    const data = await response.json();
                    if (response.ok) {
                        this.showToast('✅ បំបែកវិក្កយបត្រជោគជ័យ!', 'success');
                        setTimeout(() => { window.print(); }, 500);
                        if(data.remaining_items_count > 0) this.openQuickCheckout(this.selectedTable);
                        else {
                            this.isCheckoutModalOpen = false;
                            this.fetchTables();
                        }
                    } else this.showToast(data.message, 'error');
                } catch(e) { console.error(e); } 
                finally { this.isProcessing = false; }
            },

            // ==========================================
            // 9. HELPERS
            // ==========================================
            finishTransaction(data) {
                this.isCheckoutModalOpen = false;
                this.showToast(`✅ ជោគជ័យ! លុយអាប់: $${parseFloat(data.change).toFixed(2)}`, 'success');
                this.fetchTables();
                setTimeout(() => { window.print(); }, 500);
            },

            showToast(message, type = 'success') {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: message, type: type } }));
            }
        }
    }
</script>

<style>
    @media print {
        body * { visibility: hidden; height: 0; overflow: hidden; }
        #receipt-print-area {
            display: block !important; visibility: visible !important;
            position: absolute; left: 0; top: 0; width: 80mm;
            margin: 0 auto; padding: 0; height: auto !important;
            background-color: white !important; color: black !important;
        }
        #receipt-print-area * { visibility: visible !important; height: auto !important; }
        @page { margin: 0; size: auto; }
    }
</style>