<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    
    <style>
    body { 
        /* គ្រាន់តែហៅឈ្មោះ Font ដែលយើងបានកំណត់ក្នុង Controller ជាការស្រេច */
        font-family: 'battambang', sans-serif; 
        font-size: 12px; 
        color: #333;
        line-height: 1.5;
    }
    
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    .text-right { text-align: right; }
    .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ __('messages.sale_report') }}</h2>
    </div>

    <div style="margin-bottom: 20px;">
        <strong>{{ __('messages.total_sales') }}:</strong> {{ number_format($summary['total_sales_khr'], 0) }} ៛ | 
        <strong>{{ __('messages.total_orders') }}:</strong> {{ $summary['total_orders'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('messages.invoice') }}</th>
                <th>{{ __('messages.date') }}</th>
                <th>{{ __('messages.payment') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th class="text-right">{{ __('messages.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>#{{ $order->invoice_number }}</td>
                <td>{{ $order->created_at->format('d-M-Y H:i') }}</td>
                <td>{{ ucfirst($order->payment_method) }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td class="text-right">{{ number_format($order->total_amount, 0) }} ៛</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>{{ __('messages.grand_total') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($summary['total_sales_khr'], 0) }} ៛</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>