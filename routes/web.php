<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AssignPermissionController;
use App\Http\Controllers\Admin\PermissionController;
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
        // 1. មើលបញ្ជី User (View)
        // ដាក់ទាំង index និង fetch ព្រោះវាជាការមើលទិន្នន័យដូចគ្នា
        Route::get('/admin/users', 'index')
            ->name('admin.users.index')
            ->middleware('permission:user-list');
        Route::get('/admin/users/fetch', 'fetchUsers')
            ->name('admin.users.fetch')
            ->middleware('permission:user-list'); // សំខាន់! ត្រូវការពារ Ajax ផង
        // 2. បង្កើត User ថ្មី (Create)
        Route::post('/admin/users', 'store')
            ->name('admin.users.store')
            ->middleware('permission:user-create');
        // 3. កែប្រែ User (Edit/Update)
        Route::put('/admin/users/{id}', 'update')
            ->name('admin.users.update')
            ->middleware('permission:user-edit');
        // 4. លុប User (Delete)
        Route::delete('/admin/users/{id}', 'destroy')
            ->name('admin.users.destroy')
            ->middleware('permission:user-delete');

        Route::post('/admin/users/bulk-delete', 'bulkDestroy')
        ->name('admin.users.bulk_delete')
        ->middleware('permission:user-delete');

        Route::post('/admin/users/bulk-update', 'bulkUpdate')
        ->name('admin.users.bulk_update')
        ->middleware('permission:user-edit');

    });

    // ====================================================
    // 1. ROLE MANAGEMENT (គ្រប់គ្រង Role)
    // ====================================================
    Route::controller(RoleController::class)->group(function () {
        
        // មើលបញ្ជី Role (ត្រូវការសិទ្ធិ role-list)
        Route::get('/admin/roles', 'index')
            ->name('admin.roles.index')
            ->middleware('permission:role-list');

        Route::get('/admin/roles/fetch', 'fetchRoles')
            ->name('admin.roles.fetch')
            ->middleware('permission:role-list'); // ការពារ Ajax

        // បង្កើត Role ថ្មី (ត្រូវការសិទ្ធិ role-create)
        Route::post('/admin/roles', 'store')
            ->name('admin.roles.store')
            ->middleware('permission:role-create');

        // កែប្រែ Role (ត្រូវការសិទ្ធិ role-edit)
        Route::put('/admin/roles/{id}', 'update')
            ->name('admin.roles.update')
            ->middleware('permission:role-edit');

        // លុប Role (ត្រូវការសិទ្ធិ role-delete)
        Route::delete('/admin/roles/{id}', 'destroy')
            ->name('admin.roles.destroy')
            ->middleware('permission:role-delete');

        // Role Routes
        
        Route::post('/admin/roles/bulk-delete','bulkDelete')
            ->name('admin.roles.bulk_delete')
            ->middleware('permission:role-delete');

        
        
    });


    // ====================================================
    // 2. ASSIGN PERMISSIONS (ដាក់សិទ្ធិឱ្យ Role)
    // ====================================================
    Route::controller(AssignPermissionController::class)->group(function () {
        
        // ទាញយក Permission មកបង្ហាញ (ត្រូវការសិទ្ធិ role-list ឬ role-edit)
        Route::get('/admin/assign-permissions/{roleId}', 'fetchRolePermissions')
            ->name('admin.assign_permissions.fetch')
            ->middleware('permission:role-list');

        // Save សិទ្ធិចូល Database (ត្រូវការសិទ្ធិ role-edit ឬបង្កើតសិទ្ធិថ្មី assign-permission)
        Route::post('/admin/assign-permissions', 'update')
            ->name('admin.assign_permissions.update')
            ->middleware('permission:role-edit'); 
    });


    // ====================================================
    // 3. PERMISSION MANAGEMENT (គ្រប់គ្រង Permission ឆៅ)
    // *ណែនាំ៖ គួរដាក់ឱ្យតែ Super Admin ប្រើប៉ុណ្ណោះ*
    // ====================================================
    Route::controller(PermissionController::class)->group(function () {
        
        // មើលបញ្ជី (ប្រើ role:Super Admin ដើម្បីសុវត្ថិភាពខ្ពស់)
        Route::get('/admin/permissions', 'index')
            ->name('admin.permissions.index')
            ->middleware('permission:permission-list');

        Route::get('/admin/permissions/fetch', 'fetchPermissions')
            ->name('admin.permissions.fetch')
            ->middleware('permission:permission-list');

        // បង្កើត/កែ/លុប Permission
        Route::post('/admin/permissions', 'store')
            ->name('admin.permissions.store')
            ->middleware('permission:permission-create');

        Route::put('/admin/permissions/{id}', 'update')
            ->name('admin.permissions.update')
            ->middleware('permission:permission-edit');

        Route::delete('/admin/permissions/{id}', 'destroy')
            ->name('admin.permissions.destroy')
            ->middleware('permission:permission-delete');

        Route::post('admin/permissions/bulk-delete',  'bulkDelete')
            ->name('admin.permissions.bulk_delete')
            ->middleware('permission:permission-delete');
    });
});

// require __DIR__.'/auth.php'; // បិទចោលសិន កុំអោយជាន់គ្នាជាមួយ Custom Auth របស់យើង