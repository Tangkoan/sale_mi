<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Exception;

class PrintReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order_id;

    // កំណត់ថាបើព្រីនអត់ចេញ សាកល្បង ៣ ដង
    public $tries = 3; 

    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    public function handle()
    {
        // បន្ថែមកូដនេះមួយជួរ
        \Log::info('✅ ប្រព័ន្ធ Queue ចាប់ផ្តើមធ្វើការព្រីន សម្រាប់ Order ID: ' . $this->order_id);

        $order = Order::find($this->order_id);
        if(!$order) return;

        try {
            // កូដបញ្ជាម៉ាស៊ីនព្រីន...
        } catch (\Exception $e) {
            \Log::error('❌ ព្រីនមិនចេញទេ Error: ' . $e->getMessage());
            throw $e;
        }
    }
}