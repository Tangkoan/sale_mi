{{-- 1. Load Font ពី Google --}}
<link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">

<style>
    /* CSS សម្រាប់ Print */
    @media print {
        @page { margin: 0; size: auto; }
        body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; }
    }
    
    /* Font Khmer */
    .font-khmer, .font-khmer * {
        font-family: 'Battambang', cursive !important;
    }
</style>

<div x-data="receiptPrinter()" 
     @print-receipt.window="prepareAndPrint($event.detail)" 
     id="receipt-print-area"
     class="hidden print:block font-khmer">

    <div class="w-[78mm] mx-auto p-2 text-black text-[13px] leading-snug bg-white font-khmer">

        {{-- HEADER: បង្ហាញព័ត៌មានហាងដែលទាញពី Database --}}
        <div class="text-center mb-4">
            <h1 class="text-lg font-bold mb-1" x-text="orderDetails?.shop?.shop_en || 'POS SYSTEM'"></h1>
            <p x-text="orderDetails?.shop?.address_en || ''"></p>
            <p x-text="orderDetails?.shop?.phone_number ? ('Tel: ' + orderDetails.shop.phone_number) : ''"></p>
        </div>

        {{-- INFO --}}
        <div class="border-b border-dashed border-black pb-2 mb-2">
            <div class="flex justify-between">
                <span>លេខវិក្កយបត្រ:</span> 
                <span class="font-bold" x-text="orderDetails?.invoice_number || '---'"></span>
            </div>
            
            {{-- 🔥 ប្រើ formatted_date ពី Server ដើម្បីអោយម៉ោងត្រូវ --}}
            <div class="flex justify-between">
                <span>កាលបរិច្ឆេទ:</span> 
                <span x-text="orderDetails?.formatted_date || formatDate(orderDetails?.created_at)"></span>
            </div>

            {{-- 🔥 ប្រើ formatted_check_in ពី Server --}}
            <div class="flex justify-between">
                <span>ម៉ោងចូល (In):</span> 
                <span x-text="orderDetails?.formatted_check_in || formatTimeOnly(orderDetails?.check_in_time)"></span>
            </div>

            {{-- 🔥 ប្រើ formatted_check_out ពី Server --}}
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
            <div class="flex justify-between font-bold">
                <span>អត្រាប្រាក់:</span> 
                <span x-text="'1$ = ' + formatNumber(exchangeRate) + ' ៛'"></span>
            </div>
        </div>

        {{-- ITEMS TABLE --}}
    <table class="w-full mb-2 border-collapse">
            <thead>
                <tr class="border-b border-black text-sm">
                    <th class="text-left py-1 w-[45%]">មុខម្លូប</th>
                    <th class="text-center py-1 w-[15%]">ចំនួន</th>
                    <th class="text-right py-1 w-[20%]">តម្លៃ</th>
                    <th class="text-right py-1 w-[20%]">សរុប</th>
                </tr>
            </thead>

            <template x-for="item in groupedItems" :key="item.uniqueKey">
                <tbody class="align-top text-sm border-none">
                    
                    <tr>
                        <td class="py-1 pr-1 text-left font-bold">
                            <span x-text="item.product?.name || 'Unknown'"></span>
                        </td>
                        <td class="text-center py-1">
                            <span x-text="item.quantity"></span>
                        </td>
                        <td class="text-right py-1">
                            <span x-text="formatPrice(item.price)"></span>
                        </td>
                        <td class="text-right py-1 font-bold">
                            <span x-text="formatPrice(item.price * item.quantity)"></span>
                        </td>
                    </tr>

                    <template x-if="item.addons && item.addons.length > 0">
                        <template x-for="ad in item.addons">
                            <tr class="text-gray-600 text-[12px]">
                                <td class="py-0.5 pl-4 text-left italic relative">
                                    <span class="absolute left-1 top-0.5">+</span>
                                    <span x-text="ad.addon?.name || 'Addon'"></span>
                                </td>
                                
                                <td class="text-center py-0.5">
                                    <span x-text="ad.quantity"></span>
                                </td>
                                
                                <td class="text-right py-0.5">
                                    <span x-text="formatPrice(ad.price)"></span>
                                </td>
                                
                                <td class="text-right py-0.5">
                                    <span x-text="formatPrice(ad.price * ad.quantity)"></span>
                                </td>
                            </tr>
                        </template>
                    </template>

                </tbody>
            </template>
        </table>

        {{-- TOTALS --}}
        <div class="border-t border-dashed border-black pt-2">
            <div class="flex justify-between text-base font-bold">
                <span>សរុប ($):</span> <span x-text="formatPrice(orderDetails?.total_amount || 0)"></span>
            </div>
            <div class="flex justify-between text-base font-bold mb-1 border-b pb-1">
                <span>សរុប (៛):</span> <span x-text="formatRiel(orderDetails?.total_amount || 0)"></span>
            </div>
            
            
        </div>

        <div class="text-center mt-6 border-t border-black pt-2">
            <p class="font-bold">*** សូមអរគុណ ***</p>
        </div>
    </div>
</div>