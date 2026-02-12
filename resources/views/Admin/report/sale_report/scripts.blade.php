<script>
    // --- 1. CONFIGURATION & STATE ---
    const currentLocale = "{{ app()->getLocale() }}";
    
    // Translation strings for JS (យកចេញពី messages.php)
    const lang = {
        loading: "{{ __('messages.loading_data') }}",
        noData: "{{ __('messages.no_transactions_found') }}",
        transactions: "{{ __('messages.transactions') }}",
        invoice: "{{ __('messages.invoice') }}",
        showing: "{{ __('messages.showing') }}",
        of: "{{ __('messages.of') }}"
    };

    let activeFilterType = 'day';
    let allOrdersData = []; // Store all fetched data here
    let currentLimit = 10;  // Default limit

    // Default Date Values
    const today = "{{ date('Y-m-d') }}";
    const currentMonth = "{{ date('Y-m') }}";
    const currentYear = "{{ date('Y') }}";

    // --- 2. INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', function() {
        fetchReport(); 
    });

    // Close Modal on Click Outside
    window.onclick = function(event) {
        const modal = document.getElementById('orderDetailModal');
        const backdrop = document.getElementById('modalBackdrop');
        if (event.target === backdrop) {
            closeModal();
        }
    }

    // --- 3. FILTER LOGIC ---
    
    // Switch between Day / Month / Year
    function setFilterType(type) {
        activeFilterType = type;
        
        ['day', 'month', 'year'].forEach(t => {
            const btn = document.getElementById(`btn-${t}`);
            const group = document.getElementById(`group-${t}`);
            
            if (t === type) {
                // Active State
                btn.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-200/50');
                btn.classList.add('bg-white', 'text-blue-600', 'shadow-sm'); 
                
                // Show Input Group
                group.classList.remove('hidden');
                group.classList.add('flex');
            } else {
                // Inactive State
                btn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:bg-gray-200/50');
                btn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                
                // Hide Input Group
                group.classList.add('hidden');
                group.classList.remove('flex');
            }
        });

        fetchReport();
    }

    // Reset Date Inputs
    function triggerReset() {
        if (activeFilterType === 'day') {
            document.getElementById('day-start').value = today;
            document.getElementById('day-end').value = today;
        } else if (activeFilterType === 'month') {
            document.getElementById('month-start').value = currentMonth;
            document.getElementById('month-end').value = currentMonth;
        } else if (activeFilterType === 'year') {
            document.getElementById('year-start').value = currentYear;
            document.getElementById('year-end').value = currentYear;
        }
        fetchReport();
    }

    // Update Limit (Show 10, 20...)
    function updateLimit(val) {
        currentLimit = val;
        renderRows(); // Re-render UI without fetching API again
    }

    // --- 4. DATA FETCHING ---
    function fetchReport() {
        const tbody = document.getElementById('reportTableBody'); 
        const mobileContainer = document.getElementById('reportMobileBody'); 

        // Construct URL
        let url = `{{ route('admin.report.sale_report.fetch') }}?filter_type=${activeFilterType}`;

        if (activeFilterType === 'day') {
            url += `&start_date=${document.getElementById('day-start').value}`;
            url += `&end_date=${document.getElementById('day-end').value}`;
        } else if (activeFilterType === 'month') {
            url += `&start_month=${document.getElementById('month-start').value}`;
            url += `&end_month=${document.getElementById('month-end').value}`;
        } else if (activeFilterType === 'year') {
            url += `&start_year=${document.getElementById('year-start').value}`;
            url += `&end_year=${document.getElementById('year-end').value}`;
        }

        // Show Loading State
        const loadingHTMLTable = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400"><i class="ri-loader-4-line animate-spin text-2xl"></i><br>${lang.loading}</td></tr>`;
        const loadingHTMLMobile = `<div class="text-center py-10 text-gray-400"><i class="ri-loader-4-line animate-spin text-3xl text-blue-500"></i><br><span class="text-xs mt-2">${lang.loading}</span></div>`;

        if(tbody) tbody.innerHTML = loadingHTMLTable;
        if(mobileContainer) mobileContainer.innerHTML = loadingHTMLMobile;

        // Fetch API
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'error') throw new Error(data.message);

                const isKhmer = currentLocale === 'km';

                // Update Summary Cards
                if(data.summary) {
                    updateSummary('summaryTotalSales', data.summary.total_sales_usd, data.summary.total_sales_khr, isKhmer);
                    updateSummary('summaryCash', data.summary.cash_usd, data.summary.cash_khr, isKhmer);
                    updateSummary('summaryQR', data.summary.qr_usd, data.summary.qr_khr, isKhmer);
                    if(document.getElementById('summaryTotalOrders')) {
                        document.getElementById('summaryTotalOrders').innerText = data.summary.total_orders;
                    }
                }

                // Store Data & Render
                allOrdersData = data.orders || [];
                renderRows();
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsg = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-500">Error: ${error.message}</td></tr>`;
                if(tbody) tbody.innerHTML = errorMsg;
                if(mobileContainer) mobileContainer.innerHTML = `<div class="text-center py-8 text-red-500">Error loading data</div>`;
            });
    }

    // Helper to format currency on cards
    function updateSummary(elementId, amountUsd, amountKhr, isKhmer) {
        const el = document.getElementById(elementId);
        if(!el) return;
        if(isKhmer) {
            el.innerText = new Intl.NumberFormat('en-US').format(amountKhr) + ' ៛';
        } else {
            el.innerText = '$' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(amountUsd);
        }
    }

    // --- 5. RENDER LOGIC ---
    function renderRows() {
        const tbody = document.getElementById('reportTableBody'); 
        const mobileContainer = document.getElementById('reportMobileBody');
        const showingCount = document.getElementById('showing-count');

        // Clear existing
        if(tbody) tbody.innerHTML = '';
        if(mobileContainer) mobileContainer.innerHTML = '';

        // Handle Empty State
        if (allOrdersData.length === 0) {
            const noDataHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">${lang.noData}</td></tr>`;
            if(tbody) tbody.innerHTML = noDataHTML;
            if(mobileContainer) mobileContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-gray-300">
                    <i class="ri-inbox-archive-line text-4xl mb-2"></i>
                    <span class="text-sm">${lang.noData}</span>
                </div>`;
            if(showingCount) showingCount.innerText = '0';
            return;
        }

        // Slice Data (Pagination Logic)
        let displayData = [];
        if (currentLimit === 'all') {
            displayData = allOrdersData;
        } else {
            displayData = allOrdersData.slice(0, parseInt(currentLimit));
        }

        // Update Footer Info
        if(showingCount) {
             showingCount.parentElement.innerHTML = `${lang.showing} <span class="font-bold text-gray-600">${displayData.length}</span> ${lang.of} ${allOrdersData.length} ${lang.transactions}`;
        }

        const isKhmer = currentLocale === 'km';

        // Loop & Generate HTML
        displayData.forEach(order => {
            let isCompleted = order.status.toLowerCase() === 'completed';
            let statusColor = isCompleted ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            let statusIcon = isCompleted ? '<i class="ri-checkbox-circle-fill"></i>' : '<i class="ri-close-circle-fill"></i>';
            let displayAmount = isKhmer ? `${order.total_khr} ៛` : `$${order.total_usd}`;
            let paymentIcon = order.payment.toLowerCase().includes('qr') ? 'ri-qr-code-line' : 'ri-money-dollar-circle-line';

            // Desktop Row (With onClick)
            if(tbody) {
                tbody.innerHTML += `
                    <tr onclick="openModal('${order.invoice}')" class="hover:bg-blue-50/50 transition-colors border-b border-gray-100 last:border-0 group cursor-pointer">
                        <td class="px-6 py-4 font-bold text-gray-800 group-hover:text-blue-600">#${order.invoice}</td>
                        <td class="px-6 py-4 text-gray-500 text-xs">${order.date}</td>
                        <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 capitalize">${order.payment}</span></td>
                        <td class="px-6 py-4 text-center"><span class="px-3 py-1 rounded-full text-xs font-bold ${statusColor} inline-flex items-center gap-1">${statusIcon} ${order.status}</span></td>
                        <td class="px-6 py-4 text-right font-black text-gray-800">${displayAmount}</td>
                    </tr>`;
            }

            // Mobile Card (With onClick)
            if(mobileContainer) {
                mobileContainer.innerHTML += `
                    <div onclick="openModal('${order.invoice}')" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden active:scale-[0.98] transition-transform duration-100 cursor-pointer">
                        <div class="absolute top-0 right-0 p-2 opacity-10 pointer-events-none">
                            <i class="${paymentIcon} text-6xl text-gray-500"></i>
                        </div>
                        
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider mb-0.5">${lang.invoice}</span>
                                <h4 class="font-bold text-gray-800 text-lg leading-none">#${order.invoice}</h4>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${statusColor} border border-transparent bg-opacity-20 mb-1">
                                    ${order.status}
                                </span>
                                <span class="text-[10px] text-gray-400 font-medium">${order.date.split(' ')[0]}</span>
                            </div>
                        </div>

                        <div class="w-full h-px bg-gray-100 my-2"></div>

                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-1.5 text-gray-500">
                                <div class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100">
                                    <i class="${paymentIcon} text-xs"></i>
                                </div>
                                <span class="text-xs font-semibold capitalize">${order.payment}</span>
                            </div>
                            <span class="font-black text-lg text-blue-600 tracking-tight">${displayAmount}</span>
                        </div>
                    </div>`;
            }
        });
    }

    // --- 6. MODAL FUNCTIONS ---

    function openModal(invoiceId) {
        // Find Data
        const order = allOrdersData.find(o => o.invoice === invoiceId);
        if (!order) return;

        const isKhmer = currentLocale === 'km';
        let displayAmount = isKhmer ? `${order.total_khr} ៛` : `$${order.total_usd}`;
        let statusColor = order.status.toLowerCase() === 'completed' ? 'text-green-600' : 'text-red-600';

        // Populate Modal DOM
        document.getElementById('modalInvoice').innerText = '#' + order.invoice;
        document.getElementById('modalDate').innerText = order.date;
        document.getElementById('modalPayment').innerText = order.payment;
        document.getElementById('modalCashier').innerText = "Admin"; // Hardcoded for now
        
        const statusEl = document.getElementById('modalStatus');
        statusEl.innerText = order.status;
        statusEl.className = `font-bold ${statusColor}`;

        document.getElementById('modalTotal').innerText = displayAmount;
        document.getElementById('modalGrandTotal').innerText = displayAmount;

        // Populate Items List (Optional: Update this logic if API returns items)
        const itemsList = document.getElementById('modalItemsList');
        if(order.items && order.items.length > 0) {
            itemsList.innerHTML = '';
            order.items.forEach(item => {
                itemsList.innerHTML += `
                    <div class="flex justify-between text-xs border-b border-gray-50 pb-1 last:border-0">
                        <span>${item.name} <span class="text-gray-400">x${item.qty}</span></span>
                        <span class="font-semibold">${isKhmer ? item.total_khr : item.total_usd}</span>
                    </div>`;
            });
        } else {
             itemsList.innerHTML = '<div class="text-center text-gray-400 text-xs py-4">No items detail available</div>';
        }

        // Show Modal (Animation)
        const modal = document.getElementById('orderDetailModal');
        const backdrop = document.getElementById('modalBackdrop');
        const panel = document.getElementById('modalPanel');

        modal.classList.remove('hidden');
        
        // Small delay to allow transition to kick in
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('translate-y-full', 'opacity-0');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('orderDetailModal');
        const backdrop = document.getElementById('modalBackdrop');
        const panel = document.getElementById('modalPanel');

        // Start Hide Animation
        backdrop.classList.add('opacity-0');
        panel.classList.add('translate-y-full', 'opacity-0');

        // Wait for animation to finish before hiding element
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>