<script>
    function posTables() {
        return {
            tables: [],
            isLoading: false,
            interval: null,
            isCheckoutModalOpen: false,
            isLoadingOrder: false,
            isProcessing: false,
            selectedTable: null,
            orderDetails: { items: [], total: 0, invoice_number: '' },
            paymentMethod: 'cash',
            receivedAmount: '',
            exchangeRate: 4100, // អត្រាប្ដូរប្រាក់ (អាចកែនៅទីនេះ ឬទាញពី DB)

            init() {
                this.fetchTables();
                this.interval = setInterval(() => { 
                    if (!this.isCheckoutModalOpen) this.fetchTables(true); 
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

            // គណនាលុយរៀល
            get totalRiel() {
                const total = parseFloat(this.orderDetails.total || 0);
                return Math.ceil(total * this.exchangeRate).toLocaleString('km-KH'); // Round up and format
            },

            async openQuickCheckout(table) {
                this.selectedTable = table;
                this.isCheckoutModalOpen = true;
                this.isLoadingOrder = true;
                this.paymentMethod = 'cash';
                this.receivedAmount = '';
                await this.fetchOrderDetails(table.id);
            },

            async fetchOrderDetails(tableId) {
                try {
                    const response = await fetch(`/pos/order-details/${tableId}`);
                    if (!response.ok) throw new Error("Order not found");
                    this.orderDetails = await response.json();
                    
                    // Auto fill received amount if it's empty or was equal to previous total
                    if(this.receivedAmount === '' || this.receivedAmount == 0) {
                         this.receivedAmount = this.orderDetails.total; 
                    }
                } catch (error) {
                    console.error("Cannot load order:", error);
                    // បើគ្មាន Order, Reset
                    this.orderDetails = { items: [], total: 0 };
                } finally {
                    this.isLoadingOrder = false;
                }
            },

            // 🔥 FUNCTION សម្រាប់កែចំនួន និងលុប
            async updateItemQty(itemId, action) {
                // បិទការចុចស្ទួនៗ
                if (this.isLoadingOrder) return;
                this.isLoadingOrder = true; // Show loading state on details

                try {
                    const response = await fetch("{{ route('pos.order.update-item') }}", {
                        method: "POST",
                        headers: { 
                            "Content-Type": "application/json", 
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify({ item_id: itemId, action: action })
                    });
                    
                    if (response.ok) {
                        // Reload order details to reflect DB changes
                        await this.fetchOrderDetails(this.selectedTable.id);
                        // Reset received amount to new total automatically for convenience
                        this.receivedAmount = this.orderDetails.total; 
                    } else {
                        alert("Update failed");
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    this.isLoadingOrder = false;
                }
            },


            // 🔥 FUNCTION សម្រាប់កែចំនួនAddon
            async updateAddonQty(addonRowId, action) {
                if (this.isLoadingOrder) return;
                this.isLoadingOrder = true;

                try {
                    const response = await fetch("{{ route('pos.order.update-addon') }}", {
                        method: "POST",
                        headers: { 
                            "Content-Type": "application/json", 
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify({ addon_row_id: addonRowId, action: action })
                    });
                    
                    if (response.ok) {
                        await this.fetchOrderDetails(this.selectedTable.id);
                        this.receivedAmount = this.orderDetails.total; // Update តម្លៃដែលត្រូវបង់
                    } else {
                        alert("Update Addon Failed");
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    this.isLoadingOrder = false;
                }
            },

            async processPayment() {
                this.isProcessing = true;
                try {
                    const response = await fetch("{{ route('pos.order.checkout') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: JSON.stringify({
                            table_id: this.selectedTable.id,
                            received_amount: this.receivedAmount,
                            payment_method: this.paymentMethod
                        })
                    });
                    
                    const result = await response.json();

                    if (response.ok) {
                        this.isCheckoutModalOpen = false;
                        setTimeout(() => window.print(), 300);
                        window.onafterprint = () => { this.fetchTables(); };
                        setTimeout(() => { this.fetchTables(); }, 3000);
                    } else {
                        alert(result.message || "Payment Failed!");
                    }
                } catch (error) {
                    console.error(error);
                    alert("Error processing payment.");
                } finally {
                    this.isProcessing = false;
                }
            }


        }
    }
</script>