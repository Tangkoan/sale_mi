<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <style>
        /* ១. កំណត់ Font ខ្មែរ */
        @font-face {
            font-family: 'KhmerFont';
            src: url('{{ public_path("fonts/KhmerOSbattambang.ttf") }}') format('truetype');
        }

        /* ២. កំណត់ទំហំក្រដាស និងទម្រង់ទូទៅ */
        body {
            font-family: 'KhmerFont', sans-serif;
            width: 512px;
            margin: 0;
            padding: 10px 15px;
            background: #ffffff;
            color: #000000;
            font-size: 28px; /* ដំឡើងទំហំអក្សរបន្តិច */
            font-weight: bold; /* ធ្វើឱ្យអក្សរទាំងអស់ដិត (Bold) */
            line-height: 1.4;
        }
        
        /* ថ្នាក់សម្រាប់ធ្វើឱ្យអក្សរកាន់តែដិតខ្លាំង (Extra Bold) */
        .text-heavy {
            font-weight: 900;
            -webkit-text-stroke: 0.5px black; /* បន្ថែមកម្រាស់សាច់អក្សរ */
        }

        .text-center { text-align: center; }
        
        /* ៣. បន្ទាត់ខណ្ឌចែក (Dashed Line) ឱ្យក្រាស់ជាងមុន */
        .divider {
            border-top: 3px dashed #000;
            margin: 15px 0;
        }

        /* ៤. ផ្នែកខាងលើ (Header) */
        .header h1 {
            font-size: 40px;
            margin: 0 0 5px 0;
            font-weight: 900;
            -webkit-text-stroke: 1px black; /* ដិតខ្លាំងបំផុតសម្រាប់ចំណងជើង */
            text-transform: uppercase;
        }
        
        .meta-info {
            font-size: 26px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }

        /* ៥. តារាងបញ្ជីមុខម្ហូប (Items Table) */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding: 10px 0;
            border-bottom: 2px dotted #555; /* បន្ទាត់ពុះចែកច្បាស់ជាងមុន */
        }
        tr:last-child td {
            border-bottom: none;
        }

        .col-qty {
            width: 15%;
            font-size: 34px; /* លេខធំច្បាស់ */
            font-weight: 900;
            -webkit-text-stroke: 0.5px black;
        }
        .col-item {
            width: 85%;
            font-size: 30px; /* ឈ្មោះម្ហូបធំជាងមុន */
        }

        /* ៦. ផ្នែកចំណាំ (Note) & ជម្រើសបន្ថែម (Addons) */
        .note {
            font-size: 24px;
            color: #000;
            margin-top: 5px;
            padding-left: 10px;
            font-weight: bold; 
        }
        .addon {
            font-size: 24px;
            color: #000;
            margin-top: 5px;
            padding-left: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- ចំណងជើង -->
    <div class="header text-center">
        <h1>{{ $printerInfo->name ?? 'Order' }}</h1>
    </div>

    <!-- ព័ត៌មានតុ និងម៉ោង -->
    <div class="meta-info">
        <div>តុ (Table): <span class="text-heavy">{{ $tableName }}</span></div>
    </div>
    <div style="font-size: 24px; margin-bottom: 10px;">
        ម៉ោង: {{ date('d/m/Y H:i A') }}
    </div>

    <div class="divider"></div>

    <!-- បញ្ជីមុខម្ហូប -->
    <table>
        @foreach($items as $item)
        <tr>
            <td class="col-qty">{{ $item->quantity }} x</td>
            <td class="col-item">
                <span class="text-heavy">{{ $item->product->name ?? 'Unknown' }}</span>
                
                @if($item->note)
                    <div class="note">📝 ចំណាំ: {{ $item->note }}</div>
                @endif

                @if($item->addons && $item->addons->count() > 0)
                    @foreach($item->addons as $addonRow)
                        <div class="addon">➕ {{ $addonRow->addon->name ?? 'Extra' }} (x{{ $addonRow->quantity }})</div>
                    @endforeach
                @endif
            </td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>
    
    <!-- ផ្នែកខាងក្រោមបង្អស់ (Footer) -->
    <div class="text-center" style="font-size: 24px; margin-top: 15px;">
        *** សូមរៀបចំមុខម្ហូបខាងលើ ***
    </div>

</body>
</html>