<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str; 

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Exception;
use Throwable;

class PrintKitchenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    
    // បង្កើន Tries ដើម្បីឲ្យវាអាចវិលឆែក ៣វិនាទីម្ដង បានរហូតដល់ផុតកំណត់១០នាទី
    public $tries = 200; 
    public $backoff = 3; 

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function retryUntil()
    {
        return now()->addMinutes(10); 
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

        $hasError = false;

        foreach ($kitchenBatches as $batchKey => $batch) {
            $printerInfo = $batch['info'];
            $items       = $batch['items'];
            $ipAddress   = $printerInfo->printnode_id; 
            
            $printer = null;
            $imagePath = null;

            try {
                // ចំណុចឆ្លាតវៃទី១៖ បង្ខំឲ្យការភ្ជាប់កាត់ផ្ដាច់ត្រឹម ១វិនាទី (ការពារមិនឲ្យគាំង Queue ដល់ ២១វិនាទី)
                ini_set('default_socket_timeout', 1);

                // ចំណុចឆ្លាតវៃទី២៖ សាកភ្ជាប់ទៅ Printer មុនគេបង្អស់ បើដាច់ Network វានឹង Error លោតរំលងភ្លាមៗ មិនខាតពេលបើក Chrome គូររូប
                $connector = new NetworkPrintConnector($ipAddress, 9100, 1);
                $printer = new Printer($connector);

                // ភ្ជាប់ជោគជ័យ ទើបអនុញ្ញាតឲ្យធ្វើការ Render រូបភាព
                $firstItem = $items[0];
                $tableName = $firstItem->order->table->name ?? ('Table: ' . $firstItem->order->table_id);

                $html = View::make('pos.kitchen_receipt', compact('printerInfo', 'items', 'tableName'))->render();
                $imagePath = storage_path('app/kitchen_receipt_' . Str::uuid()->toString() . '.png');
                $chromePath = env('CHROME_PATH', 'C:\Program Files\Google\Chrome\Application\chrome.exe');

                Browsershot::html($html)
                    ->setChromePath($chromePath)
                    ->windowSize(576, 100)
                    ->fullPage()          
                    ->save($imagePath);

                $image = EscposImage::load($imagePath, false);
                $printer->bitImageColumnFormat($image);
                $printer->feed(1);
                $printer->cut();

                foreach ($items as $item) {
                    $item->update(['is_printed' => true]);
                }
                
                sleep(1); 

            } catch (Throwable $e) {
                // ប្រសិនបើម៉ាស៊ីនណាដាច់ វាចូលមកទីនេះ ហើយរំលងទៅព្រីនម៉ាស៊ីនបន្ទាប់ដែលកំពុងដើរ ក្នុងល្បឿនផ្លេកបន្ទោរ
                Log::warning("⚠️ រំលង Printer ខូច/រវល់ (IP: {$ipAddress}) - ទៅព្រីនម៉ាស៊ីនបន្ទាប់...");
                $hasError = true;
                continue; 
            } finally {
                // បង្វិល Timeout មកដើមវិញ ដើម្បីកុំឲ្យប៉ះពាល់ការងារផ្សេងទៀតរបស់ Laravel
                ini_set('default_socket_timeout', 60);
                
                if ($printer !== null) {
                    try { $printer->close(); } catch (Throwable $t) {}
                }
                if ($imagePath && file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        if ($hasError) {
            // ចំណុចឆ្លាតវៃទី៣៖ ម៉ាស៊ីនខូច នឹងត្រូវយកទៅតម្រង់ជួររង់ចាំ ៣វិនាទីសិន ទើបសាកម្ដងទៀត
            return $this->release(3);
        }
    }
}