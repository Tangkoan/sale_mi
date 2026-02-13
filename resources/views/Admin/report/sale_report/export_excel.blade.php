<table>
    <thead>
    <tr>
        <th colspan="5" style="font-weight: bold; text-align: center;">Sale Report</th>
    </tr>
    <tr>
        <th>Invoice</th>
        <th>Date</th>
        <th>Payment</th>
        <th>Status</th>
        <th>Total ($)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($orders as $order)
        <tr>
            <td>#{{ $order->invoice_number }}</td>
            <td>{{ $order->created_at->format('d-M-Y H:i') }}</td>
            <td>{{ ucfirst($order->payment_method) }}</td>
            <td>{{ ucfirst($order->status) }}</td>
            <td>{{ number_format($order->total_amount, 2) }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="4" style="text-align: right; font-weight: bold;">Grand Total:</td>
        <td style="font-weight: bold;">${{ number_format($summary['total_sales_usd'], 2) }}</td>
    </tr>
    </tbody>
</table>