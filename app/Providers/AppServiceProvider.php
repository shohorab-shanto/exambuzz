<?php

namespace App\Providers;

use App\Models\CompanyInfo;
use App\Models\Page;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        $company = CompanyInfo::find(1);
        view()->share('company', $company);
        $pages = Page::all();
        view()->share('pages', $pages);
        Paginator::useBootstrap();
    }
}
