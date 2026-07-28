<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;

use App\Models\Parameter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Default pagination view
        Paginator::defaultView('system.pagination');

        // Default language
        if (Schema::hasTable("parameters")){
            $defaultLanguageID = Parameter::where("parameter_code", "default_language")->first();
            $defaultLanguageID = $defaultLanguageID == null ? 0 : $defaultLanguageID['parameter_value'];
        }
        else
            $defaultLanguageID = 0;

        if (!isset($defaultLanguageID) || $defaultLanguageID == null) $defaultLanguageID = 1;

        $this->app->singleton('default', function () use ($defaultLanguageID) {
            return ['language_id' => $defaultLanguageID];
        });
    }
}
