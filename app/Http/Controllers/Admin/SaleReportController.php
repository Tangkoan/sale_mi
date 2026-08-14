<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; 
use Barryvdh\DomPDF\Facade\Pdf;      
use App\Exports\SaleReportExport;    

class SaleReportController extends Controller
{
    public function index()
    {
        return view('admin.report.sale_report.index');
    }

    /**
     * 1. Shared Function: សម្រាប់ទាញទិន្នន័យតាម Filter
     */
    private function getFilteredData(Request $request)
    {
        $filterType = $request->filter_type ?? 'day';
        
        // Load relationships
        $query = Order::query()->with(['items.product', 'items.addons.addon']); 
        
        $query->where('status', 'completed')
              ->orderBy('created_at', 'desc');

        // --- Filter Logic ---
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

        // --- Summary Calculation (គិតជាលុយរៀលសុទ្ធ) ---
        $totalSalesKhr = $orders->sum('total_amount');
        $cashKhr = $orders->where('payment_method', 'cash')->sum('total_amount');
        $qrKhr = $orders->where('payment_method', 'qr')->sum('total_amount');

        $summary = [
            'total_sales_khr' => $totalSalesKhr,
            'total_orders'    => $orders->count(),
            'cash_khr'        => $cashKhr,
            'qr_khr'          => $qrKhr,
        ];

        return [
            'orders'  => $orders,
            'summary' => $summary
        ];
    }

    /**
     * 2. API សម្រាប់ Frontend (Fetch Data)
     */
    public function fetchSaleData(Request $request)
    {
        try {
            $data = $this->getFilteredData($request);
            $orders = $data['orders'];
            $summary = $data['summary'];

            $tableData = $orders->map(function ($order) {
                return [
                    'invoice' => $order->invoice_number,
                    'date'    => $order->created_at ? $order->created_at->format('d-M-Y h:i A') : '',
                    'payment' => ucfirst($order->payment_method ?? ''),
                    'status'  => ucfirst($order->status ?? ''),
                    // Format ជាលុយរៀល (គ្មានកន្ទុយលេខសូន្យ)
                    'total_khr' => number_format($order->total_amount, 0), 
                    
                    'items' => $order->items->map(function($item) {
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
                            'name'      => $productName, 
                            'qty'       => $item->quantity,
                            'price'     => number_format($item->price, 0), // តម្លៃរាយជាលុយរៀល
                            'total_khr' => number_format($finalLineTotal, 0), // តម្លៃសរុបជាលុយរៀល
                        ];
                    }),
                ];
            });

            return response()->json([
                'status'   => 'success',
                'summary'  => $summary,
                'orders'   => $tableData,
                'currency' => 'KHR'
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 3. Export Excel & PDF រក្សាទុកដដែល
     */
    public function exportExcel(Request $request) 
    {
        $data = $this->getFilteredData($request);
        return Excel::download(new SaleReportExport($data['orders'], $data['summary']), 'sale_report.xlsx');
    }

    public function exportPDF(Request $request)
    {
        $data = $this->getFilteredData($request);

        if ($data['orders']->isEmpty()) {
            return back()->with('error', 'មិនមានទិន្នន័យសម្រាប់ Export ទេ'); 
        }

        try {
            // ១. បម្លែង Blade ទៅជាកូដ HTML សិន
            $html = view('admin.report.sale_report.export_pdf', [
                'orders'  => $data['orders'],
                'summary' => $data['summary']
            ])->render();

            // ២. កំណត់ Configuration របស់ mPDF
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            // ៣. បង្កើត mPDF និងបញ្ជាក់ប្រាប់ពីទីតាំង Font ខ្មែរ
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L', // ក្រដាស A4 ផ្តេក (Landscape)
                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts'), // ចង្អុលទៅកាន់ Folder /public/fonts របស់អ្នកដោយផ្ទាល់
                ]),
                'fontdata' => $fontData + [
                    'battambang' => [
                        'R' => 'KhmerOSbattambang.ttf', // ឈ្មោះ File Font របស់អ្នក
                        'useOTL' => 0xFF, // ចំណុចសំខាន់បំផុត! បើកមុខងារនេះដើម្បីអោយជើងអក្សរខ្មែរតម្រឹមបានស្អាត
                    ]
                ],
                'default_font' => 'battambang' // កំណត់ Font នេះជា Font គោល
            ]);

            // ៤. បញ្ចូល HTML ទៅក្នុង PDF
            $mpdf->WriteHTML($html);

            // ៥. ទាញយក PDF
            return response($mpdf->Output("Sale_Report_" . now()->format('Y-m-d') . ".pdf", 'D'))
                   ->header('Content-Type', 'application/pdf');
                
        } catch (\Exception $e) {
            return back()->with('error', 'មានបញ្ហាបច្ចេកទេសក្នុងការបង្កើត PDF: ' . $e->getMessage());
        }
    }
}