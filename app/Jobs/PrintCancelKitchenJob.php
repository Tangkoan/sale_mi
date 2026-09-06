<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Throwable;

class PrintCancelKitchenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cancelData;
    public $tries = 100;
    public $backoff = 3;

    public function __construct($cancelData)
    {
        // ទទួលយកទិន្នន័យម្ហូបដែលលុប (ឈ្មោះម្ហូប, ចំនួន, តុ, IP ចង្ក្រាន)
        $this->cancelData = $cancelData;
    }

    public function handle()
    {
        $ipAddress = $this->cancelData['printer_ip'];
        $printer = null;
        $imagePath = null;

        try {
            ini_set('default_socket_timeout', 1);
            $connector = new NetworkPrintConnector($ipAddress, 9100, 1);
            $printer = new Printer($connector);

            $cancelData = $this->cancelData;
            $html = View::make('pos.cancel_receipt', compact('cancelData'))->render();
            
            $imagePath = storage_path('app/cancel_receipt_' . Str::uuid()->toString() . '.png');
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

            sleep(1);

        } catch (Throwable $e) {
            Log::warning("⚠️ រំលង Printer ខូច/រវល់ (IP: {$ipAddress}) ពេលព្រីន Cancel");
            return $this->release(3);
        } finally {
            ini_set('default_socket_timeout', 60);
            if ($printer !== null) {
                try { $printer->close(); } catch (Throwable $t) {}
            }
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }
}