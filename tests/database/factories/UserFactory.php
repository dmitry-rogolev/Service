<?php

namespace dmitryrogolev\Service\Tests\Database\Factories;

use dmitryrogolev\Service\Tests\Models\User;
use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;

/**
 * Фабрика модели пользователя.
 */
class UserFactory extends TestbenchUserFactory
{
    /**
     * Имя модели, для которой создается фабрика.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model = User::class;
}
