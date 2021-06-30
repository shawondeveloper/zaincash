<?php


namespace codignwithshawon\zaincash\Http\Controllers;

use codignwithshawon\zaincash\zaincash;

use Illuminate\Support\Facades\Facade;

class Paypage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'zaincash';
    }

}
