<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ProxyHelperFacade extends Facade {
    protected static function getFacadeAccessor()
    {
        return \App\Http\Controllers\Casinodog\ProxyHelper::class;
    }
}