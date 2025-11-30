<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ThemeController;

/*
|--------------------------------------------------------------------------
| ផ្នែកទី ១: Route សម្រាប់អ្នកមិនទាន់ Login (Guest)
|--------------------------------------------------------------------------
*/

// ១. បើចូលតាម Link ទទេ (/) អោយរុញទៅ Login ភ្លាម
Route::get('/', function () {
    return redirect()->route('login');
});

// អនុញ្ញាតអោយចូលបានតែ ៥ ដងប៉ុណ្ណោះក្នុង ១ នាទី (60s)
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    // បង្ហាញ Login Form
    // សំខាន់៖ ត្រូវតែដាក់ name('login') ដើម្បីអោយ Middleware 'auth' ស្គាល់កន្លែងដែលត្រូវរុញមកពេលគេមិនទាន់ Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    // Post ទិន្នន័យ Login
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

/*
|--------------------------------------------------------------------------
| ផ្នែកទី ២: Route សម្រាប់អ្នក Login ជាប់ហើយ (Auth)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // ១. Admin Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // ២. Logout Route (សំខាន់ណាស់)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ៣. User Profile (Default របស់ Laravel Breeze - ទុកក៏បាន លុបក៏បាន)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user', [userController::class, 'userList'])->name('user.list');

    Route::get('/admin/theme', function () {
        return view('admin.theme');
    })->name('admin.theme');
    
    Route::post('/admin/theme/update', [ThemeController::class, 'update'])->name('admin.theme.update');
});

// require __DIR__.'/auth.php'; // បិទចោលសិន កុំអោយជាន់គ្នាជាមួយ Custom Auth របស់យើង