<style>
    @media print {
        body * { visibility: hidden; height: 0; overflow: hidden; }
        #receipt-print-area {
            display: block !important; visibility: visible !important;
            position: absolute; left: 0; top: 0; width: 80mm;
            margin: 0 auto; padding: 0; height: auto !important;
            background-color: white !important; color: black !important;
        }
        #receipt-print-area * { visibility: visible !important; height: auto !important; }
        @page { margin: 0; size: auto; }
    }
</style>