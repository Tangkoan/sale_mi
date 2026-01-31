{{-- ALPINE DATA FOR HEADER & EXCHANGE RATE --}}
<div x-data="headerController()" x-init="init()" class="contents">

    {{-- ================================================================================== --}}
    {{-- MAIN HEADER BAR                                                                    --}}
    {{-- ================================================================================== --}}
    <div class="z-30 shrink-0 bg-white/80 dark:bg-gray-800/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0">
        <div class="px-3 py-2 sm:px-4 sm:py-3 flex items-center justify-between gap-3">
            
            {{-- LEFT: Table Info & Back --}}
            <div class="flex items-center gap-2 sm:gap-3 overflow-hidden flex-1 min-w-0">
                <a href="{{ route('pos.tables') }}" class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-200 transition flex items-center justify-center">
                    <i class="ri-arrow-left-line text-lg sm:text-xl"></i>
                </a>
                
                <div class="flex flex-col min-w-0 border-l pl-2 sm:pl-3 border-gray-300 dark:border-gray-600">
                    <h2 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white truncate leading-tight">{{ $table->name ?? 'Unknown Table' }}</h2>
                    <div class="flex items-center gap-2 text-[10px] sm:text-xs font-medium truncate">
                        @if(isset($currentOrder) && $currentOrder) 
                            <span class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-md truncate">#{{ $currentOrder->invoice_number }}</span>
                        @else
                            <span class="bg-green-100 text-green-600 px-1.5 py-0.5 rounded-md truncate">{{ __('messages.new_order') }}</span>
                        @endif
                        
                        {{-- Polling Status Indicator --}}
                        <span class="flex items-center gap-1 text-gray-400" x-show="isPolling" style="display: none;">
                            <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-green-500"></span>
                            </span>
                            <span class="hidden xs:inline">Live</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Actions --}}
            <div class="flex items-center gap-2">
                
                {{-- ✅ NEW: Exchange Rate Button --}}
                <button @click="openExchangeModal()" 
                        class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-200 transition flex-shrink-0 relative group" 
                        title="Exchange Rate">
                    <i class="ri-exchange-dollar-line text-lg sm:text-xl"></i>
                    {{-- Tooltip Rate --}}
                    <div class="absolute top-full mt-2 right-0 bg-gray-900 text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none z-50">
                        1$ = <span x-text="formatNumber(exchangeRate)"></span>៛
                    </div>
                </button>

                {{-- Quick Addon Button --}}
                <button @click="openQuickAddon()" class="bg-purple-100 text-purple-700 hover:bg-purple-200 w-8 h-8 sm:w-auto sm:h-auto sm:px-4 sm:py-2 rounded-lg sm:rounded-xl font-bold transition flex items-center justify-center gap-2">
                    <i class="ri-add-circle-fill text-lg sm:text-xl"></i> 
                    <span class="hidden sm:inline">Addon</span>
                </button>

                {{-- Kitchen Link --}}
                <a href="{{ route('pos.kitchen.view') }}" target="_blank" class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-orange-100 text-orange-600 hover:bg-orange-200 transition flex-shrink-0" title="Kitchen Screen">
                    <i class="ri-fire-line text-lg sm:text-xl"></i>
                </a>

                {{-- Search Trigger --}}
                <button @click="toggleSearch()" 
                        class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200"
                        :class="isSearchOpen ? 'bg-primary text-white border-primary ring-2 ring-primary/30' : 'bg-white dark:bg-gray-800'">
                    <i class="ri-search-line text-lg sm:text-xl" :class="isSearchOpen ? 'text-white' : ''"></i>
                </button>
            </div>
        </div>

        {{-- Search Bar Expandable --}}
        <div x-show="isSearchOpen" x-transition class="px-3 sm:px-4 pb-2 sm:pb-3">
            <div class="relative">
                <i class="ri-search-line absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="search" placeholder="Search menu..." class="w-full pl-9 sm:pl-11 pr-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl border-0 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary outline-none transition-all shadow-inner text-sm">
            </div>
        </div>

        {{-- Category Tabs --}}
        @if(isset($categories))
        <div class="px-3 sm:px-4 pb-2 sm:pb-3 overflow-x-auto no-scrollbar flex gap-2 sm:gap-3 snap-x">
            <button @click="setCategory('all')" 
                    class="snap-start flex-shrink-0 px-3 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-bold transition-all duration-300 border shadow-sm select-none" 
                    :class="activeCategory === 'all' ? 'bg-primary border-primary text-white shadow-primary/30 scale-105' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'">
                All
            </button>
            @foreach($categories as $cat)
                <button @click="setCategory({{ $cat->id }})" 
                        class="snap-start flex-shrink-0 px-3 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-bold transition-all duration-300 border shadow-sm select-none" 
                        :class="activeCategory === {{ $cat->id }} ? 'bg-primary border-primary text-white shadow-primary/30 scale-105' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ================================================================================== --}}
    {{-- MODAL: EXCHANGE RATE                                                               --}}
    {{-- ================================================================================== --}}
    <div x-show="isExchangeModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         x-cloak 
         x-transition.opacity>
        
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all" 
             @click.away="isExchangeModalOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            {{-- Modal Header --}}
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="ri-coins-line text-emerald-500"></i> Exchange Rate
                </h3>
                <button @click="isExchangeModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 bg-gray-200 dark:bg-gray-700 w-8 h-8 rounded-full flex items-center justify-center transition">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-5">
                
                {{-- Current Rate Display --}}
                <div class="text-center p-5 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider mb-2">Current System Rate</p>
                    <div class="text-3xl sm:text-4xl font-black text-gray-800 dark:text-white">
                        <span class="text-lg text-gray-400 font-medium align-top mt-1 inline-block">$1 =</span> 
                        <span x-text="formatNumber(exchangeRate)"></span> 
                        <span class="text-base text-gray-500 font-bold">KHR</span>
                    </div>
                </div>

                {{-- Manual Input --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 ml-1">Set New Rate</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-400 font-bold">៛</span>
                        </div>
                        <input type="number" 
                               x-model="tempExchangeRate" 
                               class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition font-bold text-xl tabular-nums" 
                               placeholder="Ex: 4100">
                    </div>
                </div>

                {{-- Auto Fetch Button --}}
                <button @click="fetchRateFromApi()" 
                        class="w-full py-3 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50 hover:text-primary transition flex items-center justify-center gap-2 text-sm font-semibold group"
                        :disabled="isFetchingRate">
                    <i class="ri-download-cloud-2-line text-lg group-hover:animate-bounce" :class="isFetchingRate ? 'animate-spin' : ''"></i>
                    <span x-text="isFetchingRate ? 'Fetching from NBC...' : 'Auto Fetch from NBC API'"></span>
                </button>

            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex gap-3">
                <button @click="isExchangeModalOpen = false" class="flex-1 py-3 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-100 dark:hover:bg-gray-800 transition text-sm">Cancel</button>
                <button @click="saveExchangeRate()" class="flex-1 py-3 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-500 shadow-lg shadow-emerald-500/30 active:scale-95 transition text-sm">Save Change</button>
            </div>
        </div>
    </div>

</div>

{{-- SCRIPT LOGIC --}}
<script>
    function headerController() {
        return {
            // Header States
            isSearchOpen: false,
            search: '',
            isPolling: false,
            activeCategory: 'all',

            // Exchange Rate States
            isExchangeModalOpen: false,
            exchangeRate: localStorage.getItem('pos_exchange_rate') || 4100, 
            tempExchangeRate: 4100,
            isFetchingRate: false,

            init() {
                // 1. Load from DB on init
                this.loadSystemRate();

                this.$watch('search', value => {
                    window.dispatchEvent(new CustomEvent('search-changed', { detail: value }));
                });
            },

            // --- Header Functions ---
            toggleSearch() {
                this.isSearchOpen = !this.isSearchOpen;
                if (!this.isSearchOpen) this.search = '';
            },

            setCategory(id) {
                this.activeCategory = id;
                window.dispatchEvent(new CustomEvent('category-changed', { detail: id }));
            },

            openQuickAddon() {
                alert('Addon Quick Open (Dispatch event here)');
            },

            // --- Exchange Rate Functions ---
            
            // 1. Get Rate from DB
            async loadSystemRate() {
                try {
                    const response = await fetch("{{ route('system.exchange-rate.get') }}"); 
                    const data = await response.json();
                    if(data.rate) {
                        this.exchangeRate = parseFloat(data.rate);
                        this.tempExchangeRate = this.exchangeRate;
                        localStorage.setItem('pos_exchange_rate', this.exchangeRate);
                    }
                } catch (e) {
                    console.error("Failed to load rate", e);
                }
            },

            openExchangeModal() {
                this.tempExchangeRate = this.exchangeRate;
                this.isExchangeModalOpen = true;
            },

            // 2. Save Rate to DB
            async saveExchangeRate() {
                if (this.tempExchangeRate > 0) {
                    try {
                        const response = await fetch("{{ route('system.exchange-rate.update') }}", {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}" 
                            },
                            body: JSON.stringify({ rate: this.tempExchangeRate })
                        });
                        
                        if(response.ok) {
                            this.exchangeRate = this.tempExchangeRate;
                            localStorage.setItem('pos_exchange_rate', this.exchangeRate);
                            this.isExchangeModalOpen = false;
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Exchange rate updated!' } }));
                        } else {
                            throw new Error('Update failed');
                        }
                    } catch (e) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Failed to save rate.' } }));
                    }
                }
            },

            // 3. Auto Fetch from NBC (Updated Logic)
            async fetchRateFromApi() {
                this.isFetchingRate = true;
                try {
                    const response = await fetch("{{ route('system.exchange-rate.fetch-nbc') }}");
                    const data = await response.json();

                    // ✅ FIX: Check structure according to your JSON image
                    // data.data is an OBJECT containing "average", "bid", "ask"
                    if (data && data.data) {
                        let khrRate = 0;

                        // Priority: average > ask > bid
                        if (data.data.average) {
                            khrRate = parseFloat(data.data.average);
                        } else if (data.data.ask) {
                            khrRate = parseFloat(data.data.ask);
                        } else if (data.data.bid) {
                            khrRate = parseFloat(data.data.bid);
                        }

                        if (khrRate > 0) {
                            this.tempExchangeRate = khrRate; 
                            window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Rate fetched: ' + khrRate } }));
                        } else {
                            throw new Error("Rate not found in API response");
                        }
                    } else {
                        throw new Error("Invalid Data Structure");
                    }
                } catch (error) {
                    console.error("Error fetching rate:", error);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', message: 'Failed to fetch rate from NBC.' } }));
                } finally {
                    this.isFetchingRate = false;
                }
            },

            formatNumber(num) {
                return new Intl.NumberFormat('en-US').format(num);
            }
        }
    }
</script>