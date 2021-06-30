<?php
use codignwithshawon\zaincash\Http\Controllers\ZainCashController;

Route::group(['namespace' => 'codignwithshawon\zaincash\Http\Controllers\ZainCashController'],function(){
    
    Route::get('/pay', [ZainCashController::class,'index'])->name('pay');
    
    Route::post('/pay', [ZainCashController::class,'payRequest']);
    Route::get('/request', [ZainCashController::class,'redirectRequest'])->name('request');
});