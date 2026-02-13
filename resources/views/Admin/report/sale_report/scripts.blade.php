<script>
    // --- 1. CONFIGURATION & STATE ---
    const currentLocale = "{{ app()->getLocale() }}";
    
    const lang = {
        loading: "{{ __('messages.loading_data') }}",
        noData: "{{ __('messages.no_transactions_found') }}",
        transactions: "{{ __('messages.transactions') }}",
        invoice: "{{ __('messages.invoice') }}",
        showing: "{{ __('messages.showing') }}",
        of: "{{ __('messages.of') }}"
    };

    let activeFilterType = 'day';
    let allOrdersData = []; 
    let currentLimit = 10; 

    const today = "{{ date('Y-m-d') }}";
    const currentMonth = "{{ date('Y-m') }}";
    const currentYear = "{{ date('Y') }}";

    // --- 2. INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', function() {
        fetchReport(); 
    });

    window.onclick = function(event) {
        const modal = document.getElementById('orderDetailModal');
        const backdrop = document.getElementById('modalBackdrop');
        if (event.target === backdrop) {
            closeModal();
        }
    }

    // --- 3. FILTER LOGIC ---
    function setFilterType(type) {
        activeFilterType = type;
        
        ['day', 'month', 'year'].forEach(t => {
            const btn = document.getElementById(`btn-${t}`);
            const group = document.getElementById(`group-${t}`);
            
            if (t === type) {
                btn.classList.remove('text-sidebar-text', 'hover:text-primary', 'hover:bg-input-bg');
                btn.classList.add('bg-card-bg', 'text-primary', 'shadow-custom'); 
                group.classList.remove('hidden');
                group.classList.add('flex');
            } else {
                btn.classList.add('text-sidebar-text', 'hover:text-primary', 'hover:bg-input-bg');
                btn.classList.remove('bg-card-bg', 'text-primary', 'shadow-custom');
                group.classList.add('hidden');
                group.classList.remove('flex');
            }
        });

        fetchReport();
    }

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

    function updateLimit(val) {
        currentLimit = val;
        renderRows(); 
    }

    // --- 4. DATA FETCHING ---
    function fetchReport() {
        const tbody = document.getElementById('reportTableBody'); 
        const mobileContainer = document.getElementById('reportMobileBody'); 

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

        const loadingHTMLTable = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400"><i class="ri-loader-4-line animate-spin text-2xl"></i><br>${lang.loading}</td></tr>`;
        const loadingHTMLMobile = `<div class="text-center py-10 text-gray-400"><i class="ri-loader-4-line animate-spin text-3xl text-primary"></i><br><span class="text-xs mt-2">${lang.loading}</span></div>`;

        if(tbody) tbody.innerHTML = loadingHTMLTable;
        if(mobileContainer) mobileContainer.innerHTML = loadingHTMLMobile;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'error') throw new Error(data.message);

                const isKhmer = currentLocale === 'km';

                if(data.summary) {
                    updateSummary('summaryTotalSales', data.summary.total_sales_usd, data.summary.total_sales_khr, isKhmer);
                    updateSummary('summaryCash', data.summary.cash_usd, data.summary.cash_khr, isKhmer);
                    updateSummary('summaryQR', data.summary.qr_usd, data.summary.qr_khr, isKhmer);
                    if(document.getElementById('summaryTotalOrders')) {
                        document.getElementById('summaryTotalOrders').innerText = data.summary.total_orders;
                    }
                }

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

        if(tbody) tbody.innerHTML = '';
        if(mobileContainer) mobileContainer.innerHTML = '';

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

        let displayData = [];
        if (currentLimit === 'all') {
            displayData = allOrdersData;
        } else {
            displayData = allOrdersData.slice(0, parseInt(currentLimit));
        }

        if(showingCount) {
             showingCount.parentElement.innerHTML = `${lang.showing} <span class="font-bold text-sidebar-text">${displayData.length}</span> ${lang.of} ${allOrdersData.length} ${lang.transactions}`;
        }

        const isKhmer = currentLocale === 'km';

        displayData.forEach(order => {
            // 🔥 FIXED: Defensively handle null/undefined values
            let statusSafe = (order.status || '').toString();
            let paymentSafe = (order.payment || '').toString();
            let dateSafe = (order.date || '').toString(); // ✅ បន្ថែម dateSafe ដើម្បីការពារ error .split

            let isCompleted = statusSafe.toLowerCase() === 'completed';
            let statusColor = isCompleted ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            let statusIcon = isCompleted ? '<i class="ri-checkbox-circle-fill"></i>' : '<i class="ri-close-circle-fill"></i>';
            let displayAmount = isKhmer ? `${order.total_khr} ៛` : `$${order.total_usd}`;
            let paymentIcon = paymentSafe.toLowerCase().includes('qr') ? 'ri-qr-code-line' : 'ri-money-dollar-circle-line';
            
            // Desktop Row
            if(tbody) {
                tbody.innerHTML += `
                    <tr onclick="openModal('${order.invoice}')" class="hover:bg-page-bg transition-colors border-b border-bor-color last:border-0 group cursor-pointer">
                        <td class="px-6 py-4 font-bold text-sidebar-text group-hover:text-primary">#${order.invoice}</td>
                        <td class="px-6 py-4 text-gray-500 text-xs">${dateSafe}</td>
                        <td class="px-6 py-4"><span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-page-bg text-gray-600 border border-bor-color capitalize">${paymentSafe}</span></td>
                        <td class="px-6 py-4 text-center"><span class="px-3 py-1 rounded-full text-xs font-bold ${statusColor} inline-flex items-center gap-1">${statusIcon} ${statusSafe}</span></td>
                        <td class="px-6 py-4 text-right font-black text-sidebar-text">${displayAmount}</td>
                    </tr>`;
            }

            // Mobile Card
            if(mobileContainer) {
                // ✅ ប្រើ dateSafe.split ជំនួស order.date.split
                let shortDate = dateSafe.includes(' ') ? dateSafe.split(' ')[0] : dateSafe;

                mobileContainer.innerHTML += `
                    <div onclick="openModal('${order.invoice}')" class="bg-card-bg p-4 rounded-2xl shadow-custom border border-bor-color relative overflow-hidden active:scale-[0.98] transition-transform duration-100 cursor-pointer">
                        <div class="absolute top-0 right-0 p-2 opacity-10 pointer-events-none">
                            <i class="${paymentIcon} text-6xl text-gray-500"></i>
                        </div>
                        
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider mb-0.5">${lang.invoice}</span>
                                <h4 class="font-bold text-sidebar-text text-lg leading-none">#${order.invoice}</h4>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ${statusColor} border border-transparent bg-opacity-20 mb-1">
                                    ${statusSafe}
                                </span>
                                <span class="text-[10px] text-gray-400 font-medium">${shortDate}</span>
                            </div>
                        </div>

                        <div class="w-full h-px bg-bor-color my-2"></div>

                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-1.5 text-gray-500">
                                <div class="w-6 h-6 rounded-full bg-page-bg flex items-center justify-center border border-bor-color">
                                    <i class="${paymentIcon} text-xs"></i>
                                </div>
                                <span class="text-xs font-semibold capitalize">${paymentSafe}</span>
                            </div>
                            <span class="font-black text-lg text-primary tracking-tight">${displayAmount}</span>
                        </div>
                    </div>`;
            }
        });
    }

    // --- 6. MODAL FUNCTIONS ---

    function openModal(invoiceId) {
        const order = allOrdersData.find(o => o.invoice === invoiceId);
        if (!order) return;

        // 🔥 FIXED: Defensively handle null/undefined values
        let statusSafe = (order.status || '').toString();
        let paymentSafe = (order.payment || '').toString();
        let dateSafe = (order.date || '').toString();

        const isKhmer = currentLocale === 'km';
        let displayAmount = isKhmer ? `${order.total_khr} ៛` : `$${order.total_usd}`;
        let statusColor = statusSafe.toLowerCase() === 'completed' ? 'text-green-600' : 'text-red-600';

        document.getElementById('modalInvoice').innerText = '#' + order.invoice;
        document.getElementById('modalDate').innerText = dateSafe;
        document.getElementById('modalPayment').innerText = paymentSafe;
        document.getElementById('modalCashier').innerText = "Admin"; 
        
        const statusEl = document.getElementById('modalStatus');
        statusEl.innerText = statusSafe;
        statusEl.className = `font-bold ${statusColor}`;

        document.getElementById('modalTotal').innerText = displayAmount;
        document.getElementById('modalGrandTotal').innerText = displayAmount;

        const itemsList = document.getElementById('modalItemsList');
        if(order.items && order.items.length > 0) {
            itemsList.innerHTML = '';
            order.items.forEach(item => {
                itemsList.innerHTML += `
                    <div class="flex justify-between text-xs border-b border-bor-color pb-1 last:border-0">
                        <span class="text-sidebar-text">${item.name} <span class="text-gray-400">x${item.qty}</span></span>
                        <span class="font-semibold text-sidebar-text">${isKhmer ? item.total_khr : item.total_usd}</span>
                    </div>`;
            });
        } else {
             itemsList.innerHTML = '<div class="text-center text-gray-400 text-xs py-4">No items detail available</div>';
        }

        const modal = document.getElementById('orderDetailModal');
        const backdrop = document.getElementById('modalBackdrop');
        const panel = document.getElementById('modalPanel');

        modal.classList.remove('hidden');
        
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('translate-y-full', 'opacity-0');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('orderDetailModal');
        const backdrop = document.getElementById('modalBackdrop');
        const panel = document.getElementById('modalPanel');

        backdrop.classList.add('opacity-0');
        panel.classList.add('translate-y-full', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // --- 7. EXPORT FUNCTION ---
    function exportReport(type) {
        let url = "";
        
        if (type === 'excel') {
            url = "{{ route('admin.report.sale_report.export_excel') }}";
        } else {
            url = "{{ route('admin.report.sale_report.export_pdf') }}";
        }

        url += `?filter_type=${activeFilterType}`;

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

        window.open(url, '_blank');
    }
</script>