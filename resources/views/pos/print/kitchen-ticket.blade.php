<div style="font-family: sans-serif; width: 70mm; font-size: 14px;">
    
    {{-- Header --}}
    <div style="text-align: center; border-bottom: 2px solid black; padding-bottom: 5px; margin-bottom: 10px;">
        <h1 style="margin: 0; font-size: 24px; font-weight: bold;">{{ $locationName ?? 'KITCHEN' }}</h1>
        <div style="font-size: 18px; margin-top: 5px;">Table: <strong>{{ $tableName ?? 'N/A' }}</strong></div>
        <div style="font-size: 12px; color: #555;">{{ $time ?? '' }}</div>
    </div>

    {{-- Items List --}}
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left; width: 15%;">Qty</th>
                <th style="text-align: left;">Item</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($items))
                @foreach($items as $item)
                    <tr>
                        <td style="font-size: 20px; font-weight: bold; vertical-align: top; padding-top: 5px;">
                            {{ $item->quantity }}
                        </td>
                        <td style="vertical-align: top; padding-top: 5px;">
                            <span style="font-size: 18px; font-weight: bold;">
                                {{ $item->product->name ?? 'Unknown Item' }}
                            </span>
                            
                            @if($item->addons && $item->addons->count() > 0)
                                <div style="font-size: 14px; font-style: italic; margin-top: 2px;">
                                    @foreach($item->addons as $addon)
                                        + {{ $addon->addon->name ?? 'Addon' }} <br>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($item->note))
                                <div style="font-weight: bold; background: #eee; padding: 2px;">
                                    * Note: {{ $item->note }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    
    <div style="text-align: center; margin-top: 10px; font-size: 10px;">End Ticket</div>
</div>