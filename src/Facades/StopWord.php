<?php

namespace Vlinde\StopWord\Facades;

use Illuminate\Support\Facades\Facade;

class StopWord extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stopword';
    }
}
