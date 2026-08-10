@forelse($topProducts as $index => $item)
    <div class="flex items-center gap-3 animate-fade-in group">
        {{-- Image Container: ប្រើ bg-input-bg និង border-bor-color --}}
        <div class="w-10 h-10 rounded-lg bg-input-bg border border-bor-color flex items-center justify-center flex-shrink-0 overflow-hidden">
            @if($item->product->image ?? false)
                <img src="{{ asset('storage/'.$item->product->image) }}" class="w-full h-full object-cover">
            @else
                <i class="ri-image-line text-gray-400"></i>
            @endif
        </div>
        
        <div class="flex-1 min-w-0">
            <div class="flex justify-between mb-1">
                {{-- Product Name: ប្រើ text-sidebar-text --}}
                <h4 class="text-sm font-bold text-sidebar-text truncate w-32 group-hover:text-primary transition-colors">
                    {{ $item->product->name ?? 'Unknown' }}
                </h4>
                {{-- Price: ប្រើ text-sidebar-text --}}
                <span class="text-sm font-bold text-sidebar-text">
                    ${{ number_format($item->total_revenue, 2) }}
                </span>
            </div>
            
            {{-- Progress Bar BG: ប្រើ bg-input-bg --}}
            <div class="w-full bg-input-bg rounded-full h-1.5 relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 rounded-full 
                    {{ $index == 0 ? 'bg-amber-400' : ($index == 1 ? 'bg-gray-400' : ($index == 2 ? 'bg-orange-400' : 'bg-blue-400')) }}" 
                    style="width: {{ ($item->total_revenue / $maxProductSales) * 100 }}%"></div>
            </div>
            <div class="text-[10px] text-gray-400 mt-1">{{ number_format($item->total_qty) }} units sold</div>
        </div>
    </div>
@empty
    <div class="text-center py-10 text-gray-400">
        <i class="ri-inbox-line text-4xl opacity-30 mb-2 block"></i>
        No data available
    </div>
@endforelse