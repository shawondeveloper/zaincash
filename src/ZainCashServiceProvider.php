<?php
namespace codignwithshawon\zaincash;
use Illuminate\Support\Facades;
use Illuminate\Support\ServiceProvider;

class ZainCashServiceProvider extends ServiceProvider 
{
    public function boot(){
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/views','zaincash');
        //$this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    public function register(){
        
    }

}