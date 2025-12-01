<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\RoleController;

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
    
    // Theme
    Route::post('/admin/theme/update', [ThemeController::class, 'update'])->name('admin.theme.update');


    // User Info
    Route::get('/profile', [UserController::class, 'profile'])->name('admin.profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('admin.profile.update');

    // Change Password
    Route::get('/password', [UserController::class, 'password'])->name('admin.password');
    Route::put('/password', [UserController::class, 'updatePassword'])->name('admin.password.update');

    // User CRUD
    // User Management Routes
    Route::controller(UserController::class)->group(function () {
        Route::get('/admin/users', 'index')->name('admin.users.index');
        Route::get('/admin/users/fetch', 'fetchUsers')->name('admin.users.fetch'); // Ajax Load
        Route::post('/admin/users', 'store')->name('admin.users.store');
        Route::put('/admin/users/{id}', 'update')->name('admin.users.update');
        Route::delete('/admin/users/{id}', 'destroy')->name('admin.users.destroy');
    });

    // Role Management
    Route::get('/admin/roles', [RoleController::class, 'index'])->name('admin.roles.index');
    Route::get('/admin/roles/fetch', [RoleController::class, 'fetchRoles'])->name('admin.roles.fetch');
    Route::post('/admin/roles', [RoleController::class, 'store'])->name('admin.roles.store');
    Route::put('/admin/roles/{id}', [RoleController::class, 'update'])->name('admin.roles.update');
    Route::delete('/admin/roles/{id}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');

});

// require __DIR__.'/auth.php'; // បិទចោលសិន កុំអោយជាន់គ្នាជាមួយ Custom Auth របស់យើង