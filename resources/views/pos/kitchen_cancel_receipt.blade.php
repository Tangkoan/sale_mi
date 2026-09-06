<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Cancel Receipt</title>
    
    {{-- Load Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @page { margin: 0; }
        
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
            padding: 20px 15px;
            box-sizing: border-box; 
            overflow: hidden;
        }
        
        /* បន្ទាត់រាងដិតបន្តិចសម្រាប់សន្លឹក Cancel */
        .solid-line { 
            border-top: 3px dashed #000; 
            margin: 12px 0; 
        }

        /* បន្ទាត់កាត់ចោល (Strikethrough) សម្រាប់ម្ហូបដែលលុប */
        .strike-through {
            text-decoration: line-through;
            text-decoration-thickness: 3px;
        }

        @media print {
            html, body { height: max-content; }
            .receipt-paper { padding: 20px 15px; }
        }
    </style>
</head>
<body>

    <div id="receipt-print-area" class="receipt-paper">
        
        {{-- HEADER: បង្ហាញសញ្ញាលុបចោលយ៉ាងច្បាស់ --}}
        <div class="text-center mb-4">
            <h1 class="text-[34px] font-black uppercase leading-tight tracking-wide border-[3px] border-black inline-block px-4 py-1 rounded-lg">
                ❌ លុបចោល (VOID)
            </h1>
        </div>

        {{-- ព័ត៌មានតុ និងម៉ោង --}}
        <div class="text-[22px] mb-3 space-y-2">
            <div class="flex justify-between items-end">
                <span class="font-bold text-[24px]">តុ (Table) ៖</span> 
                <span class="font-black text-[32px]">{{ $cancelData['table_name'] }}</span>
            </div>
            <div class="flex justify-between items-end text-[20px]">
                <span>ម៉ោងលុប (Time) ៖</span> 
                <span class="font-bold">{{ $cancelData['time'] }}</span>
            </div>
        </div>

        <div class="solid-line"></div>

        {{-- បញ្ជីមុខម្ហូបដែលត្រូវលុប --}}
        <table class="w-full text-[24px] leading-tight mt-2">
            <tbody class="align-top">
                <tr class="border-b-[2px] border-dotted border-gray-500 last:border-b-0">
                    <td class="py-3 w-full">
                        {{-- ឈ្មោះមុខម្ហូប មានគូសបន្ទាត់កាត់ចំកណ្ដាល --}}
                        <div class="font-bold text-[26px] text-gray-900 leading-tight strike-through">
                            <span>{{ $cancelData['product_name'] }}</span>
                            <span class="font-black text-[28px] ml-[10px]">(-{{ $cancelData['quantity'] }})</span>
                        </div>
                        
                        {{-- ផ្នែកចំណាំ (Note) --}}
                        @if(!empty($cancelData['note']))
                            <div class="text-[20px] italic text-gray-800 mt-2 border-l-[4px] border-black pl-3 py-1 bg-gray-50 strike-through">
                                📝 ចំណាំ: {{ $cancelData['note'] }}
                            </div>
                        @endif

                        {{-- ផ្នែកជម្រើសបន្ថែម (Addons) --}}
                        @if(!empty($cancelData['addons']))
                            <div class="mt-2 space-y-1">
                                @foreach($cancelData['addons'] as $addon)
                                    <div class="text-[20px] text-gray-800 pl-2 strike-through">
                                        <span>➖ {{ $addon }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="solid-line mt-2 mb-2"></div>
        
        {{-- ផ្នែកខាងក្រោមបង្អស់ (Footer) --}}
        <div class="text-center mt-2">
            <p class="font-black text-[22px]">*** សូមកុំធ្វើមុខម្ហូបនេះ ***</p>
        </div>

    </div>

</body>
</html>