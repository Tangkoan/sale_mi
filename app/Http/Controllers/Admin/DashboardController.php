<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. Setup Filter Variables
        $filter = $request->filter ?? 'day';
        $dateInput = $request->date ?? Carbon::now()->format('Y-m-d');
        $monthInput = $request->month ?? Carbon::now()->format('Y-m');
        $yearInput = $request->year ?? Carbon::now()->format('Y');

        // Determine Date Range
        if ($filter == 'day') {
            $startPeriod = Carbon::parse($dateInput)->startOfDay();
            $endPeriod = Carbon::parse($dateInput)->endOfDay();
            $prevStart = Carbon::parse($dateInput)->subDay()->startOfDay();
            $prevEnd = Carbon::parse($dateInput)->subDay()->endOfDay();
            $comparisonText = Carbon::parse($dateInput)->format('D, d M Y');
        } elseif ($filter == 'month') {
            $startPeriod = Carbon::parse($monthInput)->startOfMonth();
            $endPeriod = Carbon::parse($monthInput)->endOfMonth();
            $prevStart = Carbon::parse($monthInput)->subMonth()->startOfMonth();
            $prevEnd = Carbon::parse($monthInput)->subMonth()->endOfMonth();
            $comparisonText = Carbon::parse($monthInput)->format('F Y');
        } else { // year
            $startPeriod = Carbon::createFromDate($yearInput, 1, 1)->startOfYear();
            $endPeriod = Carbon::createFromDate($yearInput, 12, 31)->endOfYear();
            $prevStart = Carbon::createFromDate($yearInput - 1, 1, 1)->startOfYear();
            $prevEnd = Carbon::createFromDate($yearInput - 1, 12, 31)->endOfYear();
            $comparisonText = "Year " . $yearInput;
        }

        // 2. Query Data
        $currentOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startPeriod, $endPeriod])
            ->get();

        $prevSales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total_amount');

        // 3. Calculate Metrics
        $totalSales = $currentOrders->sum('total_amount');
        $totalOrders = $currentOrders->count();
        $totalCash = $currentOrders->where('payment_method', 'cash')->sum('total_amount');
        $totalQR = $currentOrders->where('payment_method', 'qr')->sum('total_amount');

        // Growth %
        $growth = 0;
        if ($prevSales > 0) $growth = (($totalSales - $prevSales) / $prevSales) * 100;
        elseif ($totalSales > 0) $growth = 100;

        // 4. Chart Data
        $chartLabels = [];
        $chartData = [];

        if ($filter == 'day') {
            for ($i = 0; $i <= 23; $i++) {
                $chartLabels[] = sprintf('%02d:00', $i);
                $chartData[] = $currentOrders->filter(fn($o) => $o->created_at->hour == $i)->sum('total_amount');
            }
        } elseif ($filter == 'month') {
            $daysInMonth = Carbon::parse($startPeriod)->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $chartLabels[] = $i;
                $chartData[] = $currentOrders->filter(fn($o) => $o->created_at->day == $i)->sum('total_amount');
            }
        } else {
            for ($i = 1; $i <= 12; $i++) {
                $chartLabels[] = Carbon::create()->month($i)->format('M');
                $chartData[] = $currentOrders->filter(fn($o) => $o->created_at->month == $i)->sum('total_amount');
            }
        }

        // 5. Top Products Logic
        $topProducts = OrderItem::select('product_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(price * quantity) as total_revenue'))
            ->whereHas('order', function($q) use ($startPeriod, $endPeriod) {
                $q->where('status', 'completed')->whereBetween('created_at', [$startPeriod, $endPeriod]);
            })
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
            
        $maxProductSales = $topProducts->max('total_revenue') ?? 1;

        // ================= Check AJAX Request =================
        if ($request->ajax()) {
            // Render HTML for Top Products specifically to send back
            $topProductsHtml = view('admin.dashboard.partials.top_products', compact('topProducts', 'maxProductSales'))->render();

            return response()->json([
                'totalSales' => number_format($totalSales, 2),
                'totalOrders' => number_format($totalOrders),
                'totalCash' => number_format($totalCash, 2),
                'totalQR' => number_format($totalQR, 2),
                'cashPercent' => $totalSales > 0 ? ($totalCash/$totalSales)*100 : 0,
                'qrPercent' => $totalSales > 0 ? ($totalQR/$totalSales)*100 : 0,
                'comparisonText' => $comparisonText,
                'growth' => number_format($growth, 1),
                'chartLabels' => $chartLabels,
                'chartData' => $chartData,
                'topProductsHtml' => $topProductsHtml
            ]);
        }

        return view('admin.dashboard.index', compact(
            'totalSales', 'totalOrders', 'totalCash', 'totalQR', 'topProducts', 
            'maxProductSales', 'filter', 'dateInput', 'monthInput', 'yearInput', 
            'chartLabels', 'chartData', 'comparisonText', 'growth'
        ));
    }
}