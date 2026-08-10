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
    // ប្ដូរ Parameter ទៅជា $id វិញ
    public function unblock($id)
    {
        // ១. ស្វែងរកទិន្នន័យក្នុង DB តាម ID
        $blocked = BlockedIp::find($id);

        if ($blocked) {
            // ២. បំបែកយក Session ID (ព្រោះយើង Save ទុកជា "IP|SessionID")
            $parts = explode('|', $blocked->ip_address);
            $sessionId = isset($parts[1]) ? $parts[1] : '';

            // ៣. លុបចេញពី Cache របស់ Laravel ដើម្បីអោយអាច Login វិញបានភ្លាមៗ!
            if ($sessionId) {
                RateLimiter::clear('pin_login_' . $sessionId);
            }

            // ៤. លុបចេញពី Database
            $blocked->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'បានដោះ Block ដោយជោគជ័យ អ្នកអាច Login បានវិញហើយ!'
        ]);
    }
}