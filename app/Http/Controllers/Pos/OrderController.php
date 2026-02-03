<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\Table;
use App\Models\Product;

use Rawilk\Printing\Facades\Printing;
use App\Models\KitchenDestination;
use Illuminate\Validation\Rule; // Import នៅខាងលើ

use Illuminate\Support\Facades\Log;       // ✅ បន្ថែម
use Illuminate\Support\Facades\Validator; // ✅ បន្ថែម

class OrderController extends Controller
{
    // ... (store function នៅដដែល)

    public function store(Request $request)
    {
        // ---------------------------------------------------------
        // ជំហានទី ១: កត់ត្រាទិន្នន័យដែលទទួលបានពី Frontend (Log Input)
        // ---------------------------------------------------------
        // Log::info('🟢 [POS ORDER] Starting Store Process...');
        // Log::info('📥 Data Received:', $request->all());

        // ---------------------------------------------------------
        // ជំហានទី ២: ធ្វើ Validation ដោយដៃ (Manual Validation)
        // ---------------------------------------------------------
        $validator = Validator::make($request->all(), [
            'table_id' => 'required', // ដាក់ធម្មតាសិន ដើម្បីចង់ដឹងថាវាជាប់អត់
            'items'    => 'required|array|min:1',
            
            // 🔥 កន្លែងរសើប: យើង Log មើលសិន កុំទាន់អាលតឹងរ៉ឹងពេក
            'items.*.product_id' => 'required', 
            'items.*.qty'        => 'required|integer|min:1',
        ]);

        // បើ Validation បរាជ័យ -> កត់ចូល Log ភ្លាម
        if ($validator->fails()) {
            // Log::error('❌ [POS ORDER] Validation Failed:', $validator->errors()->toArray());
            
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            try {
                // ---------------------------------------------------------
                // ជំហានទី ៣: ចាប់ផ្តើមបង្កើត Order
                // ---------------------------------------------------------
                
                // 1. Check Table
                // យើងប្រើឈ្មោះ Model ផ្ទាល់ដើម្បីអោយវាស្គាល់ Prefix 'vc_' ដោយស្វ័យប្រវត្តិ
                // បើបងប្រើ Table ឈ្មោះ 'vc_tables' ត្រូវប្រាកដថា Model Table មាន $table = 'tables' ឬអត់កំណត់
                
                $order = Order::firstOrCreate(
                    ['table_id' => $request->table_id, 'status' => 'pending'],
                    [
                        'invoice_number' => 'INV-' . time() . '-' . $request->table_id,
                        'user_id'        => Auth::id(),
                        'total_amount'   => 0,
                    ]
                );
                
                // Log::info('✅ Order Created/Found ID: ' . $order->id);

                // Update Table Status
                $table = \App\Models\Table::find($request->table_id);
                if ($table) {
                    $table->update(['status' => 'busy']);
                } else {
                    Log::warning('⚠️ Table ID ' . $request->table_id . ' not found in DB');
                }

                $newOrderItems = new \Illuminate\Database\Eloquent\Collection();

                foreach ($request->items as $index => $itemData) {
                    
                    // Log មើល Item នីមួយៗ
                    // Log::info("🔄 Processing Item #$index:", $itemData);

                    // ពិនិត្យមើលថា Product ID មានពិតឬអត់ មុននឹង Save
                    // ប្រើ App\Models\Product ដើម្បីអោយស្គាល់ vc_products
                    $productExists = \App\Models\Product::find($itemData['product_id']);
                    
                    if (!$productExists) {
                        // Log::error("❌ Product ID {$itemData['product_id']} not found in DB (vc_products). Skipping...");
                        // បើរកមិនឃើញ យើងអាច Return Error ឬរំលង
                        throw new \Exception("Product ID {$itemData['product_id']} not found.");
                    }

                    $orderItem = OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity'   => $itemData['qty'],
                        'price'      => $itemData['price'],
                        'note'       => $itemData['note'] ?? null,
                        'is_printed' => false,
                        'status'     => 'pending',
                        'created_by' => Auth::id(),
                    ]);
                    
                    $newOrderItems->push($orderItem);

                    if (!empty($itemData['addons'])) {
                        foreach ($itemData['addons'] as $addon) {
                            OrderItemAddon::create([
                                'order_item_id' => $orderItem->id,
                                'addon_id'      => $addon['id'],
                                'price'         => $addon['price'],
                                'quantity'      => $addon['qty'] ?? 1
                            ]);
                        }
                    }
                }
                
                $this->recalculateOrderTotal($order->id);
                // Log::info('💰 Total Recalculated');

                // ... (ផ្នែក Auto Print រក្សាទុកដដែល ឬលុបចោលសិនក៏បានដើម្បីតេស្ត) ...

                // Log::info('🎉 Order Transaction Completed Successfully!');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Order placed successfully!',
                    'order_id' => $order->id
                ]);

            } catch (\Exception $e) {
                // ចាប់ Error គ្រប់បែបយ៉ាងនៅទីនេះ
                // Log::error('🔥 [POS ORDER] Exception Error: ' . $e->getMessage());
                // Log::error($e->getTraceAsString()); // ចង់ដឹងថាខុសនៅបន្ទាត់ណា

                // បោះ Error ទៅ Frontend វិញ
                return response()->json([
                    'status' => 'error',
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    // 🔥 FUNCTION ថ្មីសម្រាប់កែចំនួន ឬលុបមុខម្ហូប
    public function updateItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action'  => 'required|in:increase,decrease,remove',
        ]);

        return DB::transaction(function () use ($request) {
            $item = OrderItem::with('addons')->findOrFail($request->item_id);
            
            if ($request->action === 'remove') {
                // លុបចោលទាំងស្រុង (Addons នឹងលុបតាមរយៈ Cascade ឬលុបដៃ)
                OrderItemAddon::where('order_item_id', $item->id)->delete();
                $item->delete();
            } 
            elseif ($request->action === 'increase') {
                $item->increment('quantity');
            } 
            elseif ($request->action === 'decrease') {
                if ($item->quantity > 1) {
                    $item->decrement('quantity');
                } else {
                    // បើចំនួននៅសល់ ១ ហើយចុចដក គឺលុបចោលតែម្តង
                    OrderItemAddon::where('order_item_id', $item->id)->delete();
                    $item->delete();
                }
            }

            // គណនាតម្លៃសរុបឡើងវិញភ្លាមៗ
            $newTotal = $this->recalculateOrderTotal($item->order_id);

            // ពិនិត្យមើលថាបើអស់ម្ហូបពី Order ត្រូវ update table ទៅ available វិញឬអត់ (Optional)
            $remainingItems = OrderItem::where('order_id', $item->order_id)->count();
            if ($remainingItems == 0) {
                 // អាចនឹង update status order ទៅ cancel ឬទុកដដែល
            }

            return response()->json([
                'status' => 'success',
                'total'  => $newTotal
            ]);
        });
    }

    

    // 🔥 FUNCTION ថ្មី ១: គ្រាន់តែអានមុខម្ហូបពីតុដែលចង់ Merge (Read Only)
    public function getItemsForMerge($tableId)
    {
        $order = Order::with(['items.product', 'items.addons.addon'])
                      ->where('table_id', $tableId)
                      ->where('status', 'pending')
                      ->first();

        if (!$order) {
            return response()->json(['items' => []]);
        }

        return response()->json([
            'items' => $order->items,
            'source_order_id' => $order->id // ទុកចំណាំថាបានមកពី Order ណា
        ]);
    }

    // 🔥 FUNCTION ២: កែ Checkout ឲ្យចេះ "Adopt/Claim" មុខម្ហូបពីតុផ្សេង
    public function checkout(Request $request)
    {
        $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'received_amount'=> 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qr,card',
            'items'          => 'required|array', 
        ]);

        return DB::transaction(function () use ($request) {
            $mainOrder = Order::findOrFail($request->order_id);

            if ($mainOrder->status == 'completed') {
                return response()->json(['status' => 'error', 'message' => 'Order is already paid!'], 400);
            }

            // =========================================================
            // ជំហានពិសេស: SYNC & ADOPT ITEMS (ទទួលយកមុខម្ហូបពី Merge)
            // =========================================================
            
            // 1. ប្រមូល ID មុខម្ហូបទាំងអស់ដែល User បញ្ជូនមក (រួមទាំងរបស់តុ Merge ផង)
            $submittedItemIds = collect($request->items)->pluck('id')->filter()->toArray();

            // 2. លុបមុខម្ហូបណាដែលជារបស់ Main Order តែមិនមានក្នុង List (User លុបចោល)
            // ចំណាំ៖ យើងមិនលុបរបស់ Merge Order ទេ ព្រោះបើគេមិនយក វាគ្រាន់តែនៅតុដើមដដែល
            OrderItem::where('order_id', $mainOrder->id)
                     ->whereNotIn('id', $submittedItemIds)
                     ->delete(); 

            // 3. Update & Move Items
            // យើងនឹងកត់ត្រាថា Order ណាខ្លះដែលត្រូវបានរងផលប៉ះពាល់ (ដើម្បីលុបចោលពេលវាទំនេរ)
            $affectedOrderIds = [$mainOrder->id];

            foreach ($request->items as $itemData) {
                $item = OrderItem::find($itemData['id']);
                
                if ($item) {
                    // ប្រសិនបើ Item នេះមកពី Order ផ្សេង (Merge), កត់ត្រា ID Order ចាស់ទុក
                    if ($item->order_id != $mainOrder->id) {
                        $affectedOrderIds[] = $item->order_id;
                    }

                    // 🔥 សំខាន់បំផុត: ផ្លាស់ប្តូរម្ចាស់ (Move Item to Current Order)
                    $item->update([
                        'quantity' => $itemData['quantity'],
                        'order_id' => $mainOrder->id // ផ្ទេរមក Order នេះទាំងអស់
                    ]);

                    // Handle Addons (កូដដដែល)
                    if (!empty($itemData['addons'])) {
                        $submittedAddonIds = collect($itemData['addons'])->pluck('id')->toArray();
                        OrderItemAddon::where('order_item_id', $item->id)->whereNotIn('id', $submittedAddonIds)->delete();
                        foreach ($itemData['addons'] as $addonData) {
                            OrderItemAddon::where('id', $addonData['id'])->update(['quantity' => $addonData['quantity']]);
                        }
                    } else {
                        $item->addons()->delete();
                    }
                }
            }

            // 4. CLEANUP: ពិនិត្យមើល Order ចាស់ៗដែលត្រូវបាន Merge មក
            // បើគេយកអស់ហើយ (Empty Items) ត្រូវលុប Order ចោល និង Free Table
            $otherOrderIds = array_unique(array_diff($affectedOrderIds, [$mainOrder->id]));
            
            foreach ($otherOrderIds as $oldOrderId) {
                $oldOrder = Order::find($oldOrderId);
                if ($oldOrder && $oldOrder->items()->count() == 0) {
                    // បើអស់ម្ហូបហើយ -> Free Table
                    if ($oldOrder->table_id) {
                        Table::where('id', $oldOrder->table_id)->update(['status' => 'available']);
                    }
                    // លុប Order ចោល
                    $oldOrder->delete();
                }
            }

            // =========================================================
            // ជំហានបញ្ចប់: គិតលុយ (Payment)
            // =========================================================

            $totalAmount = $this->recalculateOrderTotal($mainOrder->id);
            $change = $request->received_amount - $totalAmount;

            if ($request->payment_method == 'cash' && round($change, 2) < 0) {
                return response()->json(['status' => 'error', 'message' => 'Not enough cash!'], 422);
            }

            $mainOrder->update([
                'status'          => 'completed',
                'total_amount'    => $totalAmount,
                'payment_method'  => $request->payment_method,
                'received_amount' => $request->received_amount,
                'change_amount'   => $change,
                'paid_at'         => now(),
            ]);

            if ($mainOrder->table_id) {
                Table::where('id', $mainOrder->table_id)->update(['status' => 'available']);
            }

            return response()->json([
                'status'   => 'success',
                'message'  => 'Transaction completed (Merged & Paid)!',
                'change'   => $change,
            ]);
        });
    }

    // Helper Function
    private function recalculateOrderTotal($orderId)
    {
        $order = Order::with(['items.addons'])->find($orderId);
        $totalAmount = 0;

        foreach ($order->items as $item) {
            $itemTotal = $item->price * $item->quantity;
            $addonTotal = 0;
            foreach ($item->addons as $addon) {
                $addonTotal += ($addon->price * ($addon->quantity ?? 1));
            }
            $totalAmount += ($itemTotal + $addonTotal);
        }

        $order->update(['total_amount' => $totalAmount]);
        return $totalAmount;
    }

    public function updateAddon(Request $request)
    {
        $request->validate([
            'addon_row_id' => 'required|exists:order_item_addons,id', // ID របស់បន្ទាត់ Addon
            'action'       => 'required|in:increase,decrease,remove',
        ]);

        return DB::transaction(function () use ($request) {
            $addon = OrderItemAddon::findOrFail($request->addon_row_id);
            
            // ទាញយក Order Item ដើម្បីដឹងថាវាជារបស់ Order ណា (សម្រាប់គណនាលុយសរុប)
            $orderItem = OrderItem::find($addon->order_item_id);

            if ($request->action === 'remove') {
                $addon->delete();
            } 
            elseif ($request->action === 'increase') {
                $addon->increment('quantity');
            } 
            elseif ($request->action === 'decrease') {
                if ($addon->quantity > 1) {
                    $addon->decrement('quantity');
                } else {
                    // បើនៅសល់ 1 ហើយចុចដក គឺលុបចោល
                    $addon->delete();
                }
            }

            // គណនាលុយសរុបឡើងវិញ (Function នេះមានស្រាប់ពីកូដមុន)
            $newTotal = $this->recalculateOrderTotal($orderItem->order_id);

            return response()->json([
                'status' => 'success',
                'total'  => $newTotal
            ]);
        });
    }




    public function getBusyTablesForMerge(Request $request)
    {
        // 1. ចាប់យកលេខតុបច្ចុប្បន្នពី URL (?current=5)
        $currentTableId = $request->query('current');

        // 2. ការពារករណីអត់មាន ID
        if (!$currentTableId) {
            return response()->json([]);
        }

        // 3. ទាញយកតុដែលរវល់ (Busy) តែមិនមែនតុខ្លួនឯង
        // (ប្រើ Model Table ដើម្បីអោយស្គាល់ prefix 'vc_' ដោយស្វ័យប្រវត្តិ)
        $tables = \App\Models\Table::where('status', 'busy')
                    ->where('id', '!=', $currentTableId)
                    ->select('id', 'name')
                    ->orderBy('name', 'asc')
                    ->get();

        return response()->json($tables);
    }

    public function mergeTables(Request $request)
    {
        $request->validate([
            'target_table_id' => 'required',
            'main_table_id'   => 'required',
        ]);

        return DB::transaction(function () use ($request) {
            try {
                // 1. រក Order របស់តុទាំងពីរ
                $mainOrder = Order::where('table_id', $request->main_table_id)
                                  ->where('status', 'pending')
                                  ->first();

                $targetOrder = Order::where('table_id', $request->target_table_id)
                                    ->where('status', 'pending')
                                    ->first();

                // Check ការពារ
                if (!$mainOrder) {
                    throw new \Exception("តុបច្ចុប្បន្នគ្មាន Order ដើម្បីបញ្ចូលទេ");
                }
                if (!$targetOrder) {
                    throw new \Exception("តុដែលត្រូវបញ្ចូល (Target) គ្មាន Order ទេ");
                }

                // 2. ផ្ទេរ Items ទាំងអស់ពី Target -> Main
                foreach ($targetOrder->items as $item) {
                    $item->update(['order_id' => $mainOrder->id]);
                }

                // 3. 🔥 ចំណុចសំខាន់៖ លុប Order ចាស់ចោល (Delete)
                // យើងមិន Update Status ទៅ 'merged' ទេ ព្រោះ DB អត់ស្គាល់
                // ម្យ៉ាងទៀត Order នេះអស់តម្លៃហើយ (Items ទៅអស់ហើយ) លុបចោលល្អជាង
                $targetOrder->delete();
                
                // 4. Update តុដែលត្រូវបញ្ចូល (Target) អោយទំនេរវិញ (Available)
                // (Field status ក្នុង vc_tables ទទួលយក 'available' | 'busy')
                \App\Models\Table::where('id', $request->target_table_id)
                                 ->update(['status' => 'available']);

                // 5. គណនាលុយសរុបអោយ Main Order ឡើងវិញ
                $newTotal = $this->recalculateOrderTotal($mainOrder->id);

                return response()->json([
                    'status' => 'success', 
                    'message' => 'បញ្ចូលតុជោគជ័យ!',
                    'new_total' => $newTotal
                ]);

            } catch (\Exception $e) {
                // បោះ Error ទៅ Frontend ជា Toast Message
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    // =========================================================
    // មុខងារទី ២: SPLIT BILL (បំបែកការគិតលុយ)
    // =========================================================
    public function splitPayment(Request $request)
    {
        $request->validate([
            'original_order_id' => 'required|exists:orders,id',
            'split_items'       => 'required|array|min:1', // [{id: 1, qty: 1}, {id: 2, qty: 2}]
            'payment_method'    => 'required',
            'received_amount'   => 'required'
        ]);

        return DB::transaction(function () use ($request) {
            $originalOrder = Order::findOrFail($request->original_order_id);

            // 1. បង្កើត Order ថ្មីសម្រាប់បំបែក (Sub Order)
            // សម្គាល់៖ Order នេះមិនមាន Table ID ទេ ឬប្រើ Table ID ដដែលក៏បាន តែ status completed ភ្លាមៗ
            $splitOrder = Order::create([
                'invoice_number'  => 'SPL-' . time(),
                'user_id'         => Auth::id(),
                'table_id'        => $originalOrder->table_id, // នៅតុដដែល
                'status'          => 'completed', // គិតលុយភ្លាមៗ
                'payment_method'  => $request->payment_method,
                'received_amount' => $request->received_amount,
                'total_amount'    => 0, // នឹងគណនាតាមក្រោយ
                'paid_at'         => now()
            ]);

            // 2. ដំណើរការផ្ទេរ Item
            foreach ($request->split_items as $splitItem) {
                $originalItem = OrderItem::with('addons')->find($splitItem['id']);
                
                if (!$originalItem) continue;

                $qtyToSplit = intval($splitItem['qty']);

                if ($qtyToSplit >= $originalItem->quantity) {
                    // ករណីទី 1: យកទាំងអស់ -> គ្រាន់តែប្តូរ order_id ទៅ Order ថ្មី
                    $originalItem->update(['order_id' => $splitOrder->id]);
                } else {
                    // ករណីទី 2: យកតែមួយផ្នែក -> ត្រូវបំបែក Row
                    
                    // ក. បន្ថយចំនួនពី Item ចាស់
                    $originalItem->decrement('quantity', $qtyToSplit);

                    // ខ. បង្កើត Item ថ្មីក្នុង Order ថ្មី
                    $newItem = $originalItem->replicate();
                    $newItem->order_id = $splitOrder->id;
                    $newItem->quantity = $qtyToSplit;
                    $newItem->save();

                    // គ. ចម្លង Addons (បើមាន)
                    foreach ($originalItem->addons as $addon) {
                        $newAddon = $addon->replicate();
                        $newAddon->order_item_id = $newItem->id;
                        // Addon ក៏ត្រូវបំបែកតាមសមាមាត្រដែរ (សន្មតថាកាត់តាម Item)
                        $newAddon->save();
                    }
                }
            }

            // 3. គណនាលុយឡើងវិញទាំងសងខាង
            $splitTotal = $this->recalculateOrderTotal($splitOrder->id);
            $this->recalculateOrderTotal($originalOrder->id);

            // Update Change amount
            $change = $request->received_amount - $splitTotal;
            $splitOrder->update(['total_amount' => $splitTotal, 'change_amount' => $change]);

            // 4. ពិនិត្យមើល Original Order
            // បើ Original Order អស់ Item ហើយ -> Mark as Completed ដែរ
            if ($originalOrder->items()->count() == 0) {
                $originalOrder->update(['status' => 'completed']);
                \App\Models\Table::where('id', $originalOrder->table_id)->update(['status' => 'available']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'បំបែកការគិតលុយជោគជ័យ!',
                'split_order_id' => $splitOrder->id,
                'remaining_items_count' => $originalOrder->items()->count(),
                'change' => $change
            ]);
        });
    }
}