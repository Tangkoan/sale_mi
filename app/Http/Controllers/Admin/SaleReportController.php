<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; // ត្រូវការដំឡើង package maatwebsite/excel
use Barryvdh\DomPDF\Facade\Pdf;      // ត្រូវការដំឡើង package barryvdh/laravel-dompdf
use App\Exports\SaleReportExport;    // ត្រូវការបង្កើត Class Export នេះ (បើប្រើ Excel)


class SaleReportController extends Controller
{
    public function index()
    {
        return view('admin.report.sale_report.index');
    }

    /**
     * 1. Shared Function: សម្រាប់ទាញទិន្នន័យតាម Filter (ប្រើរួមគ្នាគ្រប់កន្លែង)
     */
    private function getFilteredData(Request $request)
    {
        $filterType = $request->filter_type ?? 'day';
        
        // Load relationships ដែលចាំបាច់
        $query = Order::query()->with(['items.product', 'items.addons.addon']); 
        
        $query->where('status', 'completed')
              ->orderBy('created_at', 'desc');

        // --- Filter Logic ---
        if ($filterType == 'day') {
            $start = $request->start_date ?? Carbon::now()->format('Y-m-d');
            $end = $request->end_date ?? Carbon::now()->format('Y-m-d');
            // ប្រើ whereDate ដើម្បីកុំអោយខ្វល់ពីម៉ោង
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

        // --- Summary Calculation ---
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

        return [
            'orders' => $orders,
            'summary' => $summary,
            'exchangeRate' => $exchangeRate
        ];
    }

    /**
     * 2. API សម្រាប់ Frontend (Fetch Data)
     */
    public function fetchSaleData(Request $request)
    {
        try {
            // ហៅទិន្នន័យពី Shared Function
            $data = $this->getFilteredData($request);
            $orders = $data['orders'];
            $summary = $data['summary'];
            $exchangeRate = $data['exchangeRate'];

            // Map Data ទៅជា JSON Format សម្រាប់ JavaScript
            $tableData = $orders->map(function ($order) use ($exchangeRate) {
                return [
                    'invoice' => $order->invoice_number,
                    // Format Date ពី PHP តែម្ដង ដើម្បីកុំអោយ JS ពិបាក
                    'date' => $order->created_at ? $order->created_at->format('d-M-Y h:i A') : '',
                    'payment' => ucfirst($order->payment_method ?? ''),
                    'status' => ucfirst($order->status ?? ''),
                    'total_usd' => number_format($order->total_amount, 2),
                    'total_khr' => number_format($order->total_amount * $exchangeRate),
                    
                    // Logic Items + Addons (កូដដើមរបស់អ្នក)
                    'items' => $order->items->map(function($item) use ($exchangeRate) {
                        $productName = $item->product ? $item->product->name : 'Unknown';
                        $addonsDisplay = [];
                        $addonsTotalCost = 0;

                        if ($item->addons) {
                            foreach ($item->addons as $addonItem) {
                                $addonName = $addonItem->addon ? $addonItem->addon->name : 'Addon'; 
                                if ($addonItem->quantity > 1) {
                                    $addonName .= ' (x' . $addonItem->quantity . ')';
                                }
                                $addonsDisplay[] = $addonName;
                                $addonsTotalCost += ($addonItem->price * $addonItem->quantity);
                            }
                        }

                        if (!empty($addonsDisplay)) {
                            $productName .= ' + ' . implode(', ', $addonsDisplay);
                        }

                        $productTotalCost = $item->price * $item->quantity;
                        $finalLineTotal = $productTotalCost + $addonsTotalCost;

                        return [
                            'name' => $productName, 
                            'qty' => $item->quantity,
                            'price' => number_format($item->price, 2),
                            'total_usd' => number_format($finalLineTotal, 2),
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

    /**
     * 3. Export Excel
     */
    public function exportExcel(Request $request) 
    {
        $data = $this->getFilteredData($request);
        // អ្នកត្រូវបង្កើត SaleReportExport class (សូមមើលខាងក្រោមបើមិនទាន់មាន)
        return Excel::download(new SaleReportExport($data['orders'], $data['summary']), 'sale_report.xlsx');
    }

 
public function exportPDF(Request $request)
{
    // ១. ទាញយកទិន្នន័យ
    $data = $this->getFilteredData($request);

    // ប្រសិនបើចង់ឆែកថាមិនមានទិន្នន័យ
    if ($data['orders']->isEmpty()) {
        return back()->with('error', 'មិនមានទិន្នន័យសម្រាប់ Export ទេ'); 
    }

    // ២. បង្កើត PDF ជា Landscape (ផ្តេក)
    try {
        // ប្រើប្រាស់វិធីសាស្រ្តរបស់ Spatie ផ្ទាល់តែម្ដង មិនបាច់ប្រើ Stream ទេ
        return \Spatie\LaravelPdf\Facades\Pdf::view('admin.report.sale_report.export_pdf', [
                'orders' => $data['orders'],
                'summary' => $data['summary']
            ])
            ->format('a4')
            ->landscape()
            ->download("Sale_Report_" . now()->format('Y-m-d') . ".pdf");
            
    } catch (\Exception $e) {
        // ឥឡូវនេះ បើមាន Error វានឹងលោតមកទីនេះ ហើយប្រាប់អ្នកថាខុសអ្វីពិតប្រាកដ
        return back()->with('error', 'មានបញ្ហាបច្ចេកទេសក្នុងការបង្កើត PDF: ' . $e->getMessage());
    }
}
}