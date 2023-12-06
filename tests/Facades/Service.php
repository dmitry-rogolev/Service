<?php

namespace dmitryrogolev\Service\Tests\Facades;

use Illuminate\Support\Facades\Facade;

class Service extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \dmitryrogolev\Service\Tests\Services\Service::class;
    }
}
