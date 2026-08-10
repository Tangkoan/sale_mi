<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SaleReportExport implements FromView, ShouldAutoSize
{
    protected $orders;
    protected $summary;

    public function __construct($orders, $summary)
    {
        $this->orders = $orders;
        $this->summary = $summary;
    }

    public function view(): View
    {
        return view('admin.report.sale_report.export_excel', [
            'orders' => $this->orders,
            'summary' => $this->summary
        ]);
    }
}