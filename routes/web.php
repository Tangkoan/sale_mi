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

use App\Http\Controllers\Admin\ExchangeRateController;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\KitchenDestinationController;

use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\OrderController; // <--- កុំភ្លេច Import
use App\Http\Controllers\Pos\KitchenController; // <--- Import នៅខាងលើ

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
    })->name('admin.dashboard')->middleware('permission:dashboard');

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
            Route::get('/shop-info', [ShopInfoController::class, 'index'])->name('shop_info.index')->middleware('permission:setting-shop_info');
            Route::post('/shop-info/save', [ShopInfoController::class, 'save'])->name('shop_info.save')->middleware('permission:setting-shop_info');
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
            Route::get('/categories', 'index')->name('categories.index')->middleware('permission:category-list');
            Route::get('/categories/fetch', 'fetchCategories')->name('categories.fetch');
            Route::post('/categories', 'store')->name('categories.store')->middleware('permission:category-create');

            // !!! សំខាន់៖ ដាក់ Bulk Delete នៅពីលើ Route {id} !!!
            Route::post('/categories/bulk-delete', 'bulkDelete')->name('categories.bulk_delete')->middleware('permission:category-delete');

            // 2. Route ដែលមាន ID (ដាក់នៅខាងក្រោម)
            // Update (ប្រើ POST ជំនួស PUT សម្រាប់ File Upload)
            Route::post('/categories/{id}', 'update')->name('categories.update')->middleware('permission:category-edit');
            Route::delete('/categories/{id}', 'destroy')->name('categories.destroy')->middleware('permission:category-delete');
        });

        
        // ======================
        // TABLE CRUD
        // ======================
        Route::controller(TableController::class)->group(function () {
            Route::get('/tables', 'index')
                ->name('tables.index')
                ->middleware('permission:table-list');

            Route::get('/tables/fetch', 'fetchTables')
                ->name('tables.fetch')
                ->middleware('permission:table-list');

            Route::post('/tables', 'store')
                ->name('tables.store')
                ->middleware('permission:table-create');

            // Bulk Delete (ដាក់ពីលើ {id})
            Route::post('/tables/bulk-delete', 'bulkDelete')
                ->name('tables.bulk_delete')
                ->middleware('permission:table-delete');

            Route::put('/tables/{id}', 'update')
                ->name('tables.update')
                ->middleware('permission:table-edit');

            Route::delete('/tables/{id}', 'destroy')
                ->name('tables.destroy')
                ->middleware('permission:table-delete');
        });


        // ======================
        // ADDON CRUD
        // ======================
        Route::controller(AddonController::class)->group(function () {
            Route::get('/addons', 'index')->name('addons.index')->middleware('permission:addon-list');
            Route::get('/addons/fetch', 'fetchAddons')->name('addons.fetch')->middleware('permission:addon-list');
            Route::post('/addons', 'store')->name('addons.store')->middleware('permission:addon-create');
            
            // Bulk Delete (ដាក់ពីលើ {id})
            Route::post('/addons/bulk-delete', 'bulkDelete')->name('addons.bulk_delete')->middleware('permission:addon-delete');

            Route::put('/addons/{id}', 'update')->name('addons.update')->middleware('permission:addon-edit');
            Route::delete('/addons/{id}', 'destroy')->name('addons.destroy')->middleware('permission:addon-delete');

            Route::post('addons/{id}/toggle', 'toggleStatus')->name('addons.toggle');
        });

        // ======================
        // PRODUCT CRUD
        // ======================
        Route::controller(ProductController::class)->group(function () {
            Route::get('/products', 'index')->name('products.index')->middleware('permission:product-list');
            Route::get('/products/fetch', 'fetchProducts')->name('products.fetch')->middleware('permission:product-list');
            
            Route::post('/products', 'store')->name('products.store')->middleware('permission:product-create');
            Route::post('/products/bulk-delete', 'bulkDelete')->name('products.bulk_delete')->middleware('permission:product-delete');
            
            // Update (POST method for file upload)
            Route::post('/products/{id}', 'update')->name('products.update')->middleware('permission:product-edit');
            Route::delete('/products/{id}', 'destroy')->name('products.destroy')->middleware('permission:product-delete');
            
            // Toggle Status (Optional: បិទ/បើក Stock)
            Route::post('/products/{id}/toggle', 'toggleStatus')->name('products.toggle')->middleware('permission:product-edit-status');
        });


        // ======================
        // Destinations CRUD
        // ======================
        Route::controller(AddonController::class)->group(function () {
            Route::get('/destinations', [KitchenDestinationController::class, 'index'])->name('destinations.index')->middleware('permission:destinations-list');
            Route::get('/destinations/fetch', [KitchenDestinationController::class, 'fetchDestinations'])->name('destinations.fetch');
            Route::post('/destinations/store', [KitchenDestinationController::class, 'store'])->name('destinations.store')->middleware('permission:destinations-add');
            Route::post('/destinations/{id}', [KitchenDestinationController::class, 'update'])->name('destinations.update')->middleware('permission:destinations-edit'); // For Edit
            Route::post('/destinations/{id}/delete', [KitchenDestinationController::class, 'destroy'])->name('destinations.delete')->middleware('permission:destinations-delete');
            Route::post('/destinations/bulk-delete', [KitchenDestinationController::class, 'bulkDelete'])->name('destinations.bulk_delete')->middleware('permission:destinations-delete');
        });
        
        
        

    });
});

        // ======================
        // POS SYSTEM ROUTES
        // ======================
        Route::middleware(['auth'])->prefix('pos')->name('pos.')->group(function () {
            
            // Table Selection Screen
            Route::get('/tables', [PosController::class, 'index'])->name('tables')->middleware('permission:pos');
            Route::get('/tables/fetch', [PosController::class, 'fetchTables'])->name('tables.fetch');
            
            // Action ពេលចុចលើតុ
            Route::get('/select-table/{id}', [PosController::class, 'selectTable'])->name('select_table');

            // Menu Screen (យើងនឹងធ្វើនៅជំហានបន្ទាប់ តែដាក់ Route ទុកសិនកុំអោយ Error)
            Route::get('/menu/{table_id}', [PosController::class, 'menu'])->name('menu');

            // ======================
            // ORDER ROUTES (បន្ថែមថ្មី)
            // ======================
            Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');
            Route::post('/checkout', [OrderController::class, 'checkout'])->name('pos.order.checkout');
            // 1. API សម្រាប់ទាញយកតុដែលរវល់ (Busy Tables)
            Route::get('/tables/busy-list', [OrderController::class, 'getBusyTablesForMerge']);
            // 2. Route សម្រាប់បញ្ចូលតុ (Merge) - ដែលកំពុង Error
            Route::post('/order/merge', [OrderController::class, 'mergeTables'])->name('order.merge');
            // 3. Route សម្រាប់បំបែកវិក្កយបត្រ (Split)
            Route::post('/order/split', [OrderController::class, 'splitPayment'])->name('order.split');
            Route::get('/order/items-for-merge/{tableId}', [OrderController::class, 'getItemsForMerge']);
    
            // ✅ ត្រូវតែមាន Route នេះដាច់ខាត ទើប Modal ស្គាល់ទិន្នន័យ
            Route::get('/order-details/{table_id}', [PosController::class, 'getOrderDetails'])->name('order.details');
            // Route សម្រាប់ Checkout
            Route::post('/order/checkout', [OrderController::class, 'checkout'])->name('order.checkout');


            Route::post('/order/update-item', [OrderController::class, 'updateItem'])->name('order.update-item');
            Route::post('/order/update-addon', [OrderController::class, 'updateAddon'])->name('order.update-addon');

            // === KITCHEN & BAR ROUTES ===
            // ទំព័រដើមសម្រាប់មើលអេក្រង់
            Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.view')->middleware('permission:pos-kitchen');
            // APIs សម្រាប់ហៅដោយ JavaScript (AJAX)
            Route::get('/kitchen/fetch', [KitchenController::class, 'fetchOrders'])->name('kitchen.fetch');
            Route::post('/kitchen/update-item', [KitchenController::class, 'updateItemStatus'])->name('kitchen.update_item');
            Route::post('/kitchen/done-all', [KitchenController::class, 'markOrderReady'])->name('kitchen.done_all');

            // update status
            Route::get('/products/status', [PosController::class, 'getProductStatuses'])->name('products.status');
            Route::get('/addons/status', [PosController::class, 'getAddonStatuses'])->name('addons.status');
        });

// Exchange Rate
Route::prefix('system/exchange-rate')->name('system.exchange-rate.')->group(function () {
    Route::get('/get', [ExchangeRateController::class, 'getCurrentRate'])->name('get');
    Route::post('/update', [ExchangeRateController::class, 'updateRate'])->name('update');
    Route::get('/fetch-nbc', [ExchangeRateController::class, 'fetchFromNBC'])->name('fetch-nbc');
});

// require __DIR__.'/auth.php'; // បិទចោលសិន កុំអោយជាន់គ្នាជាមួយ Custom Auth របស់យើង