<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>វិក្កយបត្រទូទាត់ប្រាក់</title>
    
    {{-- Load Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* កំណត់ទំហំក្រដាស Print ឱ្យពេញ (Full Label Margin = 0) */
        @page { 
            margin: 0; 
        }
        
        body { 
            margin: 0; 
            padding: 0; 
            width: 100%;
            background: white; 
            font-family: 'Battambang', cursive !important;
        }
        
        /* កំណត់ទទឹង ១០០% និង Padding ឆ្វេង-ស្តាំ 20px */
        .receipt-paper { 
            width: 100%; 
            margin: 0;
            padding: 5px 10px; /* លើ-ក្រោម 15px, ឆ្វេង-ស្តាំ 20px */
            box-sizing: border-box; 
            color: #000;
        }
        
        /* បន្ទាត់ */
        .dashed-line { 
            border-top: 2px dashed #000; 
            margin: 10px 0; 
        }
        .solid-line { 
            border-top: 2px solid #000; 
            margin: 10px 0; 
        }

        /* តម្រូវសម្រាប់ពេល Print */
        @media print {
            html, body {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            .receipt-paper { 
                width: 100%; 
                padding: 0 20px; /* លើ-ក្រោម 0, ឆ្វេង-ស្តាំ 20px ពេលព្រីន */
                box-shadow: none; 
            }
        }
    </style>
</head>
<body>

    <div id="receipt-print-area" class="receipt-paper">
        
        {{-- HEADER ហាង --}}
        <div class="text-center mb-5">
            <h1 class="text-[34px] font-bold leading-tight mb-2">
                {{ $shop->shop_en ?? ($shop->name ?? 'POS SYSTEM') }}
            </h1>
            <p class="text-[20px] leading-tight">{{ $shop->address_en ?? '' }}</p>
            @if(!empty($shop->phone_number))
                <p class="text-[20px] leading-tight mt-1 font-bold">Tel: {{ $shop->phone_number }}</p>
            @endif
        </div>

        {{-- ចំណងជើងវិក្កយបត្រ --}}
        <div class="text-center mb-5">
            <div class="text-[30px] font-bold">វិក្កយបត្រទូទាត់ប្រាក់</div>
        </div>

        {{-- ព័ត៌មានទូទៅ --}}
        <div class="text-[22px] mb-4 space-y-2">
            <div class="flex justify-between items-end">
                <span>លេខវិក្កយបត្រ៖</span> 
                <span class="font-bold text-[24px]">{{ $order->invoice_number ?? '---' }}</span>
            </div>
            <div class="flex justify-between items-end">
                <span>កាលបរិច្ឆេទ៖</span> 
                <span class="font-bold">{{ $order->created_at ? $order->created_at->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="flex justify-between items-end">
                <span>ម៉ោងចូល៖</span> 
                <span class="font-bold">{{ $order->check_in_time ? \Carbon\Carbon::parse($order->check_in_time)->format('h:i A') : '---' }}</span>
            </div>
            <div class="flex justify-between items-end">
                <span>ម៉ោងចេញ៖</span> 
                <span class="font-bold">{{ \Carbon\Carbon::parse($order->check_out_time ?? now())->format('h:i A') }}</span>
            </div>
            <div class="flex justify-between items-end">
                <span>លេខតុ៖</span> 
                <span class="font-bold text-[26px]">{{ $order->table->name ?? 'ទូទៅ' }}</span>
            </div>
            <!-- <div class="flex justify-between items-end">
                <span>អ្នកគិតលុយ៖</span> 
                <span class="font-bold">{{ $order->user->name ?? (auth()->user()->name ?? 'បុគ្គលិក') }}</span>
            </div> -->
        </div>

        <div class="solid-line"></div>

        {{-- តារាងមុខម្ហូប --}}
        <table class="w-full text-[20px] leading-tight">
            <thead>
                <tr class="text-left">
                    <th class="pb-3 font-bold w-[40%]">ឈ្មោះទំនិញ</th>
                    <th class="pb-3 font-bold text-right w-[20%]">តម្លៃ</th>
                    <th class="pb-3 font-bold text-center w-[15%]">ចំនួន</th>
                    <th class="pb-3 font-bold text-right w-[25%]">សរុប(៛)</th>
                </tr>
            </thead>
            <tbody class="align-top">
                {{-- ប្តូរ colspan ទៅ 4 ដោយសារយើងមាន 4 columns --}}
                <tr>
                    <td colspan="4"><div class="dashed-line mt-0 mb-3"></div></td>
                </tr>
                
                @foreach($order->items as $item)
                    @php
                        $qty = $item->quantity;
                        $price = $item->price;
                        $lineTotal = $price * $qty;
                    @endphp
                    
                    <tr class="mt-2">
                        {{-- ឈ្មោះមុខម្ហូប --}}
                        <td class="pt-2 pr-1 font-bold text-gray-900 text-[22px]">
                            {{ $item->product->name ?? 'មិនស្គាល់' }}
                        </td>
                        {{-- តម្លៃរាយ --}}
                        <td class="pt-2 text-right font-normal text-[20px] text-gray-800">{{ number_format($price) }}</td>
                        {{-- ចំនួន --}}
                        <td class="pt-2 text-center font-bold text-[22px]">{{ $qty }}</td>
                        
                        {{-- សរុប --}}
                        <td class="pt-2 text-right font-bold text-[22px] tracking-tight">{{ number_format($lineTotal) }}</td>
                    </tr>

                    {{-- ជម្រើសបន្ថែម (Addons) --}}
                    @if($item->addons && count($item->addons) > 0)
                        @foreach($item->addons as $ad)
                            <tr class="text-[18px]">
                                {{-- ឈ្មោះ Addon --}}
                                <td class="pl-4 pt-1 text-gray-800">
                                    + {{ $ad->addon->name ?? ($ad->name ?? 'បន្ថែម') }} 
                                </td>
                                
                                {{-- តម្លៃរាយ Addon (ផ្លាស់ប្តូរមកដាក់ទីនេះវិញ) --}}
                                <td class="text-right pt-1 text-gray-700">
                                    {{ number_format($ad->price) }}
                                </td>
                                
                                {{-- ចំនួន Addon (ផ្លាស់ប្តូរមកដាក់ទីនេះវិញ) --}}
                                <td class="text-center pt-1 text-gray-800 font-bold">
                                    {{ $ad->quantity ?? 1 }}
                                </td>
                                
                                {{-- សរុប Addon --}}
                                <td class="text-right pt-1 tracking-tight text-gray-800 font-bold">
                                    {{ number_format($ad->price * ($ad->quantity ?? 1)) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="solid-line mt-4 mb-2"></div>

        {{-- ផ្នែកសរុបទឹកប្រាក់ --}}
        <div class="text-[24px] mt-2">
            <div class="flex justify-between items-center font-bold py-2">
                <span class="text-[28px]">សរុបទឹកប្រាក់៖</span> 
                <span class="text-[34px] tracking-tight">{{ number_format($order->total_amount ?? 0) }} ៛</span>
            </div>
        </div>

        <div class="solid-line mt-3 mb-4"></div>

        {{-- បាតវិក្កយបត្រ --}}
        <div class="text-center mt-5 pb-5">
            <p class="font-bold text-[26px] mb-2">*** សូមអរគុណ ***</p>
            <p class="text-[20px]">សូមអញ្ជើញមកម្ដងទៀត</p>
        </div>
        
    </div>

</body>
</html>