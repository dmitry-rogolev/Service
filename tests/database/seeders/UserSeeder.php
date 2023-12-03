<?php

namespace dmitryrogolev\Service\Tests\Database\Seeders;

use dmitryrogolev\Service\Tests\Models\User;
use Illuminate\Database\Seeder;

/**
 * Сидер модели пользователей.
 */
class UserSeeder extends Seeder
{
    /**
     * Запустить сидер.
     */
    public function run(): void
    {
        User::factory(20)->create();
    }
}
