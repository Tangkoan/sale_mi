<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopInfo;
use Illuminate\Support\Facades\Storage;

class ShopInfoController extends Controller
{
    public function index()
    {
        // ទាញយកទិន្នន័យ Row ដំបូងគេ (ព្រោះយើងមានតែមួយ)
        $shop = ShopInfo::first();
        // បញ្ជូនទៅ View (បើ $shop គ្មាន វានឹងស្មើ null)
        return view('admin.shop_info.form', compact('shop'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'shop_en' => 'required|string|max:255',
            'logo'    => 'nullable|image|max:2048',
            'fav'     => 'nullable|image|max:1024',
        ]);

        // រកមើលទិន្នន័យចាស់
        $shop = ShopInfo::first();

        // ប្រសិនបើមិនទាន់មានទិន្នន័យសោះ យើងបង្កើត Object ថ្មី
        if (!$shop) {
            $shop = new ShopInfo();
        }

        // ទទួលយកទិន្នន័យពី Form
        $shop->shop_en = $request->shop_en;
        $shop->shop_kh = $request->shop_kh;
        $shop->description_en = $request->description_en;
        $shop->description_kh = $request->description_kh;
        $shop->address_en = $request->address_en;
        $shop->address_kh = $request->address_kh;
        $shop->phone_number = $request->phone_number;
        $shop->note_kh = $request->note_kh;
        $shop->status = $request->status ?? 1;

        // Upload Logo
        if ($request->hasFile('logo')) {
            // លុបរូបចាស់ចោល បើមាន
            if ($shop->logo && Storage::disk('public')->exists($shop->logo)) {
                Storage::disk('public')->delete($shop->logo);
            }
            $shop->logo = $request->file('logo')->store('uploads/shops/logos', 'public');
        }

        // Upload Favicon
        if ($request->hasFile('fav')) {
            if ($shop->fav && Storage::disk('public')->exists($shop->fav)) {
                Storage::disk('public')->delete($shop->fav);
            }
            $shop->fav = $request->file('fav')->store('uploads/shops/favs', 'public');
        }

        $shop->save();

        return response()->json(['message' => 'Shop information saved successfully!']);
    }
}