<div x-data="receiptPrinter()" 
     @print-receipt.window="prepareAndPrint($event.detail)" 
     id="receipt-print-area" 
     class="hidden print:block"> 
     <div class="w-[78mm] mx-auto p-2 font-mono text-black text-[12px] leading-tight bg-white">
        
        {{-- 1. HEADER --}}
        <div class="text-center mb-4">
            <h1 class="text-lg font-bold uppercase mb-1" x-text="orderDetails?.shop?.shop_en || 'POS SYSTEM'"></h1>
            <p x-text="orderDetails?.shop?.address_en || ''"></p>
            <p x-text="orderDetails?.shop?.phone_number ? ('Tel: ' + orderDetails.shop.phone_number) : ''"></p>
        </div>

        {{-- 2. INFO --}}
        <div class="border-b border-dashed border-black pb-2 mb-2">
            <div class="flex justify-between"><span>Inv:</span> <span class="font-bold" x-text="orderDetails?.invoice_number || '---'"></span></div>
            <div class="flex justify-between"><span>Date:</span> <span x-text="formatDate(orderDetails?.created_at)"></span></div>
            <div class="flex justify-between"><span>Table:</span> <span x-text="selectedTable?.name || 'N/A'"></span></div>
            <div class="flex justify-between"><span>Cashier:</span> <span>{{ auth()->user()->name }}</span></div>
        </div>

        {{-- 3. ITEMS TABLE --}}
        <table class="w-full mb-2">
            <thead>
                <tr class="border-b border-black">
                    <th class="text-left py-1 w-[45%]">Item</th>
                    <th class="text-center py-1 w-[15%]">Qty</th>
                    <th class="text-right py-1 w-[20%]">Price</th>
                    <th class="text-right py-1 w-[20%]">Total</th>
                </tr>
            </thead>
            <tbody class="align-top">
                <template x-if="orderDetails?.items && orderDetails.items.length > 0">
                    <template x-for="item in orderDetails.items" :key="item.id">
                        <tr>
                            <td class="py-1 pr-1">
                                <div class="font-bold">
                                    {{-- LOGIC: ពិនិត្យមើល Extra Item --}}
                                    <template x-if="isExtraItem(item)">
                                        <span>
                                            <span x-text="(item.addons && item.addons.length > 0) 
                                                ? (item.addons[0].addon?.name || item.addons[0].name) 
                                                : 'Extra Item'"></span>
                                        </span>
                                    </template>

                                    <template x-if="!isExtraItem(item)">
                                        <span x-text="item.product?.name || 'Unknown Item'"></span>
                                    </template>
                                </div>
                                
                                {{-- Addons List --}}
                                <template x-if="!isExtraItem(item) && item.addons && item.addons.length > 0">
                                    <div class="text-[10px] italic mt-0.5 ml-2">
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
                            
                            {{-- PRICE --}}
                            <td class="text-right py-1">
                                <span x-text="formatPrice(getItemPrice(item))"></span>
                            </td>

                            {{-- TOTAL --}}
                            <td class="text-right py-1 font-bold">
                                <span x-text="formatPrice(getItemPrice(item) * item.quantity)"></span>
                            </td>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>

        {{-- 4. TOTALS --}}
        <div class="border-t border-dashed border-black pt-2">
            <div class="flex justify-between text-base font-bold">
                <span>TOTAL ($):</span>
                <span x-text="formatPrice(orderDetails?.total_amount || 0)"></span>
            </div>
            <div class="flex justify-between text-base font-bold mb-1 border-b border-black pb-1">
                <span>TOTAL (R):</span>
                <span x-text="formatRiel(orderDetails?.total_amount || 0)"></span>
            </div>
            
            <div class="flex justify-between text-xs mt-1">
                <span>Paid By:</span>
                <span class="uppercase font-bold" x-text="paymentMethod === 'qr' ? 'KHQR' : 'CASH'"></span>
            </div>

            <template x-if="true"> <div>
                    <div class="flex justify-between text-xs">
                        <span>Received:</span> 
                        <span x-text="formatPrice(receivedAmount || 0)"></span>
                    </div>
                    <div class="flex justify-between text-xs font-bold mt-1">
                        <span>Change:</span> 
                        <div>
                            <span x-text="formatPrice(calculateChange())"></span>
                            <span class="mx-1">/</span>
                            <span x-text="formatRiel(calculateChange())"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="text-center mt-6 border-t border-black pt-2">
            <p class="font-bold">*** THANK YOU ***</p>
            <p class="text-[10px] mt-1" x-text="orderDetails?.shop?.description_en || 'Powered by POS'"></p>
        </div>
    </div>
</div>