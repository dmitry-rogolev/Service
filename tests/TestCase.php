<?php

namespace dmitryrogolev\Service\Tests;

use dmitryrogolev\Service\Tests\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Возвращает случайно сгенерированного пользователя.
     */
    protected function generateUser(int $count = 1): Model|Collection
    {
        $factory = User::factory();

        return $count > 1 ? $factory->count($count)->create() : $factory->create();
    }
}
