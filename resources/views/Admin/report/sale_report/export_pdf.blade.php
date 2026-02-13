<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sale Report</h2>
    </div>

    <div style="margin-bottom: 20px;">
        <strong>Total Sales:</strong> ${{ number_format($summary['total_sales_usd'], 2) }} | 
        <strong>Total Orders:</strong> {{ $summary['total_orders'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Payment</th>
                <th>Status</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>#{{ $order->invoice_number }}</td>
                <td>{{ $order->created_at->format('d-M-Y H:i') }}</td>
                <td>{{ ucfirst($order->payment_method) }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Grand Total</strong></td>
                <td class="text-right"><strong>${{ number_format($summary['total_sales_usd'], 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>