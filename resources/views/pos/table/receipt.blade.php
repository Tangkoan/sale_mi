{{-- 1. Load Font ពី Google (ដាក់នៅខាងលើគេ) --}}
<link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">

<style>
    /* បង្ខំអោយប្រើ Font Battambang គ្រប់កាលៈទេសៈ */
    .font-khmer, .font-khmer * {
        font-family: 'Battambang', cursive !important;
    }
    
    /* CSS សម្រាប់ Print */
    @media print {
        @page {
            margin: 0;
            size: auto;
        }
        body {
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }
        /* បង្ខំ Font ម្តងទៀតពេល Print */
        .font-khmer, .font-khmer * {
            font-family: 'Battambang', cursive !important;
        }
    }
</style>

<div x-data="receiptPrinter()" 
     @print-receipt.window="prepareAndPrint($event.detail)" 
     id="receipt-print-area" 
     class="hidden print:block font-khmer"> {{-- ប្រើ class font-khmer នៅទីនេះ --}}
     
     <div class="w-[78mm] mx-auto p-2 text-black text-[13px] leading-snug bg-white font-khmer">
        
        {{-- HEADER --}}
        <div class="text-center mb-4">
            <h1 class="text-lg font-bold mb-1" x-text="orderDetails?.shop?.shop_en || 'POS SYSTEM'"></h1>
            <p x-text="orderDetails?.shop?.address_en || ''"></p>
            <p x-text="orderDetails?.shop?.phone_number ? ('Tel: ' + orderDetails.shop.phone_number) : ''"></p>
        </div>

        {{-- INFO --}}
        <div class="border-b border-dashed border-black pb-2 mb-2">
            <div class="flex justify-between"><span>លេខវិក្កយបត្រ:</span> <span class="font-bold" x-text="orderDetails?.invoice_number || '---'"></span></div>
            <div class="flex justify-between"><span>កាលបរិច្ឆេទ:</span> <span x-text="formatDate(orderDetails?.created_at)"></span></div>
            <div class="flex justify-between"><span>តុ:</span> <span x-text="selectedTable?.name || 'ទូទៅ'"></span></div>
            <div class="flex justify-between"><span>អ្នកគិតលុយ:</span> <span>{{ auth()->user()->name }}</span></div>
        </div>

        {{-- ITEMS TABLE --}}
        <table class="w-full mb-2">
            <thead>
                <tr class="border-b border-black">
                    <th class="text-left py-1 w-[45%]">បរិយាយ</th>
                    <th class="text-center py-1 w-[15%]">ចំនួន</th>
                    <th class="text-right py-1 w-[20%]">តម្លៃ</th>
                    <th class="text-right py-1 w-[20%]">សរុប</th>
                </tr>
            </thead>
            <tbody class="align-top">
                <template x-if="orderDetails?.items && orderDetails.items.length > 0">
                    <template x-for="item in orderDetails.items" :key="item.id">
                        <tr>
                            <td class="py-1 pr-1">
                                <div class="font-bold">
                                    {{-- បង្ហាញឈ្មោះផលិតផល (អាចកែដាក់ item.product?.name_kh បើមាន) --}}
                                    <span x-text="item.product?.name || 'Unknown'"></span>
                                </div>
                                {{-- Addons --}}
                                <template x-if="item.addons && item.addons.length > 0">
                                    <div class="text-[11px] italic mt-0.5 ml-2">
                                        <template x-for="ad in item.addons">
                                            <div class="flex justify-between pr-2">
                                                <span>+ <span x-text="ad.addon?.name || 'Addon'"></span></span>
                                                <span x-text="'x' + (ad.quantity || 1)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </td>
                            <td class="text-center py-1" x-text="item.quantity"></td>
                            <td class="text-right py-1"><span x-text="formatPrice(getItemPrice(item))"></span></td>
                            <td class="text-right py-1 font-bold"><span x-text="formatPrice(getItemPrice(item) * item.quantity)"></span></td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>

        {{-- TOTALS --}}
        <div class="border-t border-dashed border-black pt-2">
            <div class="flex justify-between text-base font-bold">
                <span>សរុប ($):</span> <span x-text="formatPrice(orderDetails?.total_amount || 0)"></span>
            </div>
            <div class="flex justify-between text-base font-bold mb-1 border-b border-black pb-1">
                <span>សរុប (៛):</span> <span x-text="formatRiel(orderDetails?.total_amount || 0)"></span>
            </div>
            <div class="flex justify-between text-xs mt-1">
                <span>បង់ប្រាក់តាម:</span> <span class="uppercase font-bold" x-text="paymentMethod === 'qr' ? 'KHQR' : 'សាច់ប្រាក់'"></span>
            </div>
            <div class="flex justify-between text-xs">
                <span>ប្រាក់ទទួល:</span> <span x-text="formatPrice(receivedAmount || 0)"></span>
            </div>
            <div class="flex justify-between text-xs font-bold mt-1">
                <span>ប្រាក់អាប់:</span> 
                <div>
                    <span x-text="formatPrice(calculateChange())"></span> / <span x-text="formatRiel(calculateChange())"></span>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 border-t border-black pt-2">
            <p class="font-bold">*** សូមអរគុណ ***</p>
        </div>
    </div>
</div>