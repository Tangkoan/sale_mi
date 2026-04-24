<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class BlockedIpController extends Controller
{
    // បង្ហាញ UI
    public function index()
    {
        // លុបទិន្នន័យចាស់ៗដែលផុតកំណត់ (Expired) ស្វ័យប្រវត្តិដើម្បីកុំឲ្យពេញតារាង
        BlockedIp::where('expires_at', '<', now())->delete();
        
        return view('admin.blocked_ips.index');
    }

    // ទាញទិន្នន័យជា JSON សម្រាប់តារាង
    public function fetch(Request $request)
    {
        $ips = BlockedIp::latest()->paginate(10);
        return response()->json($ips);
    }

    // មុខងារដោះ Block
    public function unblock($ip_address)
    {
        // ១. លុបចេញពី Database
        BlockedIp::where('ip_address', $ip_address)->delete();

        // ២. លុបចេញពី Cache (RateLimiter)
        RateLimiter::clear('pin_login_' . $ip_address);

        return response()->json([
            'status' => 'success',
            'message' => 'ដោះ Block សម្រាប់ IP: ' . $ip_address . ' បានជោគជ័យ!'
        ]);
    }
}