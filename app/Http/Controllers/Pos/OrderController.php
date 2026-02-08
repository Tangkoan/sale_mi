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


use App\Models\KitchenDestination;


use Illuminate\Validation\Rule; // Import នៅខាងលើ
use Illuminate\Support\Facades\Log;       // ✅ បន្ថែម
use Illuminate\Support\Facades\Validator; // ✅ បន្ថែម

// Library សម្រាប់ Print (តាមកូដដើមរបស់អ្នក)
use Rawilk\Printing\Receipts\ReceiptPrinter;
use Rawilk\Printing\Facades\Printing;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

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
            'table_id' => 'required',
            'items'    => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.addons'     => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation Error',
                'errors'  => $validator->errors()
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            try {
                // 1. Create/Find Order
                $order = Order::firstOrCreate(
                    ['table_id' => $request->table_id, 'status' => 'pending'],
                    [
                        'invoice_number' => 'INV-' . time() . '-' . $request->table_id,
                        'user_id'        => Auth::id(),
                        'total_amount'   => 0,
                    ]
                );

                // Update Table Status
                $table = Table::find($request->table_id);
                if ($table) {
                    $table->update(['status' => 'busy']);
                }

                // 2. Add Items
                foreach ($request->items as $itemData) {
                    $product = Product::find($itemData['product_id']);
                    
                    if (!$product) {
                        throw new \Exception("Product ID {$itemData['product_id']} not found.");
                    }

                    $orderItem = OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity'   => $itemData['qty'],
                        'price'      => $itemData['price'],
                        'note'       => $itemData['note'] ?? null,
                        'is_printed' => false, // ✅ សំខាន់៖ ដាក់ false សិន ដើម្បីចាំ Print
                        'status'     => 'pending',
                        'created_by' => Auth::id(),
                    ]);

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
                
                // Recalculate Total
                $this->recalculateOrderTotal($order->id);

                // ---------------------------------------------------------
                // ជំហានទី ៤ (ថ្មី): ហៅ Function ដើម្បី Print ទៅផ្ទះបាយ
                // ---------------------------------------------------------
                try {
                    $this->printOrderToKitchen($order->id);
                } catch (\Exception $printError) {
                    // បើ Print error, កុំអោយខូច Transaction គ្រាន់តែ Log ទុក
                    Log::error("🖨️ Printing Error: " . $printError->getMessage());
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Order placed successfully!',
                    'order_id' => $order->id
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        });
    }


    /**
     * 🔥 FUNCTION: បោះទៅ Printer តាមផ្នែក (Wok, Soup, Bar...)
     */
    private function printOrderToKitchen($orderId)
    {
        // 1. ទាញយក Items ដែលមិនទាន់បាន Print
        $itemsToPrint = OrderItem::with([
                'product.category.kitchenDestination', // ត្រូវតែ Load Relation នេះមក
                'addons.addon',
                'order.table'
            ])
            ->where('order_id', $orderId)
            ->where('is_printed', false) // យកតែអាមិនទាន់ Print
            ->get();

        if ($itemsToPrint->isEmpty()) {
            return;
        }

        // 2. Group Items តាម Destination ID
        // យើងបង្កើត Array មួយដើម្បីផ្ទុកទិន្នន័យដាច់ដោយឡែកតាមផ្នែក
        $kitchenBatches = [];

        foreach ($itemsToPrint as $item) {
            // ទាញយក Destination ពី Product -> Category
            $destination = $item->product?->category?->kitchenDestination;

            // បើអត់មាន Destination ឬអត់ Active ទេ គឺរំលង (ឬបោះទៅ Default Printer បើចង់)
            if (!$destination || !$destination->is_active) {
                Log::warning("Item {$item->product->name} គ្មាន Kitchen Destination ឬមិន Active។");
                continue;
            }

            // Group ដោយប្រើ ID របស់ Destination ដើម្បីកុំឱ្យច្រឡំគ្នា
            $batchKey = $destination->id;

            if (!isset($kitchenBatches[$batchKey])) {
                $kitchenBatches[$batchKey] = [
                    'info'  => $destination, // ទុកព័ត៌មាន Printer (IP, Name)
                    'items' => []
                ];
            }

            $kitchenBatches[$batchKey]['items'][] = $item;
        }

        // 3. ចាប់ផ្តើម Print ម្ដងមួយផ្នែក (Loop តាម Batch ដែលបាន Group)
        foreach ($kitchenBatches as $batchKey => $batch) {
            $printerInfo = $batch['info']; // ព័ត៌មានផ្នែក (ឈ្មោះ, IP)
            $items       = $batch['items']; // មុខម្ហូបក្នុងផ្នែកនោះ

            $ipAddress = $printerInfo->printnode_id; // តាមរូបភាព Field នេះទុក IP
            $port      = 9100; // Port របស់ Printer ភាគច្រើន

            try {
                // ភ្ជាប់ទៅ Printer តាម IP
                $connector = new NetworkPrintConnector($ipAddress, $port);
                $printer   = new Printer($connector);

                // --- HEADER (បង្ហាញឈ្មោះផ្នែកច្បាស់ៗ) ---
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
                $printer->text($printerInfo->name . "\n"); // ឧ: "WOK", "SOUP", "BAR"
                $printer->selectPrintMode(); // Reset
                $printer->text("--------------------------------\n");

                // ព័ត៌មានតុ និង ម៉ោង
                $firstItem = $items[0];
                $tableName = $firstItem->order->table->name ?? ('Table: ' . $firstItem->order->table_id);
                
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Table : " . $tableName . "\n");
                $printer->text("Inv # : " . $firstItem->order->invoice_number . "\n");
                $printer->text("Date  : " . date('d/m/Y H:i') . "\n");
                $printer->text("--------------------------------\n");

                // --- LIST ITEMS ---
                foreach ($items as $item) {
                    $productName = $item->product->name ?? 'Unknown';

                    // បង្ហាញចំនួន និង ឈ្មោះ (អក្សរធំ)
                    $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_FONT_B);
                    $printer->text("{$item->quantity} x {$productName}\n");
                    $printer->selectPrintMode(); 

                    // Note
                    if ($item->note) {
                        $printer->text("   (Note: {$item->note})\n");
                    }

                    // Addons
                    foreach ($item->addons as $addonRow) {
                        $addonName = $addonRow->addon->name ?? 'Extra';
                        $printer->text("   + {$addonName} (x{$addonRow->quantity})\n");
                    }
                    
                    $printer->text("\n"); // ដកឃ្លាមួយបន្ទាត់រវាងមុខម្ហូប
                }

                // --- FOOTER ---
                $printer->text("--------------------------------\n");
                $printer->text("Printed By: " . Auth::user()->name . "\n");
                $printer->text("\n\n\n");
                
                // Cut Paper
                $printer->cut();
                $printer->close();

                // 4. Update Status ថាបាន Print រួចរាល់
                foreach ($items as $item) {
                    $item->update(['is_printed' => true]);
                }

                Log::info("✅ Printed [{$printerInfo->name}] to IP: $ipAddress");

            } catch (\Exception $e) {
                // បើ Print មិនចេញ (ដាច់ភ្លើង, ខុស IP) កត់ចូល Log តែមិនឱ្យគាំង System
                Log::error("❌ Print Error [{$printerInfo->name} - $ipAddress]: " . $e->getMessage());
            }
        }
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
        // Load relationship 'addons' អោយហើយ
        $order = Order::with(['items.addons'])->find($orderId); 
        $totalAmount = 0;

        if ($order && $order->items) {
            foreach ($order->items as $item) {
                $itemTotal = $item->price * $item->quantity;
                $addonTotal = 0;

                // 🔥 កែត្រង់នេះ៖ ពី $item->items_addons មកជា $item->addons
                // ថែម ( ?? [] ) ដើម្បីការពារកុំអោយ Error បើវាអត់មាន Addons
                foreach ($item->addons ?? [] as $addon) { 
                    $addonTotal += ($addon->price * ($addon->quantity ?? 1));
                }
                
                $totalAmount += ($itemTotal + $addonTotal);
            }

            $order->update(['total_amount' => $totalAmount]);
        }
        
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


    // =========================================================
    // 🔥 FIXED FUNCTION: MOVE TABLE (With Error Handling)
    // =========================================================
    public function moveTable(Request $request)
    {
        // 1. Validation (កុំទាន់ដាក់ exists:vc_tables ដើម្បីការពារបញ្ហា Table Name ខុស)
        $validator = Validator::make($request->all(), [
            'current_table_id' => 'required',
            'target_table_id'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                
                // A. រកតុថ្មី (Target Table)
                $targetTable = Table::find($request->target_table_id);
                if (!$targetTable) {
                    throw new \Exception("រកមិនឃើញតុគោលដៅ (ID: {$request->target_table_id})");
                }
                
                if ($targetTable->status !== 'available') {
                    throw new \Exception("តុ {$targetTable->name} មិនទំនេរទេ (Status: {$targetTable->status})");
                }

                // B. រក Order នៃតុបច្ចុប្បន្ន
                $order = Order::where('table_id', $request->current_table_id)
                              ->where('status', 'pending') // យកតែ Order ដែលមិនទាន់គិតលុយ
                              ->first();

                if (!$order) {
                    throw new \Exception("តុបច្ចុប្បន្នគ្មានការកម្មង់ទេ (ឬត្រូវបានគិតលុយរួចរាល់)");
                }

                // C. ដំណើរការប្ដូរ (Update)
                // 1. ប្ដូរលេខតុនៅក្នុង Order
                $order->update(['table_id' => $request->target_table_id]);

                // 2. Update Status តុចាស់ -> Available
                Table::where('id', $request->current_table_id)->update(['status' => 'available']);
                
                // 3. Update Status តុថ្មី -> Busy
                $targetTable->update(['status' => 'busy']);

                return response()->json([
                    'status'  => 'success',
                    'message' => "បានប្ដូរទៅតុ {$targetTable->name} ជោគជ័យ!"
                ]);
            });

        } catch (\Exception $e) {
            // កត់ត្រាទុកក្នុង Log (storage/logs/laravel.log)
            Log::error('MOVE TABLE ERROR: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // បោះ Error មក Frontend ដើម្បីអោយដឹងថាខុសអី
            return response()->json([
                'status'  => 'error',
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }
}