<?php

namespace App\Providers;

use App\Models\CompanyInfo;
use App\Models\Page;
use Illuminate\Support\Facades\Schema;
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
        $company = null;
        if (Schema::hasTable('company_infos')) {
            $company = CompanyInfo::find(1) ?? CompanyInfo::first();
        }

        if (!$company) {
            $company = new CompanyInfo([
                'name' => config('app.name'),
            ]);
        }

        view()->share('company', $company);

        if (Schema::hasTable('pages')) {
            $pages = Page::all();
            view()->share('pages', $pages);
        }

        Paginator::useBootstrap();
    }
}
