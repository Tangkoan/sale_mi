<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

class PrintKitchenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    
    // បើ Print មិនចេញ ឲ្យព្យាយាមម្ដងទៀត ៥ ដង
    public $tries = 5; 
    
    // សម្រាក ៣០ វិនាទី មុននឹងព្យាយាម Print ម្ដងទៀត (ទុកពេលឲ្យគេដាក់ក្រដាស)
    public $backoff = 30; 

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $itemsToPrint = OrderItem::with([
                'product.category.kitchenDestination', 
                'addons.addon.kitchenDestination', 
                'order.table' 
            ])
            ->where('order_id', $this->orderId)
            ->where('is_printed', false)
            ->get();

        if ($itemsToPrint->isEmpty()) { return; }

        $kitchenBatches = [];
        foreach ($itemsToPrint as $item) {
            $isWrapperProduct = stripos($item->product?->name, 'extra') !== false;

            if ($isWrapperProduct && $item->addons->isNotEmpty()) {
                foreach ($item->addons as $addon) {
                    $destination = $addon->addon?->kitchenDestination;
                    if (!$destination) {
                        $destination = $item->product?->category?->kitchenDestination;
                    }

                    if (!$destination || !$destination->is_active) { continue; }
                    
                    $batchKey = $destination->id;
                    if (!isset($kitchenBatches[$batchKey])) {
                        $kitchenBatches[$batchKey] = ['info' => $destination, 'items' => []];
                    }

                    $fakeItem = clone $item;
                    $fakeItem->quantity = floatval($item->quantity) * floatval($addon->quantity ?? 1);
                    
                    $fakeProduct = clone $item->product;
                    $fakeProduct->name = $addon->addon->name ?? $addon->name ?? 'Addon'; 
                    
                    $fakeItem->setRelation('product', $fakeProduct);
                    $fakeItem->setRelation('addons', collect()); 
                    
                    $kitchenBatches[$batchKey]['items'][] = $fakeItem;
                }
            } else {
                $destination = $item->product?->category?->kitchenDestination;
                if (!$destination || !$destination->is_active) { continue; }
                
                $batchKey = $destination->id;
                if (!isset($kitchenBatches[$batchKey])) {
                    $kitchenBatches[$batchKey] = ['info' => $destination, 'items' => []];
                }
                $kitchenBatches[$batchKey]['items'][] = $item;
            }
        }

        foreach ($kitchenBatches as $batchKey => $batch) {
            $printerInfo = $batch['info'];
            $items       = $batch['items'];
            $ipAddress   = $printerInfo->printnode_id; 

            try {
                $firstItem = $items[0];
                $tableName = $firstItem->order->table->name ?? ('Table: ' . $firstItem->order->table_id);

                $html = View::make('pos.kitchen_receipt', compact('printerInfo', 'items', 'tableName'))->render();
                $imagePath = storage_path('app/kitchen_receipt_' . uniqid() . '.png');
                $chromePath = env('CHROME_PATH', 'C:\Program Files\Google\Chrome\Application\chrome.exe');

                Browsershot::html($html)
                    ->setChromePath($chromePath)
                    ->windowSize(576, 100)
                    ->fullPage()           
                    ->save($imagePath);

                $connector = new NetworkPrintConnector($ipAddress, 9100, 3);
                $printer = new Printer($connector);

                $image = EscposImage::load($imagePath, false);
                $printer->bitImageColumnFormat($image);
                
                $printer->feed(1);
                $printer->cut();
                $printer->close();

                if (file_exists($imagePath)) { unlink($imagePath); }

                foreach ($items as $item) {
                    $item->update(['is_printed' => true]);
                }
            } catch (\Exception $e) {
                Log::error("❌ Print Error (Kitchen): " . $e->getMessage());
                // ចំណុចសំខាន់៖ បោះ Error បន្តដើម្បីឲ្យ Laravel យកវាទៅ Retry ម្ដងទៀត
                throw $e; 
            }
        }
    }
}