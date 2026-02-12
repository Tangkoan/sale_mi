<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 md:mb-6 gap-4">
    {{-- Title & Mobile Header --}}
    <div class="flex justify-between items-center w-full md:w-auto">
        <h1 class="text-xl md:text-2xl font-black text-sidebar-text tracking-tight">
            {{ __('messages.sale_report') }}
        </h1>
        <button onclick="triggerReset()" class="md:hidden p-2 rounded-full bg-input-bg text-sidebar-text active:bg-bor-color border border-bor-color">
            <i class="ri-refresh-line text-lg"></i>
        </button>
    </div>
    
    {{-- Filter Toolbar --}}
    <div class="w-full md:w-auto bg-card-bg p-2 rounded-xl shadow-custom border border-bor-color flex flex-col md:flex-row gap-3 md:gap-2">
        
        {{-- SECTION 1: Buttons (Day/Month/Year) --}}
        <div class="flex bg-input-bg p-1 rounded-lg w-full md:w-auto relative border border-input-border">
            <button onclick="setFilterType('day')" id="btn-day" 
                class="filter-tab flex-1 md:flex-none w-full md:w-20 py-2 md:py-1.5 text-xs font-bold rounded-md transition-all duration-200 shadow-custom bg-card-bg text-primary uppercase tracking-wider">
                {{ __('messages.day') }}
            </button>
            <button onclick="setFilterType('month')" id="btn-month" 
                class="filter-tab flex-1 md:flex-none w-full md:w-20 py-2 md:py-1.5 text-xs font-bold rounded-md transition-all duration-200 text-sidebar-text hover:text-primary hover:bg-card-bg uppercase tracking-wider">
                {{ __('messages.month') }}
            </button>
            <button onclick="setFilterType('year')" id="btn-year" 
                class="filter-tab flex-1 md:flex-none w-full md:w-20 py-2 md:py-1.5 text-xs font-bold rounded-md transition-all duration-200 text-sidebar-text hover:text-primary hover:bg-card-bg uppercase tracking-wider">
                {{ __('messages.year') }}
            </button>
        </div>

        {{-- Separator (Desktop Only) --}}
        <div class="hidden md:block w-px my-1 bg-bor-color"></div>

        {{-- SECTION 2: Date Inputs --}}
        <div id="filter-inputs" class="flex-1 flex flex-col md:flex-row items-stretch md:items-center justify-between md:justify-start gap-2">
            
            {{-- 1. DAY RANGE --}}
            <div id="group-day" class="filter-group flex flex-row items-center w-full md:w-auto">
                <div class="flex items-center bg-input-bg px-2 py-2 md:py-1.5 rounded-lg border border-input-border w-full md:w-auto justify-between">
                    {{-- Start Date --}}
                    <input type="date" id="day-start" 
                           class="bg-transparent border-none text-[11px] md:text-sm font-bold text-sidebar-text focus:ring-0 p-0 cursor-pointer flex-1 w-0 md:w-auto min-w-0 text-center appearance-none placeholder-gray-400" 
                           value="{{ date('Y-m-d') }}" onchange="fetchReport()">
                    
                    <span class="text-gray-400 text-[10px] px-1 shrink-0"><i class="ri-arrow-right-line"></i></span>
                    
                    {{-- End Date --}}
                    <input type="date" id="day-end" 
                           class="bg-transparent border-none text-[11px] md:text-sm font-bold text-sidebar-text focus:ring-0 p-0 cursor-pointer flex-1 w-0 md:w-auto min-w-0 text-center appearance-none placeholder-gray-400" 
                           value="{{ date('Y-m-d') }}" onchange="fetchReport()">
                </div>
            </div>

            {{-- 2. MONTH RANGE --}}
            <div id="group-month" class="filter-group hidden flex-row items-center w-full md:w-auto">
                <div class="flex items-center bg-input-bg px-2 py-2 md:py-1.5 rounded-lg border border-input-border w-full md:w-auto justify-between">
                    {{-- Start Month --}}
                    <input type="month" id="month-start" 
                           class="bg-transparent border-none text-[11px] md:text-sm font-bold text-sidebar-text focus:ring-0 p-0 cursor-pointer flex-1 w-0 md:w-auto min-w-0 text-center appearance-none placeholder-gray-400" 
                           value="{{ date('Y-m') }}" onchange="fetchReport()">
                    
                    <span class="text-gray-400 text-[10px] px-1 shrink-0"><i class="ri-arrow-right-line"></i></span>
                    
                    {{-- End Month --}}
                    <input type="month" id="month-end" 
                           class="bg-transparent border-none text-[11px] md:text-sm font-bold text-sidebar-text focus:ring-0 p-0 cursor-pointer flex-1 w-0 md:w-auto min-w-0 text-center appearance-none placeholder-gray-400" 
                           value="{{ date('Y-m') }}" onchange="fetchReport()">
                </div>
            </div>

            {{-- 3. YEAR RANGE --}}
            <div id="group-year" class="filter-group hidden flex-row items-center w-full md:w-auto">
                <div class="flex items-center bg-input-bg px-3 py-2 md:py-1.5 rounded-lg border border-input-border w-full md:w-auto justify-between">
                    <select id="year-start" class="bg-transparent border-none text-xs md:text-sm font-bold text-sidebar-text focus:ring-0 p-0 cursor-pointer w-full md:w-24 text-center" onchange="fetchReport()">
                        @for($i = date('Y'); $i >= date('Y') - 5; $i--) <option value="{{ $i }}">{{ $i }}</option> @endfor
                    </select>
                    <span class="text-gray-400 text-xs px-2 shrink-0"><i class="ri-arrow-right-line"></i></span>
                    <select id="year-end" class="bg-transparent border-none text-xs md:text-sm font-bold text-sidebar-text focus:ring-0 p-0 cursor-pointer w-full md:w-24 text-center" onchange="fetchReport()">
                        @for($i = date('Y'); $i >= date('Y') - 5; $i--) <option value="{{ $i }}">{{ $i }}</option> @endfor
                    </select>
                </div>
            </div>

            {{-- Refresh Button --}}
            <button onclick="triggerReset()" class="hidden md:block ml-2 p-2 rounded-full text-sidebar-text hover:text-primary hover:bg-input-bg transition-all shadow-custom border border-transparent hover:border-bor-color">
                <i class="ri-refresh-line text-lg"></i>
            </button>
        </div>
    </div>
</div>