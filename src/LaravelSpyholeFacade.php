<?php

namespace Ratespecial\LaravelSpyhole;

use Illuminate\Support\Facades\Facade;

class LaravelSpyholeFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-spyhole';
    }
}
