<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Validation rules
        // alphabet
        Validator::extend('alphabet_number_dash', function ($attribute, $value, $parameters, $validator) {
            return(preg_match("/^[A-Za-z0-9_-]*$/i",$value));
        });
        Validator::extend('alphabet_dash', function ($attribute, $value, $parameters, $validator) {
            return(preg_match("/^[A-Za-z_-]*$/i",$value));
        });
        Validator::extend('alphabet_number', function ($attribute, $value, $parameters, $validator) {
            return(preg_match("/^[A-Za-z0-9]*$/i",$value));
        });
        Validator::extend('alphabet', function ($attribute, $value, $parameters, $validator) {
            return(preg_match("/^[A-Za-z]*$/i",$value));
        });

        Validator::extend('checkboxes_required', function ($attribute, $value, $parameters, $validator) {
            return(count($value) > 0);
        });
    }
}
