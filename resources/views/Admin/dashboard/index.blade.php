@extends('admin.dashboard')
@section('title', __('messages.dashboard'))

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="min-h-screen bg-page-bg pb-10 font-sans text-sidebar-text transition-colors duration-300">

    {{-- ================================================================================== --}}
    {{--  ផ្នែកទី ១: HEADER & FILTER (នៅខាងក្រៅ Loading Area ដើម្បីកុំអោយបាត់ពេល Load)  --}}
    {{-- ================================================================================== --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end mb-8 gap-6 pt-6 px-1">
        <div>
            <h1 class="text-3xl font-extrabold text-sidebar-text tracking-tight">{{ __('messages.dashboard') }}</h1>
            <p class="text-gray-500 font-medium mt-1 flex items-center gap-2">
                <i class="ri-calendar-line text-primary"></i>
                {{ __('messages.overview_for') }} <span id="comparisonText" class="text-sidebar-text font-bold">{{ $comparisonText }}</span>
            </p>
        </div>

        {{-- Filter Controls --}}
        <div class="bg-card-bg p-1.5 rounded-2xl shadow-custom border border-bor-color flex flex-col sm:flex-row gap-2 w-full xl:w-auto">
            {{-- Type Buttons --}}
            <div class="flex bg-input-bg rounded-xl p-1 relative border border-input-border">
                <button onclick="setFilter('day')" id="btn-day" class="filter-btn flex-1 px-5 py-2 text-xs font-bold uppercase rounded-lg transition-all {{ $filter=='day' ? 'bg-card-bg text-primary shadow-sm' : 'text-gray-500 hover:text-sidebar-text' }}">{{ __('messages.day') }}</button>
                <button onclick="setFilter('month')" id="btn-month" class="filter-btn flex-1 px-5 py-2 text-xs font-bold uppercase rounded-lg transition-all {{ $filter=='month' ? 'bg-card-bg text-primary shadow-sm' : 'text-gray-500 hover:text-sidebar-text' }}">{{ __('messages.month') }}</button>
                <button onclick="setFilter('year')" id="btn-year" class="filter-btn flex-1 px-5 py-2 text-xs font-bold uppercase rounded-lg transition-all {{ $filter=='year' ? 'bg-card-bg text-primary shadow-sm' : 'text-gray-500 hover:text-sidebar-text' }}">{{ __('messages.year') }}</button>
            </div>

            {{-- Date Inputs --}}
            <div class="relative min-w-[180px] flex items-center px-2">
                <input type="date" id="dateInput" value="{{ $dateInput }}" onchange="fetchData()" class="w-full bg-transparent border-none text-sm font-bold text-sidebar-text focus:ring-0 {{ $filter != 'day' ? 'hidden' : '' }}">
                <input type="month" id="monthInput" value="{{ $monthInput }}" onchange="fetchData()" class="w-full bg-transparent border-none text-sm font-bold text-sidebar-text focus:ring-0 {{ $filter != 'month' ? 'hidden' : '' }}">
                <select id="yearInput" onchange="fetchData()" class="w-full bg-transparent border-none text-sm font-bold text-sidebar-text focus:ring-0 {{ $filter != 'year' ? 'hidden' : '' }}">
                    @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ $yearInput == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    {{-- ================================================================================== --}}
    {{--  ផ្នែកទី ២: MAIN CONTENT WRAPPER (ដាក់ Loading Overlay ក្នុងនេះ)                 --}}
    {{-- ================================================================================== --}}
    <div class="relative min-h-[400px]"> {{-- ថែម class relative នៅទីនេះ --}}

        {{-- Loading Overlay (ឥឡូវវាស្ថិតនៅក្រោម Header ហើយ) --}}
        {{-- ខ្ញុំបានដូរ bg-page-bg ទៅជា bg-page-bg/80 ដើម្បីអោយមើលទៅឃើញស្រមោលៗបន្តិច (Modern) ឬដាក់ bg-page-bg ដូចដើមបើចង់បិទជិត --}}
        <div id="loadingOverlay" class="absolute inset-0 bg-page-bg/90 z-50 hidden flex flex-col items-center justify-center rounded-3xl backdrop-blur-sm transition-all duration-300">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-primary mb-4"></div>
            <p class="text-sidebar-text font-bold text-lg animate-pulse">{{ __('messages.loading_data') }}</p>
        </div>

        {{-- COLORFUL METRICS GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            
            {{-- Card 1: Revenue --}}
            <div class="relative overflow-hidden rounded-3xl p-6 bg-gradient-to-br from-slate-800 to-black text-white shadow-custom group border border-white/10">
                <div class="absolute top-0 right-0 p-4 opacity-10"><i class="ri-money-dollar-circle-fill text-8xl"></i></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-white/10 rounded-xl backdrop-blur-sm"><i class="ri-wallet-3-line text-xl"></i></div>
                        <span id="growthBadge" class="px-2 py-1 rounded-lg text-xs font-bold bg-white/10 text-white flex items-center gap-1">
                            <i class="ri-arrow-up-line"></i> <span id="growthValue">{{ number_format($growth, 1) }}</span>%
                        </span>
                    </div>
                    <p class="text-slate-300 text-xs font-bold uppercase tracking-widest mb-1">{{ __('messages.total_revenue') }}</p>
                    <h3 class="text-3xl font-black tracking-tight">$<span id="totalSales">{{ number_format($totalSales, 2) }}</span></h3>
                </div>
            </div>

            {{-- Card 2: Orders --}}
            <div class="relative overflow-hidden rounded-3xl p-6 bg-gradient-to-br from-orange-400 to-pink-500 text-white shadow-custom border border-white/10">
                <div class="absolute -bottom-4 -right-4 opacity-20"><i class="ri-shopping-bag-3-fill text-9xl transform -rotate-12"></i></div>
                <div class="relative z-10">
                    <div class="mb-4 p-2 w-fit bg-white/20 rounded-xl backdrop-blur-sm"><i class="ri-shopping-cart-2-line text-xl"></i></div>
                    <p class="text-white/80 text-xs font-bold uppercase">{{ __('messages.total_orders') }}</p>
                    <h3 class="text-3xl font-black"><span id="totalOrders">{{ number_format($totalOrders) }}</span></h3>
                </div>
            </div>

            {{-- Card 3: Cash Sales --}}
            <div class="relative overflow-hidden rounded-3xl p-6 bg-gradient-to-br from-emerald-400 to-teal-600 text-white shadow-custom border border-white/10">
                <div class="absolute -top-6 -right-6 opacity-20"><i class="ri-cash-line text-9xl"></i></div>
                <div class="relative z-10">
                    <div class="mb-4 p-2 w-fit bg-white/20 rounded-xl backdrop-blur-sm"><i class="ri-hand-coin-line text-xl"></i></div>
                    <p class="text-white/80 text-xs font-bold uppercase">{{ __('messages.cash_sales') }}</p>
                    <h3 class="text-2xl font-black mb-2">$<span id="totalCash">{{ number_format($totalCash, 2) }}</span></h3>
                    <div class="w-full bg-black/10 rounded-full h-1">
                        <div id="cashBar" class="bg-white h-1 rounded-full transition-all duration-500" style="width: {{ $totalSales > 0 ? ($totalCash/$totalSales)*100 : 0 }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Card 4: QR Payments --}}
            <div class="relative overflow-hidden rounded-3xl p-6 bg-gradient-to-br from-blue-400 to-cyan-500 text-white shadow-custom border border-white/10">
                <div class="absolute bottom-0 right-0 opacity-20"><i class="ri-qr-scan-2-line text-8xl"></i></div>
                <div class="relative z-10">
                    <div class="mb-4 p-2 w-fit bg-white/20 rounded-xl backdrop-blur-sm"><i class="ri-qr-code-line text-xl"></i></div>
                    <p class="text-white/80 text-xs font-bold uppercase">{{ __('messages.qr_payments') }}</p>
                    <h3 class="text-2xl font-black mb-2">$<span id="totalQR">{{ number_format($totalQR, 2) }}</span></h3>
                    <div class="w-full bg-black/10 rounded-full h-1">
                        <div id="qrBar" class="bg-white h-1 rounded-full transition-all duration-500" style="width: {{ $totalSales > 0 ? ($totalQR/$totalSales)*100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ANALYTICS & CHARTS --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            <div class="xl:col-span-2 bg-card-bg p-6 rounded-3xl border border-bor-color shadow-custom">
                <h3 class="text-lg font-bold text-sidebar-text mb-6">{{ __('messages.sales_analytics') }}</h3>
                <div class="h-72 w-full"><canvas id="salesChart"></canvas></div>
            </div>
            
            <div class="bg-card-bg p-6 rounded-3xl border border-bor-color shadow-custom flex flex-col">
                <h3 class="text-lg font-bold text-sidebar-text mb-6">{{ __('messages.top_products') }}</h3>
                <div id="topProductsList" class="flex-1 overflow-y-auto space-y-4 custom-scrollbar pr-2">
                    @include('admin.dashboard.partials.top_products')
                </div>
            </div>
        </div>
    </div> {{-- End Relative Wrapper --}}

</div>

{{-- SCRIPT នៅដដែលទាំងអស់ (Logic រក្សាដដែល) --}}
<script>
    let currentFilter = '{{ $filter }}';
    let salesChartInstance = null;

    // 1. Setup Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)'); 
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    function initChart(labels, data) {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

        if(salesChartInstance) salesChartInstance.destroy();
        
        salesChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: '{{ __('messages.revenue') }}', // បកប្រែក្នុង Chart
                    data: data,
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { 
                        grid: { display: false }, 
                        ticks: { font: { size: 10, family: "'Figtree', sans-serif" }, color: textColor } 
                    },
                    y: { 
                        grid: { color: gridColor, borderDash: [5, 5] }, 
                        beginAtZero: true,
                        ticks: { color: textColor }
                    }
                }
            }
        });
    }

    initChart(@json($chartLabels), @json($chartData));

    function setFilter(type) {
        currentFilter = type;
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('bg-card-bg', 'text-primary', 'shadow-sm');
            btn.classList.add('text-gray-500');
        });
        const activeBtn = document.getElementById('btn-' + type);
        activeBtn.classList.remove('text-gray-500');
        activeBtn.classList.add('bg-card-bg', 'text-primary', 'shadow-sm');

        document.getElementById('dateInput').classList.add('hidden');
        document.getElementById('monthInput').classList.add('hidden');
        document.getElementById('yearInput').classList.add('hidden');

        if(type === 'day') document.getElementById('dateInput').classList.remove('hidden');
        else if(type === 'month') document.getElementById('monthInput').classList.remove('hidden');
        else document.getElementById('yearInput').classList.remove('hidden');

        fetchData();
    }

    function fetchData() {
        const date = document.getElementById('dateInput').value;
        const month = document.getElementById('monthInput').value;
        const year = document.getElementById('yearInput').value;

        // បង្ហាញ Loading
        document.getElementById('loadingOverlay').classList.remove('hidden');
        document.getElementById('loadingOverlay').classList.add('flex'); // Ensure flex is added back

        // Reset Text (Optional)
        // document.getElementById('totalSales').innerText = "...";

        const url = `{{ route('admin.dashboard') }}?ajax=1&filter=${currentFilter}&date=${date}&month=${month}&year=${year}`;

        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            document.getElementById('totalSales').innerText = data.totalSales;
            document.getElementById('totalOrders').innerText = data.totalOrders;
            document.getElementById('totalCash').innerText = data.totalCash;
            document.getElementById('totalQR').innerText = data.totalQR;
            document.getElementById('comparisonText').innerText = data.comparisonText;
            document.getElementById('growthValue').innerText = data.growth;
            document.getElementById('cashBar').style.width = data.cashPercent + '%';
            document.getElementById('qrBar').style.width = data.qrPercent + '%';

            initChart(data.chartLabels, data.chartData);
            document.getElementById('topProductsList').innerHTML = data.topProductsHtml;
        })
        .catch(error => {
            console.error('Error:', error);
        })
        .finally(() => {
            setTimeout(() => {
                document.getElementById('loadingOverlay').classList.add('hidden');
                document.getElementById('loadingOverlay').classList.remove('flex');
            }, 300);
        });
    }
</script>
@endsection