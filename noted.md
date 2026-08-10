*** How To See Your Reponsive

២. ឆែកមើលប្រភពតភ្ជាប់ (Remote URL)
git remote -v

៣. ឆែកមើលប្រវត្តិការងារ (Commit History)
git log

៤. ឆែកមើល Branch ដែលកំពុងឈរ
git branch

៥. ឆែកមើលការកំណត់ផ្សេងៗ (Config)
git config --list



Route : admin/blocked-ips (សម្រាប់ដោះស្រាយ Block IP)


របៀបដំណើរ Export PDF សម្រាប៉អក្សរខ្មែរបាន
ជំហានទី ១៖ ដំឡើង PHP Packages តាមរយៈ Composer
    composer require spatie/laravel-pdf
    composer require spatie/browsershot

ជំហានទី ២៖ ដំឡើង Puppeteer (Google Chrome) តាមរយៈ NPM
    npm install
    npm install puppeteer

ជំហានទី ៣៖ ការសរសេរកូដនៅក្នុង Controller

    use Illuminate\Http\Request;
    use Spatie\LaravelPdf\Facades\Pdf; // កុំភ្លេច use Facade នេះនៅខាងលើ

    public function exportPDF(Request $request)
    {
        // ១. ទាញយកទិន្នន័យរបស់អ្នក
        $data = $this->getFilteredData($request);

        if ($data['orders']->isEmpty()) {
            return back()->with('error', 'មិនមានទិន្នន័យសម្រាប់ Export ទេ'); 
        }

        // ២. បង្កើត និងទាញយក PDF
        try {
            return Pdf::view('admin.report.sale_report.export_pdf', [
                    'orders' => $data['orders'],
                    'summary' => $data['summary']
                ])
                ->format('a4')       // កំណត់ទំហំក្រដាស (a4, a3, letter...)
                ->landscape()        // កំណត់ជាក្រដាសផ្តេក (បើចង់បានបញ្ឈរ សូមលុបបន្ទាត់នេះចោល ឬដាក់ portrait())
                ->download("Sale_Report_" . now()->format('Y-m-d') . ".pdf");
                
        } catch (\Exception $e) {
            // ចាប់ Error បង្ហាញប្រាប់បើមានបញ្ហា (ឧទាហរណ៍៖ អត់ទាន់ Run npm install)
            return back()->with('error', 'មានបញ្ហាក្នុងការបង្កើត PDF: ' . $e->getMessage());
        }
    }






ជំហានទី ៤៖ ការរៀបចំ View (Blade) និង ការដាក់ Font ខ្មែរ
    export_pdf.blade.php

    <!DOCTYPE html>
    <html lang="km">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sale Report PDF</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
        
        <style>
            body { 
                /* កំណត់ Font ខ្មែរជាគោល */
                font-family: 'Battambang', sans-serif; 
                font-size: 12px; 
                color: #333;
                margin: 0;
                padding: 20px;
            }
            
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-right { text-align: right; }
        </style>
    </head>
    <body>
        </body>
    </html>













