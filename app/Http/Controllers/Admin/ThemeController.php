<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|array',
        ]);

        $user = auth()->user();
        $user->theme_settings = $request->theme; // Save JSON ទាំងអស់
        $user->save();

        return response()->json(['message' => 'Theme updated successfully!']);
    }
}