<div id="receipt-print-area" class="hidden">
    <div class="w-[78mm] mx-auto p-2 font-mono text-black text-[12px] leading-tight">
        
        {{-- 1. HEADER --}}
        <div class="text-center mb-4">
            <h1 class="text-lg font-bold uppercase mb-1" x-text="orderDetails.shop ? orderDetails.shop.shop_en : 'POS SYSTEM'"></h1>
            <p x-text="orderDetails.shop ? orderDetails.shop.address_en : ''"></p>
            <p x-text="orderDetails.shop ? ('Tel: ' + orderDetails.shop.phone_number) : ''"></p>
        </div>

        {{-- 2. INFO --}}
        <div class="border-b border-dashed border-black pb-2 mb-2">
            <div class="flex justify-between"><span>Inv:</span> <span class="font-bold" x-text="orderDetails.invoice_number"></span></div>
            <div class="flex justify-between"><span>Date:</span> <span x-text="orderDetails.date"></span></div>
            <div class="flex justify-between"><span>Table:</span> <span x-text="selectedTable ? selectedTable.name : ''"></span></div>
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
                <template x-for="item in orderDetails.items" :key="item.id">
                    <tr>
                        <td class="py-1 pr-1">
                            <div class="font-bold">
                                {{-- 🔥 RECEIPT LOGIC: ប្តូរឈ្មោះ Extra ទៅជាឈ្មោះ Addon --}}
                                <template x-if="isExtraItem(item) && item.addons.length > 0">
                                    <span x-text="item.addons[0].addon ? item.addons[0].addon.name : item.addons[0].name"></span>
                                </template>
                                <template x-if="isExtraItem(item) && item.addons.length == 0">
                                    <span>មុខម្ហូបបន្ថែម</span>
                                </template>
                                <template x-if="!isExtraItem(item)">
                                    <span x-text="item.product ? item.product.name : 'Unknown'"></span>
                                </template>
                            </div>
                            
                            {{-- បង្ហាញ Addon List តែចំពោះ Product ធម្មតា --}}
                            <template x-if="!isExtraItem(item) && item.addons && item.addons.length > 0">
                                <div class="text-[10px] italic mt-0.5">
                                    <template x-for="ad in item.addons">
                                        <div class="flex justify-between pr-2">
                                            <span>+ <span x-text="ad.addon ? ad.addon.name : 'Addon'"></span></span>
                                            <span><span x-text="'x' + (ad.quantity || 1)"></span></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </td>

                        <td class="text-center py-1" x-text="item.quantity"></td>
                        
                        <td class="text-right py-1">
                            {{-- បើ Extra យកតម្លៃពី Addon --}}
                            <template x-if="isExtraItem(item) && item.addons.length > 0">
                                <span x-text="parseFloat(item.addons[0].price).toFixed(2)"></span>
                            </template>
                            <template x-if="!isExtraItem(item)">
                                <span x-text="parseFloat(item.price).toFixed(2)"></span>
                            </template>
                        </td>

                        <td class="text-right py-1 font-bold">
                            {{-- តម្លៃសរុប (Extra = Addon price * quantity) --}}
                            <template x-if="isExtraItem(item) && item.addons.length > 0">
                                <span x-text="(parseFloat(item.addons[0].price) * item.quantity).toFixed(2)"></span>
                            </template>
                            <template x-if="!isExtraItem(item)">
                                <span x-text="(parseFloat(item.price) * item.quantity).toFixed(2)"></span>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        {{-- 4. TOTALS --}}
        <div class="border-t border-dashed border-black pt-2">
            <div class="flex justify-between text-base font-bold">
                <span>TOTAL ($):</span>
                <span x-text="'$' + parseFloat(orderDetails.total || 0).toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-base font-bold mb-1 border-b border-black pb-1">
                <span>TOTAL (R):</span>
                <span x-text="Math.ceil(parseFloat(orderDetails.total || 0) * exchangeRate).toLocaleString('km-KH') + ' ៛'"></span>
            </div>
            
            <div class="flex justify-between text-xs mt-1">
                <span>Paid By:</span>
                <span class="uppercase font-bold" x-text="paymentMethod === 'qr' ? 'KHQR' : 'CASH'"></span>
            </div>

            <template x-if="paymentMethod === 'cash'">
                <div>
                    <div class="flex justify-between text-xs">
                        <span>Received:</span> 
                        <span x-text="'$' + parseFloat(receivedAmount || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-xs font-bold mt-1">
                        <span>Change:</span> 
                        <div>
                            <span x-text="'$' + Math.max(0, (receivedAmount || 0) - (orderDetails.total || 0)).toFixed(2)"></span>
                            <span class="mx-1">/</span>
                            <span x-text="(Math.max(0, (receivedAmount || 0) - (orderDetails.total || 0)) * exchangeRate).toLocaleString('km-KH') + ' ៛'"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="text-center mt-6 border-t border-black pt-2">
            <p class="font-bold">*** THANK YOU ***</p>
            <p class="text-[10px] mt-1" x-text="orderDetails.shop ? orderDetails.shop.description_en : 'Powered by IceCream POS'"></p>
        </div>
    </div>
</div>