<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateController extends Controller
{
    // 1. យកតម្លៃពី Database មកប្រើ
    public function getCurrentRate()
    {
        $rate = ExchangeRate::where('currency_code', 'KHR')->first();
        return response()->json([
            'rate' => $rate ? $rate->rate : 4100
        ]);
    }

    // 2. Update តម្លៃចូល Database
    public function updateRate(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|min:1'
        ]);

        $rate = ExchangeRate::updateOrCreate(
            ['currency_code' => 'KHR'],
            ['rate' => $request->rate, 'is_active' => true]
        );

        return response()->json([
            'status' => 'success', 
            'message' => 'Exchange rate updated successfully', 
            'rate' => $rate->rate
        ]);
    }

    // 3. ហៅ API ពី NBC/MEF (Fix SSL Error)
    public function fetchFromNBC()
    {
        try {
            // ✅ Fix: ប្រើ withoutVerifying() ដើម្បីកុំអោយជាប់ SSL Error លើ Localhost
            // ✅ Fix: ថែម User-Agent ដើម្បីកុំអោយ Server គេ Block
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept'     => 'application/json',
                ])
                ->timeout(10) // រង់ចាំអតិបរមា 10 វិនាទី
                ->get('https://data.mef.gov.kh/api/v1/realtime-api/exchange-rate?currency_id=USD');

            if ($response->successful()) {
                return $response->json();
            }

            // បើ API Error, Log មើលថាមានបញ្ហាអី
            Log::error('NBC API Error: ' . $response->body());

            return response()->json([
                'status' => 'error', 
                'message' => 'API returned status: ' . $response->status()
            ], 500);

        } catch (\Exception $e) {
            // Log Error ជាក់លាក់ (ដូចជា cURL error)
            Log::error('NBC Connection Exception: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage() // បង្ហាញ Error ផ្ទាល់មក Frontend
            ], 500);
        }
    }
}