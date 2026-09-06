<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Kantumruy Pro', sans-serif; width: 576px; margin: 0; padding: 10px; color: black; background: white; }
        .header { text-align: center; border-bottom: 2px dashed black; padding-bottom: 10px; margin-bottom: 10px; }
        .cancel-badge { background: black; color: white; padding: 5px 15px; font-size: 26px; font-weight: bold; border-radius: 5px; display: inline-block; }
        .table-name { font-size: 30px; font-weight: 900; margin: 10px 0; }
        .item-row { display: flex; justify-content: space-between; font-size: 28px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .qty { font-size: 32px; font-weight: 900; border: 3px solid black; padding: 2px 15px; border-radius: 8px; }
        .reason { font-size: 22px; font-weight: bold; text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="cancel-badge">❌ ឈប់ធ្វើ (VOID) ❌</div>
        <div class="table-name">តុ: {{ $cancelData['table_name'] }}</div>
        <div style="font-size: 20px;">ម៉ោង: {{ now()->format('H:i d/m/Y') }}</div>
    </div>

    <div class="item-row">
        <div>{{ $cancelData['product_name'] }}</div>
        <div class="qty">- {{ $cancelData['cancel_qty'] }}</div>
    </div>

    <div class="reason">
        មូលហេតុ: {{ $cancelData['reason'] }}
    </div>
</body>
</html>