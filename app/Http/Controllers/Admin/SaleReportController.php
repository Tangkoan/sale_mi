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
            
            // 🔥 Load ទាំង Product និង Addons (ជាមួយឈ្មោះ Addon)
            // 'items.addons.addon' គឺហៅតាម function ដែលយើងបង្កើតក្នុង Model OrderItemAddon
            $query = Order::query()->with(['items.product', 'items.addons.addon']); 

            $query->where('status', 'completed')
                  ->orderBy('created_at', 'desc');

            // --- Filter Logic (រក្សាទុកដដែល) ---
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

            // --- Summary Logic (រក្សាទុកដដែល) ---
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

            // 🔥 Mapping Data សម្រាប់ Table និង Modal
            $tableData = $orders->map(function ($order) use ($exchangeRate) {
                return [
                    'invoice' => $order->invoice_number,
                    'date' => $order->created_at->format('d-M-Y h:i A'),
                    'payment' => ucfirst($order->payment_method),
                    'status' => ucfirst($order->status),
                    'total_usd' => number_format($order->total_amount, 2),
                    'total_khr' => number_format($order->total_amount * $exchangeRate),
                    
                    // 🔥 Logic សម្រាប់ Items + Addons
                    'items' => $order->items->map(function($item) use ($exchangeRate) {
                        
                        // ១. ឈ្មោះ Product
                        $productName = $item->product ? $item->product->name : 'Unknown Product';
                        
                        // ២. គណនា Addons
                        $addonsDisplay = [];
                        $addonsTotalCost = 0;

                        if ($item->addons) {
                            foreach ($item->addons as $addonItem) {
                                // រកឈ្មោះ Addon (ពី Table Master)
                                $addonName = $addonItem->addon ? $addonItem->addon->name : 'Addon'; 
                                
                                // 🔥 បង្ហាញឈ្មោះ និង ចំនួន (បើលើសពី ១)
                                // ឧទាហរណ៍៖ "Pearl (x2)"
                                if ($addonItem->quantity > 1) {
                                    $addonName .= ' (x' . $addonItem->quantity . ')';
                                }
                                
                                $addonsDisplay[] = $addonName;

                                // បូកតម្លៃ Addons (Price * Qty)
                                $addonsTotalCost += ($addonItem->price * $addonItem->quantity);
                            }
                        }

                        // ៣. បូកបញ្ចូលឈ្មោះ Addon ទៅក្នុងឈ្មោះ Product
                        // លទ្ធផល៖ "Coffee Latte + Pearl (x2), Jelly"
                        if (!empty($addonsDisplay)) {
                            $productName .= ' + ' . implode(', ', $addonsDisplay);
                        }

                        // ៤. គណនាតម្លៃសរុបចុងក្រោយ (Product + Addons)
                        // តម្លៃដើម Product * ចំនួន Product
                        $productTotalCost = $item->price * $item->quantity;
                        
                        // តម្លៃសរុប = តម្លៃ Product + តម្លៃ Addons
                        $finalLineTotal = $productTotalCost + $addonsTotalCost;

                        return [
                            'name' => $productName, 
                            'qty' => $item->quantity, // ចំនួន Product
                            'price' => number_format($item->price, 2), // តម្លៃ Unit របស់ Product
                            'total_usd' => number_format($finalLineTotal, 2), // តម្លៃសរុបរួមបញ្ចូល Addons
                            'total_khr' => number_format($finalLineTotal * $exchangeRate),
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