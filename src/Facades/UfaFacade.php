<?php namespace App\Ufa\Facades;

use Illuminate\Support\Facades\Facade;

class UfaFacade extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'UfaService';
    }

}