<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AssignPermissionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleAssignmentRuleController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ShopInfoController;

use App\Http\Controllers\Admin\CategoryController;

use Illuminate\Support\Facades\Session;


// Route កំណត់ភាសា
        Route::get('/lang/{locale}', function ($locale) {
            // កំណត់ភាសាដែលអនុញ្ញាត (English និង Khmer)
            if (in_array($locale, ['en', 'km'])) {
                Session::put('locale', $locale);
            }
            return redirect()->back(); // ត្រឡប់ទៅទំព័រដើមវិញ
        })->name('switch.language');
// End Route កំណត់ភាសា



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

// ==========================
// AUTH MIDDLEWARE
// ==========================
Route::middleware('auth')->group(function () {

    // ======================
    // Admin Dashboard
    // ======================
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile (Default + Custom)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user', [UserController::class, 'userList'])->name('user.list');

    // ======================
    // All Routes Under /admin
    // ======================
    Route::prefix('admin')->name('admin.')->group(function () {

        // Theme
        Route::view('/theme', 'admin.theme')->name('theme')->middleware('permission:theme-color');
        Route::post('/theme/update', [ThemeController::class, 'update'])->name('theme.update')->middleware('permission:theme-color');

        // User Info
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

        // Change Password
        Route::get('/password', [UserController::class, 'password'])->name('password');
        Route::put('/password', [UserController::class, 'updatePassword'])->name('password.update');

        
        // ======================
        // Shop Info CRUD
        // ======================
        Route::controller(ShopInfoController::class)->group(function () {
            Route::get('/shop-info', [ShopInfoController::class, 'index'])->name('shop_info.index');
            Route::post('/shop-info/save', [ShopInfoController::class, 'save'])->name('shop_info.save');
        });
        
        // ======================
        // USER CRUD
        // ======================
        Route::controller(UserController::class)->group(function () {

            Route::get('/users', 'index')
                ->name('users.index')
                ->middleware('permission:user-list');

            Route::get('/users/fetch', 'fetchUsers')
                ->name('users.fetch')
                ->middleware('permission:user-list');

            Route::post('/users', 'store')
                ->name('users.store')
                ->middleware('permission:user-create');

            Route::put('/users/{id}', 'update')
                ->name('users.update')
                ->middleware('permission:user-edit');

            Route::delete('/users/{id}', 'destroy')
                ->name('users.destroy')
                ->middleware('permission:user-delete');

            Route::post('/users/bulk-delete', 'bulkDestroy')
                ->name('users.bulk_delete')
                ->middleware('permission:user-delete');

            Route::post('/users/bulk-update', 'bulkUpdate')
                ->name('users.bulk_update')
                ->middleware('permission:user-edit');
        });


        // ======================
        // ROLE
        // ======================
        Route::controller(RoleController::class)->group(function () {

            Route::get('/roles', 'index')
                ->name('roles.index')
                ->middleware('permission:role-list');

            Route::get('/roles/fetch', 'fetchRoles')
                ->name('roles.fetch')
                ->middleware('permission:role-list');

            Route::post('/roles', 'store')
                ->name('roles.store')
                ->middleware('permission:role-create');

            Route::put('/roles/{id}', 'update')
                ->name('roles.update')
                ->middleware('permission:role-edit');

            Route::delete('/roles/{id}', 'destroy')
                ->name('roles.destroy')
                ->middleware('permission:role-delete');

            Route::post('/roles/bulk-delete', 'bulkDelete')
                ->name('roles.bulk_delete')
                ->middleware('permission:role-delete');
        });


        // ======================
        // PERMISSION ASSIGN TO ROLE
        // ======================
        Route::controller(AssignPermissionController::class)->group(function () {

            Route::get('/assign-permissions/{roleId}', 'fetchRolePermissions')
                ->name('assign_permissions.fetch')
                ->middleware('permission:role-list');

            Route::post('/assign-permissions', 'update')
                ->name('assign_permissions.update')
                ->middleware('permission:role-assign');
        });


        // ======================
        // PERMISSION CRUD
        // ======================
        Route::controller(PermissionController::class)->group(function () {

            Route::get('/permissions', 'index')
                ->name('permissions.index')
                ->middleware('permission:permission-list');

            Route::get('/permissions/fetch', 'fetchPermissions')
                ->name('permissions.fetch')
                ->middleware('permission:permission-list');

            Route::post('/permissions', 'store')
                ->name('permissions.store')
                ->middleware('permission:permission-create');

            Route::put('/permissions/{id}', 'update')
                ->name('permissions.update')
                ->middleware('permission:permission-edit');

            Route::delete('/permissions/{id}', 'destroy')
                ->name('permissions.destroy')
                ->middleware('permission:permission-delete');

            Route::post('/permissions/bulk-delete', 'bulkDelete')
                ->name('permissions.bulk_delete')
                ->middleware('permission:permission-delete');
        });


        // ======================
        // ROLE ASSIGNMENT RULE (SUPER ADMIN ONLY)
        // ======================
        // ប្រើ Permission ជំនួស Role ដើម្បីឱ្យ Admin ចូលបានដែរ (បើគាត់មានសិទ្ធិ)
        Route::middleware(['auth', 'can:rule-list'])->group(function () {
            Route::resource('rules', RoleAssignmentRuleController::class)
                ->only(['index', 'edit', 'update']);
        });



        // ប្រើ Permission: activity-list ដើម្បីចូលមើល
        Route::middleware(['auth', 'can:activity-list'])->group(function () {
            // 1. ទំព័រដើម (View)
            Route::get('/activity-logs', [ActivityLogController::class, 'index'])
                ->name('activity_logs.index');

            // 2. API សម្រាប់ទាញទិន្នន័យ (Ajax)
            Route::get('/activity-logs/fetch', [ActivityLogController::class, 'fetchLogs'])
                ->name('activity_logs.fetch');

            // 3. API លុប (ត្រូវការ Permission: activity-delete)
            Route::middleware(['can:activity-delete'])->group(function() {
                Route::delete('/activity-logs/{id}', [ActivityLogController::class, 'destroy'])
                    ->name('activity_logs.destroy');
                    
                Route::post('/activity-logs/bulk-delete', [ActivityLogController::class, 'bulkDelete'])
                    ->name('activity_logs.bulk_delete');
            });
        });

        // ======================
        // CATEGORY CRUD
        // ======================
        Route::controller(CategoryController::class)->group(function () { 
            // 1. Route ធម្មតា និង Bulk Action (ដាក់នៅពីលើគេ)
            Route::get('/categories', 'index')->name('categories.index');
            Route::get('/categories/fetch', 'fetchCategories')->name('categories.fetch');
            Route::post('/categories', 'store')->name('categories.store');

            // !!! សំខាន់៖ ដាក់ Bulk Delete នៅពីលើ Route {id} !!!
            Route::post('/categories/bulk-delete', 'bulkDelete')->name('categories.bulk_delete');

            // 2. Route ដែលមាន ID (ដាក់នៅខាងក្រោម)
            // Update (ប្រើ POST ជំនួស PUT សម្រាប់ File Upload)
            Route::post('/categories/{id}', 'update')->name('categories.update');
            Route::delete('/categories/{id}', 'destroy')->name('categories.destroy');
        });

        
        

    });
});



// require __DIR__.'/auth.php'; // បិទចោលសិន កុំអោយជាន់គ្នាជាមួយ Custom Auth របស់យើង