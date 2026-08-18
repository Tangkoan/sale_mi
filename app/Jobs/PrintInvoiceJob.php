<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\ShopInfo;
use App\Models\KitchenDestination;
use Illuminate\Support\Facades\Log;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

class PrintInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    public $paymentDetails;
    public $tries = 3;

    public function __construct($orderId, $paymentDetails)
    {
        $this->orderId = $orderId;
        $this->paymentDetails = $paymentDetails;
    }

    public function handle()
    {
        $printer = null;
        try {
            $order = Order::with(['items.product', 'items.addons.addon', 'table', 'user'])->find($this->orderId);
            if (!$order) return;

            // =========================================================
            // Group ឈ្មោះម្ហូប
            // =========================================================
            $groupedItems = [];
            foreach ($order->items as $item) {
                $noteKey = $item->note ? strtolower(trim($item->note)) : '';
                $rowKey = $item->product_id . '_' . floatval($item->price) . '_' . $noteKey;

                if (array_key_exists($rowKey, $groupedItems)) {
                    $groupedItems[$rowKey]->quantity += $item->quantity;
                    if ($item->addons) {
                        $existingAddons = $groupedItems[$rowKey]->addons;
                        foreach ($item->addons as $incomingAddon) {
                            $incomingId = $incomingAddon->addon_id ?? $incomingAddon->name ?? 'unknown';
                            $found = false;
                            foreach ($existingAddons as $exAddon) {
                                $exId = $exAddon->addon_id ?? $exAddon->name ?? 'unknown';
                                if ($exId == $incomingId) {
                                    $currentQty = floatval($exAddon->quantity ?? 1);
                                    $addQty = floatval($incomingAddon->quantity ?? 1);
                                    $exAddon->quantity = $currentQty + $addQty;
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $existingAddons->push(clone $incomingAddon);
                            }
                        }
                    }
                } else {
                    $clonedItem = clone $item;
                    $clonedAddons = collect();
                    if ($item->addons) {
                        foreach ($item->addons as $addon) {
                            $addonId = $addon->addon_id ?? $addon->name ?? 'unknown';
                            $existing = $clonedAddons->first(function($a) use ($addonId) {
                                $id = $a->addon_id ?? $a->name ?? 'unknown';
                                return $id == $addonId;
                            });

                            if ($existing) {
                                $existing->quantity = floatval($existing->quantity ?? 1) + floatval($addon->quantity ?? 1);
                            } else {
                                $clonedAddons->push(clone $addon);
                            }
                        }
                    }
                    $clonedItem->setRelation('addons', $clonedAddons);
                    $groupedItems[$rowKey] = $clonedItem;
                }
            }

            $order->setRelation('items', collect(array_values($groupedItems)));
            // =========================================================

            $cashierDestination = KitchenDestination::where('name', 'like', '%អ្នកគិតលុយ%')->first();
            if (!$cashierDestination || empty($cashierDestination->printnode_id)) {
                throw new \Exception("រកមិនឃើញ IP សម្រាប់ម៉ាស៊ីនអ្នកគិតលុយទេ!");
            }
            
            $ipAddress = $cashierDestination->printnode_id;
            $shop = ShopInfo::first();
            $paymentDetails = $this->paymentDetails;

            $html = \Illuminate\Support\Facades\View::make('pos.invoice_receipt', compact('order', 'paymentDetails', 'shop'))->render();

            $imagePath = storage_path('app/invoice_' . uniqid() . '.png');
            $chromePath = env('CHROME_PATH', 'C:\Program Files\Google\Chrome\Application\chrome.exe');

            \Spatie\Browsershot\Browsershot::html($html)
                ->setChromePath($chromePath)
                ->windowSize(576, 800)
                ->fullPage()           
                ->save($imagePath);

            $connector = new NetworkPrintConnector($ipAddress, 9100, 3);
            $printer = new Printer($connector);

            $image = EscposImage::load($imagePath, false);
            $printer->bitImageColumnFormat($image);
            
            $printer->feed(2);
            $printer->cut();

            if (file_exists($imagePath)) { unlink($imagePath); }

        } catch (\Exception $e) {
            Log::error("❌ Invoice Print Error: " . $e->getMessage());
            // ចាំបាច់ត្រូវមានបន្ទាត់នេះ
            throw $e; 
        } finally {
            if ($printer !== null) {
                try { $printer->close(); } catch (\Throwable $t) {}
            }
        }
    }
}