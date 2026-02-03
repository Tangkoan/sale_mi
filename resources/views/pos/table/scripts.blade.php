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
            
            // Merge & Split State
            isMergeModalOpen: false,
            busyTables: [],
            isSplitMode: false,
            selectedSplitItems: [],
            
            // Order Data
            selectedTable: null,
            paymentMethod: 'cash',
            receivedAmount: '',
            exchangeRate: 4100,
            confirmEmpty: false, // សម្រាប់បញ្ជាក់ពេលលុបម្ហូបអស់
            
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

                    // 🔥 CLONE DATA: ទាញមកដាក់ក្នុង Local Variable សិន
                    this.orderDetails = {
                        ...data.order,
                        items: data.items, // Items នេះអាចកែប្រែបានតាមចិត្តមុនពេល Save
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

            // 🔥 Function គណនាលុយក្នុងម៉ាស៊ីន (Local)
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
                // Update លុយទទួលអូតូ (បើមិនមែន Split)
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
                    else this.orderDetails.items.splice(index, 1); // លុបចេញពី Array (Local)
                } else if (action === 'remove') {
                    this.orderDetails.items.splice(index, 1); // លុបចេញពី Array (Local)
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
                            
                            // Logic: បើជា Extra Item ការដក Addon ស្មើនឹងដក Item ធំ
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

                // Validation
                if (this.paymentMethod === 'cash' && (parseFloat(this.receivedAmount || 0) < this.currentTotalUSD)) {
                    this.showToast('ទឹកប្រាក់ទទួលបានមិនគ្រប់គ្រាន់!', 'error');
                    return;
                }
                
                // បើ User លុបម្ហូបអស់ពីវិក្កយបត្រ
                if (this.orderDetails.items.length === 0) {
                     if(!confirm('ការបញ្ជាទិញគ្មានទិន្នន័យ (បានលុបអស់)។ តើអ្នកចង់ Cancel Order នេះទេ?')) return;
                     this.confirmEmpty = true; // Allow processing
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
                            items: this.orderDetails.items // 🔥 សំខាន់៖ បោះទិន្នន័យចុងក្រោយទៅអោយ Server Update (Sync)
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
            // 7. MERGE TABLE FEATURES
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
                // លែងសួរ Confirm ពី Server, គ្រាន់តែសួរ User
                // if (!confirm('Confirm merge locally?')) return;

                try {
                    // 1. ហៅ API ថ្មី៖ គ្រាន់តែយកមុខម្ហូប មិនទាន់កែ Database
                    const response = await fetch(`/pos/order/items-for-merge/${targetTableId}`);
                    const data = await response.json();

                    if (data.items && data.items.length > 0) {
                        // 2. បញ្ចូលមុខម្ហូបថ្មីទៅក្នុង List បច្ចុប្បន្ន (Local State)
                        data.items.forEach(item => {
                            // យើងទុក ID ដើម ដើម្បីអោយ Checkout ស្គាល់ថាវាជា Item មានស្រាប់ក្នុង DB
                            // Checkout នឹងធ្វើការផ្ទេរ (Move) តាមក្រោយ
                            this.orderDetails.items.push(item);
                        });

                        // 3. គណនាលុយសរុបឡើងវិញលើអេក្រង់
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
                // ហៅផ្ទាំង Print (អាចប្រើ window.print() ឬ function print ផ្ទាល់ខ្លួន)
                setTimeout(() => { window.print(); }, 500);
            },

            showToast(message, type = 'success') {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: message, type: type } }));
            }
        }
    }
</script>