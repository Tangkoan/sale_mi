<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

class PrintKitchenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    public $tries = 3; // សាកល្បងព្រីន ៣ ដង បើបរាជ័យ

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $itemsToPrint = OrderItem::with([
                'product.category.kitchenDestination', 
                'addons.addon',
                'order.table' 
            ])
            ->where('order_id', $this->orderId)
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
            
            $printer = null;

            try {
                $firstItem = $items[0];
                $tableName = $firstItem->order->table->name ?? ('Table: ' . $firstItem->order->table_id);

                $html = \Illuminate\Support\Facades\View::make('pos.kitchen_receipt', compact('printerInfo', 'items', 'tableName'))->render();
                $imagePath = storage_path('app/kitchen_receipt_' . uniqid() . '.png');
                $chromePath = env('CHROME_PATH', 'C:\Program Files\Google\Chrome\Application\chrome.exe');

                \Spatie\Browsershot\Browsershot::html($html)
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

                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                foreach ($items as $item) {
                    $item->update(['is_printed' => true]);
                }

            } catch (\Exception $e) {
                Log::error("❌ Kitchen Print Error: " . $e->getMessage());
                // ចាំបាច់ត្រូវមានបន្ទាត់នេះ ដើម្បីប្រាប់ Laravel អោយដឹងថាព្រីនបរាជ័យ អោយទុកក្នុង Queue សិន
                throw $e; 
            } finally {
                if ($printer !== null) {
                    try { $printer->close(); } catch (\Throwable $t) {}
                }
            }
        }
    }
}