<script>
    function posTables() {
        return {
            // ==========================================
            // 1. DATA (STATE)
            // ==========================================
            tables: [],
            isLoading: false,
            interval: null,
            
            // Modal State
            isCheckoutModalOpen: false,
            isLoadingOrder: false,
            isProcessing: false,
            
            // Order Data
            selectedTable: null,
            paymentMethod: 'cash',
            receivedAmount: '',
            exchangeRate: 4100,
            
            orderDetails: {
                id: null,
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
                // Refresh តុម្តងរៀងរាល់ 5 វិនាទី (ដរាបណាមិនទាន់បើក Checkout)
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

            get totalRiel() {
                const total = parseFloat(this.orderDetails.total || 0);
                return Math.ceil(total * this.exchangeRate).toLocaleString('km-KH');
            },
            
            // Helper: ពិនិត្យមើលថាជា Extra ឬអត់
            isExtraItem(item) {
                return item.product && item.product.name.toLowerCase().includes('extra');
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
            },

            // ==========================================
            // 3. OPEN CHECKOUT
            // ==========================================
            async openQuickCheckout(table) {
                if (table.status === 'available') {
                    this.showToast('តុនេះទំនេរ សូមធ្វើការកម្មង់ជាមុនសិន', 'warning');
                    return;
                }

                this.isLoading = true;
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
                    this.receivedAmount = this.orderDetails.total; // Auto fill amount
                    this.paymentMethod = 'cash';
                    this.isCheckoutModalOpen = true;

                } catch (error) {
                    console.error("Cannot load order:", error);
                    this.showToast("មានបញ្ហាក្នុងការទាញទិន្នន័យ Order", 'error');
                } finally {
                    this.isLoading = false;
                }
            },

            // ==========================================
            // 4. ITEM LOGIC
            // ==========================================
            updateItemQty(itemId, action) {
                let index = this.orderDetails.items.findIndex(i => i.id === itemId);
                if (index === -1) return;
                let item = this.orderDetails.items[index];

                if (action === 'increase') item.quantity++;
                else if (action === 'decrease') {
                    if (item.quantity > 1) item.quantity--;
                    else this.orderDetails.items.splice(index, 1);
                } else if (action === 'remove') {
                    this.orderDetails.items.splice(index, 1);
                }
                this.recalculateTotalLocal();
            },

            updateAddonQty(addonId, action) {
                for (let item of this.orderDetails.items) {
                    if (item.addons) {
                        let addonIndex = item.addons.findIndex(a => a.id === addonId);
                        if (addonIndex !== -1) {
                            let addon = item.addons[addonIndex];
                            
                            // បើជា Extra Item ការដក Addon ស្មើនឹងដក Item ធំ
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
            // 5. PAYMENT & PRINT LOGIC
            // ==========================================
            async processPayment() {
                // Validation
                if (this.paymentMethod === 'cash' && (parseFloat(this.receivedAmount || 0) < parseFloat(this.orderDetails.total))) {
                    this.showToast('ទឹកប្រាក់ទទួលបានមិនគ្រប់គ្រាន់!', 'error');
                    return;
                }

                if (this.orderDetails.items.length === 0) {
                     if(!confirm('ការបញ្ជាទិញគ្មានទិន្នន័យ។ តើអ្នកចង់ Cancel Order នេះទេ?')) return;
                }

                this.isProcessing = true;
                try {
                    const response = await fetch('/pos/checkout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
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
                        this.isCheckoutModalOpen = false;
                        this.showToast(`✅ ការទូទាត់ជោគជ័យ! លុយអាប់: $${parseFloat(data.change).toFixed(2)}`, 'success');
                        this.fetchTables();
                        
                        setTimeout(() => {
                            window.print();
                        }, 500);

                    } else {
                        this.showToast(data.message || 'Payment Failed', 'error');
                    }
                } catch (error) {
                    console.error(error);
                    this.showToast('System Error: សូមព្យាយាមម្តងទៀត', 'error');
                } finally {
                    this.isProcessing = false;
                }
            },

            // ==========================================
            // 6. TOAST HELPER FUNCTION
            // ==========================================
            showToast(message, type = 'success') {
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { message: message, type: type } 
                }));
            }
        }
    }
</script>