<?php

namespace dmitryrogolev\Service\Tests\Services;

use dmitryrogolev\Service\Tests\Database\Seeders\UserSeeder;
use dmitryrogolev\Service\Tests\Models\User;

class Service extends \dmitryrogolev\Services\Service
{
    public function __construct()
    {
        parent::__construct(User::class, UserSeeder::class);
    }
}
