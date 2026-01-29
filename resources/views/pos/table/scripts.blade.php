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
            orderDetails: { items: [], total: 0 },
            paymentMethod: 'cash',
            receivedAmount: '',

            init() {
                this.fetchTables();
                this.interval = setInterval(() => { if (!this.isCheckoutModalOpen) this.fetchTables(true); }, 5000);
            },

            async fetchTables(silent = false) {
                if (!silent) this.isLoading = true;
                try {
                    const response = await fetch("{{ route('pos.tables.fetch') }}");
                    this.tables = await response.json();
                } catch (error) { console.error(error); } 
                finally { if (!silent) this.isLoading = false; }
            },

            async openQuickCheckout(table) {
                this.selectedTable = table;
                this.isCheckoutModalOpen = true;
                this.isLoadingOrder = true;
                this.paymentMethod = 'cash';
                this.receivedAmount = '';

                try {
                    const response = await fetch(`/pos/order-details/${table.id}`);
                    if (!response.ok) throw new Error("Order not found");
                    this.orderDetails = await response.json();
                    this.receivedAmount = this.orderDetails.total; 
                } catch (error) {
                    alert("Cannot load order details.");
                    this.isCheckoutModalOpen = false;
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
                    
                    if (response.ok) {
                        this.isCheckoutModalOpen = false;
                        setTimeout(() => window.print(), 300);
                        window.onafterprint = () => { this.fetchTables(); };
                        setTimeout(() => { this.fetchTables(); }, 3000);
                    } else {
                        alert("Payment Failed!");
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