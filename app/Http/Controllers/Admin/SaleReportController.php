<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order; 

class SaleReportController extends Controller
{
    public function index()
    {
        return view('admin.report.sale_report.index');
    }

    public function fetchSaleData(Request $request)
    {
        try {
            $filterType = $request->filter_type ?? 'day';
            
            // 🔥 កែចំណុចទី ១: ថែម .product នៅពីក្រោយ items ដើម្បីយកឈ្មោះទំនិញ
            $query = Order::query()->with('items.product'); 

            $query->where('status', 'completed')
                  ->orderBy('created_at', 'desc');

            // --- Filter Logic (នៅដដែល) ---
            if ($filterType == 'day') {
                $start = $request->start_date ?? Carbon::now()->format('Y-m-d');
                $end = $request->end_date ?? Carbon::now()->format('Y-m-d');
                $query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
            } elseif ($filterType == 'month') {
                $startInput = $request->start_month ?? Carbon::now()->format('Y-m');
                $endInput = $request->end_month ?? Carbon::now()->format('Y-m');
                $start = Carbon::parse($startInput)->startOfMonth();
                $end = Carbon::parse($endInput)->endOfMonth();
                $query->whereBetween('created_at', [$start, $end]);
            } elseif ($filterType == 'year') {
                $start = $request->start_year ?? Carbon::now()->year;
                $end = $request->end_year ?? Carbon::now()->year;
                $query->whereYear('created_at', '>=', $start)->whereYear('created_at', '<=', $end);
            }

            $orders = $query->get();
            $exchangeRate = 4100;

            // --- Summary Logic (នៅដដែល) ---
            $totalSalesUsd = $orders->sum('total_amount');
            $cashUsd = $orders->where('payment_method', 'cash')->sum('total_amount');
            $qrUsd = $orders->where('payment_method', 'qr')->sum('total_amount');

            $summary = [
                'total_sales_usd' => $totalSalesUsd,
                'total_sales_khr' => $totalSalesUsd * $exchangeRate,
                'total_orders' => $orders->count(),
                'cash_usd' => $cashUsd,
                'cash_khr' => $cashUsd * $exchangeRate,
                'qr_usd' => $qrUsd,
                'qr_khr' => $qrUsd * $exchangeRate,
            ];

            // 🔥 កែចំណុចទី ២: Mapping Items អោយត្រូវនឹង Database
            $tableData = $orders->map(function ($order) use ($exchangeRate) {
                return [
                    'invoice' => $order->invoice_number,
                    'date' => $order->created_at->format('d-M-Y h:i A'),
                    'payment' => ucfirst($order->payment_method),
                    'status' => ucfirst($order->status),
                    'total_usd' => number_format($order->total_amount, 2),
                    'total_khr' => number_format($order->total_amount * $exchangeRate),
                    
                    // 🔥 Logic ទាញយក Items
                    'items' => $order->items->map(function($item) use ($exchangeRate) {
                        
                        // ពិនិត្យមើលថាតើមាន Product ឬអត់ (ការពារ Error បើ Product ត្រូវលុប)
                        $productName = $item->product ? $item->product->name : 'Unknown Product'; // បងអាចប្តូរ 'name' ទៅតាម column ក្នុង table products (ឧ. product_name)
                        
                        // គណនាតម្លៃសរុប (Price * Quantity)
                        $itemTotal = $item->price * $item->quantity;

                        return [
                            'name' => $productName, 
                            'qty' => $item->quantity, // ឈ្មោះ column ក្នុង DB បងគឺ quantity
                            'price' => number_format($item->price, 2),
                            'total_usd' => number_format($itemTotal, 2),
                            'total_khr' => number_format($itemTotal * $exchangeRate),
                        ];
                    }),
                ];
            });

            return response()->json([
                'status' => 'success',
                'summary' => $summary,
                'orders' => $tableData,
                'currency' => app()->getLocale() == 'km' ? 'KHR' : 'USD'
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}