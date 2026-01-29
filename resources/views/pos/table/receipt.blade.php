<div id="receipt-print-area" class="hidden">
    <div class="w-[78mm] mx-auto p-2 font-mono text-black text-[12px] leading-tight">
        {{-- Header --}}
        <div class="text-center mb-4">
            <template x-if="orderDetails.shop && orderDetails.shop.logo">
                <img :src="'/storage/' + orderDetails.shop.logo" class="h-12 mx-auto mb-2 object-contain grayscale"> 
            </template>
            <h1 class="text-lg font-bold uppercase mb-1" x-text="orderDetails.shop ? orderDetails.shop.shop_en : 'POS SYSTEM'"></h1>
            <p x-text="orderDetails.shop ? orderDetails.shop.address_en : ''"></p>
            <p x-text="orderDetails.shop ? ('Tel: ' + orderDetails.shop.phone_number) : ''"></p>
        </div>

        {{-- Info --}}
        <div class="border-b border-dashed border-black pb-2 mb-2">
            <div class="flex justify-between"><span>Inv:</span> <span class="font-bold" x-text="orderDetails.invoice_number"></span></div>
            <div class="flex justify-between"><span>Date:</span> <span x-text="orderDetails.date"></span></div>
            <div class="flex justify-between"><span>Table:</span> <span x-text="selectedTable ? selectedTable.name : ''"></span></div>
            <div class="flex justify-between"><span>Cashier:</span> <span>{{ auth()->user()->name }}</span></div>
        </div>

        {{-- Items --}}
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
                <template x-for="item in orderDetails.items" :key="item.id">
                    <tr>
                        <td class="py-1 pr-1">
                            <div class="font-bold" x-text="item.product.name"></div>
                            {{-- Print Addons with Qty --}}
                            <template x-if="item.addons && item.addons.length > 0">
                                <div class="text-[10px] italic mt-0.5">
                                    <template x-for="ad in item.addons">
                                        <div class="flex justify-between pr-2">
                                            <span>+ <span x-text="ad.addon ? ad.addon.name : 'Addon'"></span></span>
                                            <span>
                                                <span x-text="'x' + (ad.quantity || 1)"></span>
                                                <span x-text="'($' + (parseFloat(ad.price) * (ad.quantity || 1)).toFixed(2) + ')'"></span>
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </td>
                        <td class="text-center py-1" x-text="item.quantity"></td>
                        <td class="text-right py-1" x-text="parseFloat(item.price).toFixed(2)"></td>
                        <td class="text-right py-1 font-bold" x-text="(parseFloat(item.price) * item.quantity).toFixed(2)"></td>
                    </tr>
                </template>
            </tbody>
        </table>

        {{-- Total & Footer --}}
        <div class="border-t border-dashed border-black pt-2">
            <div class="flex justify-between text-base font-bold mb-1">
                <span>TOTAL:</span>
                <span x-text="'$' + parseFloat(orderDetails.total || 0).toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-xs">
                <span>Paid By:</span>
                <span class="uppercase font-bold" x-text="paymentMethod === 'qr' ? 'KHQR' : 'CASH'"></span>
            </div>
            <template x-if="paymentMethod === 'cash'">
                <div>
                    <div class="flex justify-between text-xs"><span>Received:</span> <span x-text="'$' + parseFloat(receivedAmount || 0).toFixed(2)"></span></div>
                    <div class="flex justify-between text-xs font-bold mt-1"><span>Change:</span> <span x-text="'$' + Math.max(0, (receivedAmount || 0) - (orderDetails.total || 0)).toFixed(2)"></span></div>
                </div>
            </template>
        </div>
        <div class="text-center mt-6 border-t border-black pt-2">
            <p class="font-bold">*** THANK YOU ***</p>
            <p class="text-[10px] mt-1" x-text="orderDetails.shop ? orderDetails.shop.description_en : 'Powered by IceCream POS'"></p>
        </div>
    </div>
</div>