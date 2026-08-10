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
use App\Models\ShopInfo;
use App\Models\KitchenDestination;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View; // ✅ បន្ថែម View Facade សម្រាប់ Render Blade

// Library សម្រាប់ Print
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Illuminate\Support\Facades\File;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\ImagickEscposImage;

// Library សម្រាប់ថតរូបវិក្កយបត្រ (ថ្មី)
use Spatie\Browsershot\Browsershot; 

class OrderController extends Controller
{
    public function store(Request $request)
    {
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
                        'check_in_time'  => now(),
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
                        'is_printed' => false, 
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

                ob_start();

                try {
                    $this->printOrderToKitchen($order->id);
                } catch (\Exception $printError) {
                    Log::error("🖨️ Printing Error: " . $printError->getMessage());
                }

                ob_end_clean();

                if (ob_get_level() > 0) {
                    ob_clean();
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Order placed successfully!',
                    'order_id' => $order->id
                ]);

            } catch (\Exception $e) {
                if (ob_get_level() > 0) {
                    ob_clean();
                }
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    /**
     * 🔥 FUNCTION: បោះទៅ Printer តាមផ្នែកដោយប្រើ Browsershot (HTML ទៅ Image) 
     * ដើម្បីให้อក្សរខ្មែរចេញមកត្រូវទម្រង់ស្អាត ១០០%
     */
    /**
     * 🔥 FUNCTION: បោះទៅ Printer តាមផ្នែកដោយប្រើ Browsershot (HTML ទៅ Image) 
     * ធានាថាអក្សរខ្មែរចេញមកត្រូវទម្រង់ស្អាត ១០០%
     */
    private function printOrderToKitchen($orderId)
    {
        $itemsToPrint = OrderItem::with([
                'product.category.kitchenDestination', 
                'addons.addon',
                'order.table' 
            ])
            ->where('order_id', $orderId)
            ->where('is_printed', false)
            ->get();

        if ($itemsToPrint->isEmpty()) { return; }

        $kitchenBatches = [];
        foreach ($itemsToPrint as $item) {
            $destination = $item->product?->category?->kitchenDestination;
            if (!$destination || !$destination->is_active) { continue; }
            
            $batchKey = $destination->id;
            if (!isset($kitchenBatches[$batchKey])) {
                $kitchenBatches[$batchKey] = ['info' => $destination, 'items' => []];
            }
            $kitchenBatches[$batchKey]['items'][] = $item;
        }

        foreach ($kitchenBatches as $batchKey => $batch) {
            $printerInfo = $batch['info'];
            $items       = $batch['items'];
            $ipAddress   = $printerInfo->printnode_id; 

            try {
                $firstItem = $items[0];
                $tableName = $firstItem->order->table->name ?? ('Table: ' . $firstItem->order->table_id);

                // ១. បង្កើត HTML ពី Blade View របស់អ្នក
                $html = \Illuminate\Support\Facades\View::make('pos.kitchen_receipt', compact('printerInfo', 'items', 'tableName'))->render();

                // ២. កំណត់កន្លែងរក្សាទុករូបភាពបណ្តោះអាសន្ន
                $imagePath = storage_path('app/kitchen_receipt_' . uniqid() . '.png');

                // ៣. ថតអេក្រង់ HTML ទៅជា Image ដោយប្រើ Chrome ផ្ទាល់ក្នុងកុំព្យូទ័ររបស់អ្នក (កូដត្រឹមត្រូវគឺ setChromePath)
                \Spatie\Browsershot\Browsershot::html($html)
                    ->setChromePath('C:\Program Files\Google\Chrome\Application\chrome.exe') // 👈 ទីតាំង Chrome របស់អ្នក
                    ->windowSize(512, 800) // ទំហំទទឹងក្រដាស 80mm
                    ->fullPage()           
                    ->save($imagePath);

                // ៤. បញ្ជូនរូបភាពនោះទៅ Print តាម Network
                $connector = new NetworkPrintConnector($ipAddress, 9100, 3);
                $printer = new Printer($connector);

                $image = EscposImage::load($imagePath, false);
                $printer->bitImageColumnFormat($image);
                
                $printer->feed(2);
                $printer->cut();
                $printer->close();

                // ៥. លុបរូបភាពបណ្តោះអាសន្នចេញវិញ
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                // ៦. Update ថាបាន Print រួចរាល់
                foreach ($items as $item) {
                    $item->update(['is_printed' => true]);
                }

            } catch (\Exception $e) {
                Log::error("❌ Print Error: " . $e->getMessage());
            }
        }
    }

    /**
     * 🔥 Function សម្រាប់បំលែងអក្សរខ្មែរទៅជារូបភាព ហើយបញ្ជូនទៅម៉ាស៊ីនព្រីន
     */
    private function printKhmerTextAsImage(Printer $printer, string $text, int $fontSize = 24)
    {
        $fontPath = public_path('fonts/KhmerOSbattambang.ttf');

        if (!file_exists($fontPath)) {
            $printer->text($text . "\n");
            return;
        }

        try {
            $width = 512; 
            
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            $textHeight = abs($bbox[7] - $bbox[1]);
            $height = $textHeight + 16;

            $img = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, $width, $height, $white);

            imagettftext(
                $img,
                $fontSize,
                0,
                5,
                $height - 6,
                $black,
                $fontPath,
                $text
            );

            $tempPath = storage_path('app/khmer_pos_' . uniqid() . '.png');
            imagepng($img, $tempPath);
            imagedestroy($img);

            $escImage = EscposImage::load($tempPath, false);
            $printer->bitImageColumnFormat($escImage);

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

        } catch (\Exception $e) {
            Log::error('KHMER IMAGE PRINT FAIL: ' . $e->getMessage());
            $printer->text($text . "\n");
        }
    }

    public function updateItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'action'  => 'required|in:increase,decrease,remove',
        ]);

        return DB::transaction(function () use ($request) {
            $item = OrderItem::with('addons')->findOrFail($request->item_id);
            
            if ($request->action === 'remove') {
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
                    OrderItemAddon::where('order_item_id', $item->id)->delete();
                    $item->delete();
                }
            }

            $newTotal = $this->recalculateOrderTotal($item->order_id);

            return response()->json([
                'status' => 'success',
                'total'  => $newTotal
            ]);
        });
    }

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
            'source_order_id' => $order->id 
        ]);
    }

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

            $submittedItemIds = collect($request->items)->pluck('id')->filter()->toArray();

            OrderItem::where('order_id', $mainOrder->id)
                     ->whereNotIn('id', $submittedItemIds)
                     ->delete(); 

            $affectedOrderIds = [$mainOrder->id];

            foreach ($request->items as $itemData) {
                $item = OrderItem::find($itemData['id']);
                
                if ($item) {
                    if ($item->order_id != $mainOrder->id) {
                        $affectedOrderIds[] = $item->order_id;
                    }

                    $item->update([
                        'quantity' => $itemData['quantity'],
                        'order_id' => $mainOrder->id 
                    ]);

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

            $otherOrderIds = array_unique(array_diff($affectedOrderIds, [$mainOrder->id]));
            
            foreach ($otherOrderIds as $oldOrderId) {
                $oldOrder = Order::find($oldOrderId);
                if ($oldOrder && $oldOrder->items()->count() == 0) {
                    if ($oldOrder->table_id) {
                        Table::where('id', $oldOrder->table_id)->update(['status' => 'available']);
                    }
                    $oldOrder->delete();
                }
            }

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
                'check_out_time'  => now(), 
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

    public function getOrderDetails($tableId)
    {
        try {
            $order = Order::with(['items.product', 'items.addons.addon', 'table']) 
                ->where('table_id', $tableId)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$order) {
                return response()->json(['status' => 'error', 'message' => 'No active order found'], 404);
            }

            $order->items->transform(function($item) {
                $addonTotal = 0;
                
                if ($item->addons) {
                    foreach ($item->addons as $addon) {
                        $qty = intval($addon->quantity ?? 1); 
                        $price = floatval($addon->price ?? 0);
                        $addonTotal += ($price * $qty);
                    }
                }

                $item->unit_price_calculated = $item->price + $addonTotal;
                $item->total_line_price_calculated = $item->unit_price_calculated * $item->quantity;

                return $item;
            });

            $shop = ShopInfo::first();
            $timezone = 'Asia/Phnom_Penh';
            $dateFormatted = $order->created_at->setTimezone($timezone)->format('d/m/Y h:i A');

            return response()->json([
                'status' => 'success',
                'order'  => $order,
                'items'  => $order->items, 
                'formatted_date' => $dateFormatted,
                'shop' => $shop
            ]);

        } catch (\Exception $e) {
            Log::error("Order Details Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server Error'], 500);
        }
    }

    private function recalculateOrderTotal($orderId)
    {
        $order = Order::with(['items.addons'])->find($orderId); 
        $totalAmount = 0;

        if ($order && $order->items) {
            foreach ($order->items as $item) {
                $itemTotal = $item->price * $item->quantity;
                $addonTotal = 0;

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
            'addon_row_id' => 'required|exists:order_item_addons,id', 
            'action'       => 'required|in:increase,decrease,remove',
        ]);

        return DB::transaction(function () use ($request) {
            $addon = OrderItemAddon::findOrFail($request->addon_row_id);
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
                    $addon->delete();
                }
            }

            $newTotal = $this->recalculateOrderTotal($orderItem->order_id);

            return response()->json([
                'status' => 'success',
                'total'  => $newTotal
            ]);
        });
    }

    public function getBusyTablesForMerge(Request $request)
    {
        $currentTableId = $request->query('current');

        if (!$currentTableId) {
            return response()->json([]);
        }

        $tables = Table::where('status', 'busy')
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
                $mainOrder = Order::where('table_id', $request->main_table_id)
                                  ->where('status', 'pending')
                                  ->first();

                $targetOrder = Order::where('table_id', $request->target_table_id)
                                    ->where('status', 'pending')
                                    ->first();

                if (!$mainOrder) {
                    throw new \Exception("តុបច្ចុប្បន្នគ្មាន Order ដើម្បីបញ្ចូលទេ");
                }
                if (!$targetOrder) {
                    throw new \Exception("តុដែលត្រូវបញ្ចូល (Target) គ្មាន Order ទេ");
                }

                foreach ($targetOrder->items as $item) {
                    $item->update(['order_id' => $mainOrder->id]);
                }

                $targetOrder->delete();
                
                Table::where('id', $request->target_table_id)
                                 ->update(['status' => 'available']);

                $newTotal = $this->recalculateOrderTotal($mainOrder->id);

                return response()->json([
                    'status' => 'success', 
                    'message' => 'បញ្ចូលតុជោគជ័យ!',
                    'new_total' => $newTotal
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Server Error: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    public function splitPayment(Request $request)
    {
        $request->validate([
            'original_order_id' => 'required|exists:orders,id',
            'split_items'       => 'required|array|min:1', 
            'payment_method'    => 'required',
            'received_amount'   => 'required'
        ]);

        return DB::transaction(function () use ($request) {
            $originalOrder = Order::findOrFail($request->original_order_id);

            $splitOrder = Order::create([
                'invoice_number'  => 'SPL-' . time(),
                'user_id'         => Auth::id(),
                'table_id'        => $originalOrder->table_id, 
                'status'          => 'completed', 
                'payment_method'  => $request->payment_method,
                'received_amount' => $request->received_amount,
                'total_amount'    => 0, 
                'paid_at'         => now()
            ]);

            foreach ($request->split_items as $splitItem) {
                $originalItem = OrderItem::with('addons')->find($splitItem['id']);
                
                if (!$originalItem) continue;

                $qtyToSplit = intval($splitItem['qty']);

                if ($qtyToSplit >= $originalItem->quantity) {
                    $originalItem->update(['order_id' => $splitOrder->id]);
                } else {
                    $originalItem->decrement('quantity', $qtyToSplit);

                    $newItem = $originalItem->replicate();
                    $newItem->order_id = $splitOrder->id;
                    $newItem->quantity = $qtyToSplit;
                    $newItem->save();

                    foreach ($originalItem->addons as $addon) {
                        $newAddon = $addon->replicate();
                        $newAddon->order_item_id = $newItem->id;
                        $newAddon->save();
                    }
                }
            }

            $splitTotal = $this->recalculateOrderTotal($splitOrder->id);
            $this->recalculateOrderTotal($originalOrder->id);

            $change = $request->received_amount - $splitTotal;
            $splitOrder->update(['total_amount' => $splitTotal, 'change_amount' => $change]);

            if ($originalOrder->items()->count() == 0) {
                $originalOrder->update(['status' => 'completed']);
                Table::where('id', $originalOrder->table_id)->update(['status' => 'available']);
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

    public function moveTable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_table_id' => 'required',
            'target_table_id'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $targetTable = Table::find($request->target_table_id);
                if (!$targetTable) {
                    throw new \Exception("រកមិនឃើញតុគោលដៅ (ID: {$request->target_table_id})");
                }
                
                if ($targetTable->status !== 'available') {
                    throw new \Exception("តុ {$targetTable->name} មិនទំនេរទេ (Status: {$targetTable->status})");
                }

                $order = Order::where('table_id', $request->current_table_id)
                              ->where('status', 'pending') 
                              ->first();

                if (!$order) {
                    throw new \Exception("តុបច្ចុប្បន្នគ្មានការកម្មង់ទេ (ឬត្រូវបានគិតលុយរួចរាល់)");
                }

                $order->update(['table_id' => $request->target_table_id]);
                Table::where('id', $request->current_table_id)->update(['status' => 'available']);
                $targetTable->update(['status' => 'busy']);

                return response()->json([
                    'status'  => 'success',
                    'message' => "បានប្ដូរទៅតុ {$targetTable->name} ជោគជ័យ!"
                ]);
            });

        } catch (\Exception $e) {
            Log::error('MOVE TABLE ERROR: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'status'  => 'error',
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }
}