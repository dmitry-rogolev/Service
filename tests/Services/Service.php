<?php

namespace dmitryrogolev\Service\Tests\Services;

use dmitryrogolev\Contracts\Resourcable as ResourcableContract;
use dmitryrogolev\Service\Tests\Database\Seeders\UserSeeder;
use dmitryrogolev\Service\Tests\Models\User;
use dmitryrogolev\Traits\Resourcable;

class Service extends \dmitryrogolev\Services\Service implements ResourcableContract
{
    use Resourcable;

    public function __construct()
    {
        parent::__construct(User::class, UserSeeder::class);
    }
}
