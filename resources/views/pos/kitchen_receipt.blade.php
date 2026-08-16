<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Receipt</title>
    
    {{-- Load Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* កំណត់ទំហំក្រដាស Print ឱ្យពេញ */
        @page { 
            margin: 0; 
        }
        
        /* ប្រើ fit-content ដើម្បីកាត់ផ្ដាច់ត្រឹមចុងអក្សរ កុំឲ្យសល់ក្រដាស */
        html, body { 
            margin: 0; 
            padding: 0; 
            width: 100%;
            height: fit-content; 
            background: white; 
            font-family: 'Battambang', cursive !important; 
            color: #000;
        }
        
        .receipt-paper { 
            width: 100%; 
            margin: 0;
            padding: 20px 15px; /* គម្លាត 20px លើ-ក្រោម និង 15px ឆ្វេង-ស្តាំ */
            box-sizing: border-box; 
            overflow: hidden;
        }
        
        /* បន្ទាត់ */
        .solid-line { 
            border-top: 2px solid #000; 
            margin: 12px 0; 
        }

        /* តម្រូវសម្រាប់ពេល Print */
        @media print {
            html, body {
                height: max-content;
            }
            .receipt-paper { 
                padding: 20px 15px; /* រក្សាគម្លាត 20px ដដែលពេលព្រីន */
            }
        }
    </style>
</head>
<body>

    <div id="receipt-print-area" class="receipt-paper">
        
        {{-- HEADER: ឈ្មោះព្រីនធឺ ឬ ចំណងជើង --}}
        <div class="text-center mb-4">
            <h1 class="text-[34px] font-black uppercase leading-tight tracking-wide border-2 border-black inline-block px-4 py-1 rounded-lg">
                {{ $printerInfo->name ?? 'ORDER' }}
            </h1>
        </div>

        {{-- ព័ត៌មានតុ និងម៉ោង --}}
        <div class="text-[22px] mb-3 space-y-2">
            <div class="flex justify-between items-end">
                <span class="font-bold text-[24px]">តុ (Table) ៖</span> 
                <span class="font-black text-[32px]">{{ $tableName }}</span>
            </div>
            <div class="flex justify-between items-end text-[20px]">
                <span>ម៉ោង (Time) ៖</span> 
                <span class="font-bold">{{ now()->format('d/m/Y h:i A') }}</span>
            </div>
        </div>

        <div class="solid-line"></div>

        {{-- បញ្ជីមុខម្ហូប --}}
        <table class="w-full text-[24px] leading-tight mt-2">
            <tbody class="align-top">
                @foreach($items as $item)
                    <tr class="border-b-[2px] border-dotted border-gray-500 last:border-b-0">
                        
                        <td class="py-3 w-full">
                            {{-- ឈ្មោះមុខម្ហូប និង ចំនួននៅជាប់គ្នា (គម្លាត 10px) --}}
                            <div class="font-bold text-[26px] text-gray-900 leading-tight">
                                <span>{{ $item->product->name ?? 'Unknown' }}</span>
                                <span class="font-black text-[28px] ml-[10px]">(*{{ $item->quantity }})</span>
                            </div>
                            
                            {{-- ផ្នែកចំណាំ (Note) --}}
                            @if($item->note)
                                <div class="text-[20px] italic text-gray-800 mt-2 border-l-[4px] border-black pl-3 py-1 bg-gray-50">
                                    📝 ចំណាំ: {{ $item->note }}
                                </div>
                            @endif

                            {{-- ផ្នែកជម្រើសបន្ថែម (Addons) --}}
                            @if($item->addons && $item->addons->count() > 0)
                                <div class="mt-2 space-y-1">
                                    @foreach($item->addons as $addonRow)
                                        <div class="text-[20px] text-gray-800 pl-2">
                                            <span>➕ {{ $addonRow->addon->name ?? 'Extra' }}</span>
                                            <span class="font-bold text-[22px] ml-[10px]">(*{{ $addonRow->quantity }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="solid-line mt-2 mb-2"></div>
        
        {{-- ផ្នែកខាងក្រោមបង្អស់ (Footer) --}}
        {{-- បានដកចេញនូវ pb-4 និង mt-4 ដើម្បីកុំឲ្យសល់ចន្លោះធំពេកនៅខាងក្រោម --}}
        <div class="text-center mt-2">
            <p class="font-black text-[22px]">*** សូមរៀបចំមុខម្ហូបខាងលើ ***</p>
        </div>

    </div>

</body>
</html>