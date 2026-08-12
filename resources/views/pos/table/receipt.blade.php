{{-- 1. Load Font ពី Google --}}
<link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">

<style>
    /* CSS សម្រាប់ Print */
    @media print {
        @page { 
            margin: 0; 
            size: 80mm auto; /* បង្ខំឱ្យ Browser ដឹងថានេះជាក្រដាស 80mm */
        }
        body { 
            margin: 0; 
            padding: 0; 
            -webkit-print-color-adjust: exact; 
            width: 100%;
        }
    }
    
    /* Font Khmer */
    .font-khmer, .font-khmer * {
        font-family: 'Battambang', cursive !important;
    }
</style>

<div x-data="receiptPrinter()" 
     @print-receipt.window="prepareAndPrint($event.detail)" 
     id="receipt-print-area"
     class="hidden print:block font-khmer w-full">

    {{-- 🔥 កែទំហំមកត្រឹម 68mm និងបន្ថយអក្សរមក 11px ដើម្បីធានាមិនដាច់សងខាង --}}
    <div class="w-[68mm] print:w-[68mm] mx-auto print:ml-1 text-black text-[11px] leading-tight bg-white font-khmer">

        {{-- HEADER --}}
        <div class="text-center mb-2">
            <h1 class="text-base font-bold mb-0.5" x-text="orderDetails?.shop?.shop_en || 'POS SYSTEM'"></h1>
            <p class="text-[10px]" x-text="orderDetails?.shop?.address_en || ''"></p>
            <p class="text-[10px]" x-text="orderDetails?.shop?.phone_number ? ('Tel: ' + orderDetails.shop.phone_number) : ''"></p>
        </div>

        {{-- INFO --}}
        <div class="border-b border-dashed border-black pb-1 mb-1 text-[11px]">
            <div class="flex justify-between">
                <span>វិក្កយបត្រ:</span> 
                <span class="font-bold" x-text="orderDetails?.invoice_number || '---'"></span>
            </div>
            <div class="flex justify-between">
                <span>កាលបរិច្ឆេទ:</span> 
                <span x-text="orderDetails?.formatted_date || formatDate(orderDetails?.created_at)"></span>
            </div>
            <div class="flex justify-between">
                <span>ម៉ោងចូល (In):</span> 
                <span x-text="orderDetails?.formatted_check_in || formatTimeOnly(orderDetails?.check_in_time)"></span>
            </div>
            <div class="flex justify-between">
                <span>ម៉ោងចេញ (Out):</span> 
                <span x-text="orderDetails?.formatted_check_out || formatTimeOnly(orderDetails?.check_out_time || new Date())"></span>
            </div>
            <div class="flex justify-between">
                <span>តុ:</span> 
                <span x-text="selectedTable?.name || 'ទូទៅ'"></span>
            </div>
            <div class="flex justify-between">
                <span>អ្នកគិតលុយ:</span> 
                <span>{{ auth()->user()->name }}</span>
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        <table class="w-full mb-1 border-collapse text-[11px]">
            <thead>
                <tr class="border-b border-black">
                    {{-- 🔥 រៀបចំទំហំ Column ថ្មីឱ្យត្រូវគ្នាបេះបិទ --}}
                    <th class="text-left py-1 w-[40%]">មុខម្ហូប</th>
                    <th class="text-center py-1 w-[10%]">ចំនួន</th>
                    <th class="text-right py-1 w-[22%]">តម្លៃ(៛)</th>
                    <th class="text-right py-1 w-[28%] pr-0.5">សរុប(៛)</th>
                </tr>
            </thead>

            <template x-for="item in groupedItems" :key="item.uniqueKey">
                <tbody class="align-top border-none">
                    
                    <tr>
                        <td class="py-0.5 pr-1 text-left font-bold leading-tight">
                            <span x-text="item.product?.name || 'Unknown'"></span>
                        </td>
                        <td class="text-center py-0.5">
                            <span x-text="item.quantity"></span>
                        </td>
                        <td class="text-right py-0.5 tracking-tighter">
                            <span x-text="formatRiel(item.price)"></span>
                        </td>
                        <td class="text-right py-0.5 font-bold pr-0.5 tracking-tighter">
                            <span x-text="formatRiel(item.price * item.quantity)"></span>រៀល
                        </td>
                    </tr>

                    <template x-if="item.addons && item.addons.length > 0">
                        <template x-for="ad in item.addons">
                            {{-- អក្សរ Addon តូចជាងមុខម្ហូបបន្តិច (10px) --}}
                            <tr class="text-gray-600 text-[10px]">
                                <td class="py-0.5 pl-2 text-left italic relative">
                                    <span class="absolute left-0 top-0.5">+</span>
                                    <span x-text="ad.addon?.name || 'Addon'"></span>
                                </td>
                                <td class="text-center py-0.5">
                                    <span x-text="ad.quantity"></span>
                                </td>
                                <td class="text-right py-0.5 tracking-tighter">
                                    <span x-text="formatRiel(ad.price)"></span>
                                </td>
                                <td class="text-right py-0.5 pr-0.5 tracking-tighter">
                                    <span x-text="formatRiel(ad.price * ad.quantity)"></span>
                                </td>
                            </tr>
                        </template>
                    </template>

                </tbody>
            </template>
        </table>

        {{-- TOTALS --}}
        <div class="border-t border-dashed border-black pt-1">
            <div class="flex justify-between text-[14px] font-bold mb-1 border-b pb-1 pr-0.5">
                <span>សរុប (៛):</span> 
                <span class="tracking-tighter" x-text="formatRiel(orderDetails?.total_amount || 0)"></span>
            </div>
        </div>

        <div class="text-center mt-3 border-t border-black pt-1 mb-2">
            <p class="font-bold text-[11px]">*** សូមអរគុណ ***</p>
        </div>
    </div>
</div>