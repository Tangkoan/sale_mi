<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

// Import to user permission
use Illuminate\Support\Facades\Gate;

// Import សម្រាប់ហៅ Logo និង Name Shop ដែលប្រើគ្រប់ Page
use Illuminate\Support\Facades\View; // <--- បន្ថែមបន្ទាត់នេះជាចាំបាច់
use App\Models\ShopInfo; // ហៅ Model របស់អ្នកមក (ឈ្មោះអាចខុសគ្នាទៅតាមអ្វីដែលអ្នកបង្កើត)


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(125);


        // អនុញ្ញាតឱ្យ Super Admin អាចធ្វើអ្វីបានទាំងអស់ (Bypass @can check)
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });


        // ចែករំលែកទិន្នន័យ $shop ទៅគ្រប់ View ទាំងអស់
        View::composer('*', function ($view) {
            // យកទិន្នន័យជួរទី១ មកបង្ហាញ (first record)
            $shop = ShopInfo::first(); 
            $view->with('shop', $shop);
        });
    }
}
