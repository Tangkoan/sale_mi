<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Jobs\PrintInvoiceJob;
use Illuminate\Support\Facades\DB;

class OrderHistoryController extends Controller
{
    public function index()
    {
        return view('admin.order_history.order_list');
    }

    public function fetchOrders(Request $request)
    {
        // ទាញយក Order រួមជាមួយឈ្មោះតុ 
        $query = Order::with('table')->where('status', 'completed');

        // 1. លក្ខខណ្ឌស្វែងរកតាម លេខវិក្កយបត្រ (Invoice Number)
        if ($request->keyword) {
            $query->where('invoice_number', 'like', '\%' .$request->keyword . '%');
        }

        // 2. លក្ខខណ្ឌ Filter តាមថ្ងៃ (Date)
        if ($request->date) {
            $query->whereDate('created_at',$request->date);
        }

        $sortBy  = $request->input('sort_by', 'created_at');$sortDir = $request->input('sort_dir', 'desc');$query->orderBy($sortBy,$sortDir);

        $perPage =$request->input('per_page', 10);
        $orders = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($orders);
    }

    // ទាញយកទិន្នន័យលម្អិតសម្រាប់បង្ហាញក្នុង Modal
    public function getOrderDetails($id)
    {
        $order = Order::with(['items.product', 'items.addons.addon', 'table', 'user'])
                      ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'order'  => $order,
            'items'  => $order->items
        ]);
    }

    // មុខងារបញ្ជាព្រីនឡើងវិញ
    public function reprintInvoice($id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // រៀបចំ Payment Details
            $paymentDetails = [
                'received_amount' => $order->received_amount,
                'payment_method'  => $order->payment_method,
                'change_amount'   => $order->change_amount,
            ];

            // បញ្ជាទៅកាន់ Job ដើម្បី Print Invoice ម្ដងទៀត
            PrintInvoiceJob::dispatch($order->id,$paymentDetails);

            return response()->json(['status' => 'success', 'message' => 'វិក្កយបត្រត្រូវបានបញ្ជូនទៅម៉ាស៊ីនព្រីនដោយជោគជ័យ!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'មានបញ្ហាក្នុងការព្រីន: ' . $e->getMessage()], 500);
        }
    }
}