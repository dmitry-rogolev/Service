<?php

namespace dmitryrogolev\Service\Tests\Feature\Traits;

use dmitryrogolev\Service\Tests\Facades\Service;
use dmitryrogolev\Service\Tests\Models\User;
use dmitryrogolev\Service\Tests\RefreshDatabase;
use dmitryrogolev\Service\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Тестируем ресурсный сервис модели.
 */
class ResourcableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Есть ли метод, возвращающий все записи таблицы?
     */
    public function test_index(): void
    {
        $count = random_int(1, 10);
        $this->generate(User::class, $count);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $all = Service::index();
        $this->assertInstanceOf(Collection::class, $all);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Получаем все записи из таблицы.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $all = Service::index();
        $this->assertCount($count, $all);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::index();
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, создающий модель по ее аттрибутам.
     */
    public function test_store(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем получение модели.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::store($attributes);
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::store($attributes);
        $this->assertEquals(
            $attributes,
            $model->only(['name', 'email', 'password'])
        );
        $this->assertModelExists($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем коллекцию аттрибутов.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ]);
        $model = Service::store($attributes);
        $this->assertEquals(
            $attributes->all(),
            $model->only(['name', 'email', 'password'])
        );
        $this->assertModelExists($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $this->resetQueryExecutedCount();
        Service::store($attributes);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий модель по ее идентификатору.
     */
    public function test_show(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::show($user->getKey());
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::show($user->getKey());
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::show($user);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::show(364939);
        $this->assertNull($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                  Подтверждаем количество выполненных запросов.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::show($user->getKey());
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||          Передаем модель и подтверждаем, что запрос к БД не выполнен.          ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::show($user);
        $this->assertQueryExecutedCount(0);
    }

    /**
     * Есть ли метод, обновляющий модель переданными аттрибутами?
     */
    public function test_update(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::update($user, $attributes);
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем модель и массив аттрибутов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::update($user, $attributes);
        $this->assertTrue($user->is($model));
        $this->assertEquals($attributes, $model->only(['name', 'email', 'password']));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Передаем идентификатор и массив аттрибутов.                  ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::update($user->getKey(), $attributes);
        $this->assertTrue($user->is($model));
        $this->assertEquals($attributes, $model->only(['name', 'email', 'password']));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем модель и коллекцию аттрибутов.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ]);
        $model = Service::update($user, $attributes);
        $this->assertTrue($user->is($model));
        $this->assertEquals($attributes->all(), $model->only(['name', 'email', 'password']));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                  Подтверждаем количество выполненных запросов.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $this->resetQueryExecutedCount();
        $model = Service::update($user, $attributes);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, программно удаляющий модель из таблицы?
     */
    public function test_destroy(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        Service::destroy($user->getKey());
        $this->assertSoftDeleted($user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        Service::destroy($user);
        $this->assertSoftDeleted($user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                  Подтверждаем количество выполненных запросов.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        $this->resetQueryExecutedCount();
        Service::destroy($user);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, восстанавливающий программно удаленную модель?
     */
    public function test_restore(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        Service::destroy($user);
        $model = Service::restore($user->getKey());
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        Service::destroy($user);
        $this->assertSoftDeleted($user);
        $model = Service::restore($user->getKey());
        $this->assertTrue($user->is($model));
        $this->assertNotSoftDeleted($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                  Подтверждаем количество выполненных запросов.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        Service::destroy($user);
        $this->resetQueryExecutedCount();
        Service::restore($user->getKey());
        $this->assertQueryExecutedCount(2);
    }

    /**
     * Есть ли метод, удаляющий модель из таблицы?
     */
    public function test_force_destroy(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        $this->assertModelExists($user);
        Service::forceDestroy($user->getKey());
        $this->assertModelMissing($user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                                Передаем модель.                                ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        $this->assertModelExists($user);
        Service::forceDestroy($user);
        $this->assertModelMissing($user);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                  Подтверждаем количество выполненных запросов.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $user = $this->generate(User::class);
        $this->resetQueryExecutedCount();
        Service::forceDestroy($user);
        $this->assertQueryExecutedCount(1);
    }
}
