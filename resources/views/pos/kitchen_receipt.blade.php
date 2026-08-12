<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <title>Kitchen Receipt</title>
    <style>
        /* ១. កំណត់ Font ខ្មែរ */
        @font-face {
            font-family: 'KhmerFont';
            src: url('{{ public_path("fonts/KhmerOSbattambang.ttf") }}') format('truetype');
        }

        /* ២. កំណត់ទំហំក្រដាស និងទម្រង់ទូទៅ */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0 auto; 
            padding: 10px; 
            -webkit-print-color-adjust: exact; 
            width: 100%;
            max-width: 512px; /* កំណត់ប្រវែងអតិបរមាសម្រាប់ម៉ាស៊ីនព្រីន */
            font-family: 'KhmerFont', sans-serif;
            background: #ffffff;
            color: #000000;
            font-size: 26px; 
            font-weight: bold; 
            line-height: 1.4;
        }
        
        /* ថ្នាក់សម្រាប់អក្សរ */
        .text-heavy { font-weight: 900; }
        .text-center { text-align: center; }
        .text-uppercase { text-transform: uppercase; }
        
        /* ៣. បន្ទាត់ខណ្ឌចែក (Dashed Line) */
        .divider {
            border-top: 3px dashed #000;
            margin: 15px 0;
        }

        /* ៤. ផ្នែកខាងលើ (Header & Meta) */
        .header h1 {
            font-size: 40px;
            margin: 0 0 10px 0;
            font-weight: 900;
            -webkit-text-stroke: 1px black;
        }
        
        .meta-info {
            font-size: 26px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .meta-time {
            font-size: 22px;
            margin-bottom: 10px;
        }

        /* ៥. តារាងបញ្ជីមុខម្ហូប (Items Table) */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding: 12px 0;
            border-bottom: 2px dotted #555;
        }
        tr:last-child td {
            border-bottom: none;
        }

        /* រំលេចចំនួនមុខម្ហូបឱ្យច្បាស់សម្រាប់ចុងភៅ */
        .col-qty {
            width: 18%;
            text-align: left;
        }
        .qty-badge {
            font-size: 34px;
            font-weight: 900;
            border: 2px solid #000;
            padding: 2px 8px;
            border-radius: 5px;
            display: inline-block;
        }

        .col-item {
            width: 82%;
            font-size: 30px; 
        }

        /* ៦. ផ្នែកចំណាំ (Note) & ជម្រើសបន្ថែម (Addons) */
        .note {
            font-size: 24px;
            color: #000;
            margin-top: 8px;
            padding: 4px 0 4px 10px;
            border-left: 4px solid #000; /* បន្ថែមបន្ទាត់ដើម្បីឱ្យចំណាំលេចធ្លោ */
            font-style: italic;
        }
        .addon {
            font-size: 24px;
            color: #000;
            margin-top: 6px;
            padding-left: 10px;
        }

        /* ៧. ផ្នែកខាងក្រោមបង្អស់ (Footer) */
        .footer {
            text-align: center;
            font-size: 24px;
            margin-top: 15px;
            font-weight: 900;
        }
    </style>
</head>
<body>

    <!-- ចំណងជើង -->
    <div class="header text-center">
        <h1 class="text-uppercase">{{ $printerInfo->name ?? 'ORDER' }}</h1>
    </div>

    <!-- ព័ត៌មានតុ និងម៉ោង -->
    <div class="meta-info">
        <div>តុ (Table): <span class="text-heavy" style="font-size: 30px;">{{ $tableName }}</span></div>
    </div>
    <div class="meta-time">
        ម៉ោង (Time): {{ now()->format('d/m/Y h:i A') }}
    </div>

    <div class="divider"></div>

    <!-- បញ្ជីមុខម្ហូប -->
    <table>
        @foreach($items as $item)
            <tr>
                <td class="col-qty">
                    <span class="qty-badge">{{ $item->quantity }}</span>
                </td>
                <td class="col-item">
                    <div class="text-heavy">{{ $item->product->name ?? 'Unknown' }}</div>
                    
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
    
    <!-- ផ្នែកខាងក្រោមបង្អស់ -->
    <div class="footer">
        *** សូមរៀបចំមុខម្ហូបខាងលើ ***
    </div>

</body>
</html>