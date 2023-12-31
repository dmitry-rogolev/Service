<?php

namespace dmitryrogolev\Service\Tests\Feature\Services;

use dmitryrogolev\Service\Tests\Database\Factories\UserFactory;
use dmitryrogolev\Service\Tests\Database\Seeders\UserSeeder;
use dmitryrogolev\Service\Tests\Facades\Service;
use dmitryrogolev\Service\Tests\Models\User;
use dmitryrogolev\Service\Tests\RefreshDatabase;
use dmitryrogolev\Service\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Тестируем сервис работы с таблицей ролей.
 */
class ServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Есть ли метод, возвращающий имя модели сервиса?
     */
    public function test_get_model(): void
    {
        $this->assertEquals(User::class, Service::getModel());
    }

    /**
     * Есть ли метод, возвращающий имя сидера модели?
     */
    public function test_get_seeder(): void
    {
        $this->assertEquals(UserSeeder::class, Service::getSeeder());
    }

    /**
     * Есть ли метод, возвращающий имя фабрики модели?
     */
    public function test_get_factory(): void
    {
        $this->assertEquals(UserFactory::class, Service::getFactory());
    }

    /**
     * Есть ли метод, возвращающий список имен столбцов с уникальными значениями,
     * которые можно использовать для идентификации?
     */
    public function test_unique_keys(): void
    {
        $this->assertIsArray(Service::uniqueKeys());
        $this->assertNotEmpty(Service::uniqueKeys());
    }

    /**
     * Есть ли метод, создающий экземпляр модели, но не сохраняющий ее в таблицу?
     */
    public function test_make(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем получение модели.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
        ];
        $model = Service::make($attributes);
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
        ];
        $model = Service::make($attributes);
        $this->assertEquals(
            $attributes,
            $model->only(['name', 'email', 'password'])
        );
        $this->assertModelMissing($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем коллекцию аттрибутов.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
        ]);
        $model = Service::make($attributes);
        $this->assertEquals(
            $attributes->all(),
            $model->only(['name', 'email', 'password'])
        );
        $this->assertModelMissing($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
        ];
        $this->resetQueryExecutedCount();
        Service::make($attributes);
        $this->assertQueryExecutedCount(0);
    }

    /**
     * Есть ли метод, создающий модель, только если она не существует в таблице?
     */
    public function test_make_if_not_exists(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем получение модели.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
        ];
        $model = Service::makeIfNotExists($attributes);
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
        ];
        $model = Service::makeIfNotExists($attributes);
        $this->assertEquals(
            $attributes,
            $model->only(['name', 'email', 'password'])
        );
        $this->assertModelMissing($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем коллекцию аттрибутов.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
        ]);
        $model = Service::makeIfNotExists($attributes);
        $this->assertEquals(
            $attributes->all(),
            $model->only(['name', 'email', 'password'])
        );
        $this->assertModelMissing($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Передаем существующие в таблице аттрибуты.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = $this->generate(User::class)->only(['name', 'email', 'password']);
        $model = Service::makeIfNotExists($attributes);
        $this->assertNull($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'password',
        ];
        $this->resetQueryExecutedCount();
        Service::makeIfNotExists($attributes);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, создающий группу экземпляров моделей?
     */
    public function test_make_group(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методов коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $models = Service::makeGroup($group);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $models = Service::makeGroup($group);
        $this->assertCount(count($group), $models);
        $models->every(fn ($item) => $this->assertModelMissing($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем коллекцию аттрибутов.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $group = collect([
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ]);
        $models = Service::makeGroup($group);
        $this->assertCount(count($group), $models);
        $models->every(fn ($item) => $this->assertModelMissing($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $this->resetQueryExecutedCount();
        Service::makeGroup($group);
        $this->assertQueryExecutedCount(0);
    }

    /**
     * Есть ли метод, создающий группу экземпляров моделей, только если они не существуют в таблице.
     */
    public function test_make_group_if_not_exists(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методов коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $models = Service::makeGroupIfNotExists($group);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $models = Service::makeGroupIfNotExists($group);
        $this->assertCount(count($group), $models);
        $models->every(fn ($item) => $this->assertModelMissing($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем коллекцию аттрибутов.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $group = collect([
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ]);
        $models = Service::makeGroupIfNotExists($group);
        $this->assertCount(count($group), $models);
        $models->every(fn ($item) => $this->assertModelMissing($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||              Передаем коллекцию существующих в таблице аттрибутов.             ||
        // ! ||--------------------------------------------------------------------------------||

        $group->each(fn ($item) => Service::getModel()::factory()->create($item));
        $models = Service::makeGroupIfNotExists($group);
        $this->assertCount(0, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||            при передачи коллекции существующих в таблице аттрибутов.           ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::makeGroupIfNotExists($group);
        $this->assertQueryExecutedCount(count($group));
    }

    /**
     * Есть ли метод, возвращающий коллекцию с одной моделью по ее идентификатору?
     */
    public function test_where_key_with_one_param(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||             Подтверждаем возврат методом коллекции с одной моделью.            ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $models = Service::whereKey($id);
        $this->assertInstanceOf(Collection::class, $models);
        $this->assertInstanceOf(User::class, $models->first());

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $model = Service::whereKey($id)->first();
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $id = 364939;
        $model = Service::whereKey($id)->first();
        $this->assertNull($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                  Подтверждаем количество выполненных запросов.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $this->resetQueryExecutedCount();
        Service::whereKey($id);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Передаем null и подтверждаем, что запрос к БД был выполнен.          ||
        // ! ||--------------------------------------------------------------------------------||

        $id = null;
        $this->resetQueryExecutedCount();
        Service::whereKey($id);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по их идентификаторам?
     */
    public function test_where_key_with_many_params(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $users->pluck('id')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::whereKey($ids);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::whereKey($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::whereKey($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->concat(['key', 4564646, null]);
        $actual = Service::whereKey($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = ['key', 4564646, null];
        $condition = Service::whereKey(...$ids)->isEmpty();
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::whereKey($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий коллекцию всех моделей, за исключением тех, которые имеют переданные идентификаторы?
     */
    public function test_where_key_not_with_many_params(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $this->generate(User::class, 2)->pluck('id')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::whereKeyNot($ids);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::whereKeyNot($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::whereKeyNot($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->concat(['key', '4564646', 'null']);
        $actual = Service::whereKeyNot($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $expected = Service::getModel()::all()->pluck('id')->all();
        $ids = ['key', '4564646', 'null'];
        $actual = Service::whereKeyNot(...$ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::whereKeyNot($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по их уникальным ключам?
     */
    public function test_where_unique_key_with_one_param(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат коллекции моделей.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $models = Service::whereUniqueKey($id);
        $this->assertInstanceOf(Collection::class, $models);
        $this->assertInstanceOf(User::class, $models->first());

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $model = Service::whereUniqueKey($id)->first();
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Передаем значение уникального аттрибута.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $unique = $user->email;
        $model = Service::whereUniqueKey($unique)->first();
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $id = 'my_key';
        $condition = Service::whereUniqueKey($id)->isEmpty();
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $this->resetQueryExecutedCount();
        Service::whereUniqueKey($id);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по их уникальным аттрибутам?
     */
    public function test_where_unique_key_with_many_params(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $users->pluck('id')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::whereUniqueKey($ids);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::whereUniqueKey($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::whereUniqueKey($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем массив значений уникальных аттрибутов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('email')->all();
        $actual = Service::whereUniqueKey($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем смешанные данные.                           ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = [
            $users->get(0)->email,
            $users->get(1)->getKey(),
            $users->get(2)->email,
        ];
        $actual = Service::whereUniqueKey($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->concat(['key', 4564646, null]);
        $actual = Service::whereUniqueKey($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = ['key', 4564646, null];
        $condition = Service::whereUniqueKey($ids)->isEmpty();
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::whereUniqueKey($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий коллекцию всех моделей, за исключением тех, которые имеют переданные уникальные ключи?
     */
    public function test_where_unique_key_not_with_many_params(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $this->generate(User::class, 2)->pluck('id')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::whereUniqueKeyNot($ids);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::whereUniqueKeyNot($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем массив значений уникальных аттрибутов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('email')->all();
        $actual = Service::whereUniqueKeyNot($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем смешанные данные.                           ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = [
            $users->get(0)->email,
            $users->get(1)->getKey(),
            $users->get(2)->email,
        ];
        $actual = Service::whereUniqueKeyNot($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::whereUniqueKeyNot($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->concat(['key', '4564646', 'null']);
        $actual = Service::whereUniqueKeyNot($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $expected = Service::getModel()::all()->pluck('id')->all();
        $ids = ['key', '4564646', 'null'];
        $actual = Service::whereUniqueKeyNot(...$ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::whereUniqueKeyNot($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий первую из моделей, найденных по их уникальным ключам?
     */
    public function test_first_where_unique_key_with_one_param(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                          Подтверждаем возврат модели.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $model = Service::firstWhereUniqueKey($id);
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $model = Service::firstWhereUniqueKey($id);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Передаем значение уникального аттрибута.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->email;
        $model = Service::firstWhereUniqueKey($id);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $id = 'my_key';
        $model = Service::firstWhereUniqueKey($id);
        $this->assertNull($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $this->resetQueryExecutedCount();
        Service::firstWhereUniqueKey($id);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий первую из моделей, найденных по их уникальным ключам?
     */
    public function test_first_where_unique_key_with_many_params(): void
    {
        $users = $this->generate(User::class, 3);
        $first = $users->first();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                          Подтверждаем возврат модели.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $model = Service::firstWhereUniqueKey($ids);
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $model = Service::firstWhereUniqueKey($ids);
        $this->assertTrue($first->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем коллекцию уникальных ключей.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('email');
        $model = Service::firstWhereUniqueKey($ids);
        $this->assertTrue($first->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем смешанные данные.                           ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = [
            28374,
            $first->getKey(),
            $this->generate(User::class, false)->email,
        ];
        $model = Service::firstWhereUniqueKey($ids);
        $this->assertTrue($first->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем отсутствующие в таблице ключи.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = ['key', 36485, null];
        $model = Service::firstWhereUniqueKey($ids);
        $this->assertFalse($first->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::firstWhereUniqueKey($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по столбцу?
     */
    public function test_where(): void
    {
        $this->generate(User::class, 3);
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем возврат коллекции.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $models = Service::where($column, $value);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем столбец и его значение.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $model = Service::where($column, $value)->first();
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем столбец, оператор и значение.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $operator = '=';
        $value = $user->email;
        $model = Service::where($column, $operator, $value)->first();
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                               Передаем замыкание.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $operator = '=';
        $value = $user->email;
        $callback = fn ($query) => $query->where($column, $operator, $value);
        $model = Service::where($callback)->first();
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем массив пар ключ-значений,                       ||
        // ! ||             где ключ - это имя столбца, а значение - его значение.             ||
        // ! ||--------------------------------------------------------------------------------||

        $column = ['email' => $user->email];
        $model = Service::where($column)->first();
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Передаем массив, где каждое его значение представляет собой массив,      ||
        // ! ||                   состоящий из столбца, оператора и значения.                  ||
        // ! ||--------------------------------------------------------------------------------||

        $column = [
            ['email', '=', $user->email],
        ];
        $model = Service::where($column)->first();
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Передаем отсутствующее в таблице значение.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = 'undefined';
        $this->assertCount(0, Service::where($column, $value));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $this->resetQueryExecutedCount();
        Service::where($column, $value);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий первую модель по столбцу.
     */
    public function test_first_where(): void
    {
        $this->generate(User::class, 3);
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Подтверждаем возврат модели                          ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $models = Service::firstWhere($column, $value);
        $this->assertInstanceOf(Model::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем столбец и его значение.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $model = Service::firstWhere($column, $value);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем столбец, оператор и значение.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $operator = '=';
        $value = $user->email;
        $model = Service::firstWhere($column, $operator, $value);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                               Передаем замыкание.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $operator = '=';
        $value = $user->email;
        $callback = fn ($query) => $query->where($column, $operator, $value);
        $model = Service::firstWhere($callback);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем массив пар ключ-значений,                       ||
        // ! ||             где ключ - это имя столбца, а значение - его значение.             ||
        // ! ||--------------------------------------------------------------------------------||

        $column = ['email' => $user->email];
        $model = Service::firstWhere($column);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Передаем массив, где каждое его значение представляет собой массив,      ||
        // ! ||                   состоящий из столбца, оператора и значения.                  ||
        // ! ||--------------------------------------------------------------------------------||

        $column = [
            ['email', '=', $user->email],
        ];
        $model = Service::firstWhere($column);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Передаем отсутствующее в таблице значение.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = 'undefined';
        $model = Service::firstWhere($column, $value);
        $this->assertNull($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $this->resetQueryExecutedCount();
        Service::firstWhere($column, $value);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей, не удовлетворяющих условию?
     */
    public function test_where_not(): void
    {
        $users = $this->generate(User::class, 3);
        $user = $this->generate(User::class);
        $expected = $users->pluck('email')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем возврат коллекции.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $actual = Service::whereNot($column, $value);
        $this->assertInstanceOf(Collection::class, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем столбец и его значение.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $actual = Service::whereNot($column, $value)->pluck('email')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем столбец, оператор и значение.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $operator = '=';
        $value = $user->email;
        $actual = Service::whereNot($column, $operator, $value)->pluck('email')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                               Передаем замыкание.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $operator = '=';
        $value = $user->email;
        $callback = fn ($query) => $query->where($column, $operator, $value);
        $actual = Service::whereNot($callback)->pluck('email')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем массив пар ключ-значений,                       ||
        // ! ||             где ключ - это имя столбца, а значение - его значение.             ||
        // ! ||--------------------------------------------------------------------------------||

        $column = ['email' => $user->email];
        $actual = Service::whereNot($column)->pluck('email')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Передаем массив, где каждое его значение представляет собой массив,      ||
        // ! ||                   состоящий из столбца, оператора и значения.                  ||
        // ! ||--------------------------------------------------------------------------------||

        $column = [
            ['email', '=', $user->email],
        ];
        $actual = Service::whereNot($column)->pluck('email')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Передаем отсутствующее в таблице значение.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = 'undefined';
        $expected = $users->collect()->push($user)->pluck('email')->all();
        $actual = Service::whereNot($column, $value)->pluck('email')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $this->resetQueryExecutedCount();
        Service::whereNot($column, $value);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий самую позднюю по времени создания модель?
     */
    public function test_latest(): void
    {
        $this->generate(User::class, 2);
        $this->travel(5)->minutes();
        $latest = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем получение модели.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::latest();
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||          Подтверждаем получение последней по времени создания записи.          ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::latest();
        $this->assertModelExists($model);
        $this->assertTrue($latest->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        $model = Service::latest();
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||          Подтверждаем получение null при отсутствии записей в таблице.         ||
        // ! ||--------------------------------------------------------------------------------||

        Service::getModel()::truncate();
        $model = Service::latest();
        $this->assertNull($model);
    }

    /**
     * Есть ли метод, возвращающий самую раннюю по времени создания модель?
     */
    public function test_oldest(): void
    {
        $oldest = $this->generate(User::class);
        $this->travel(5)->minutes();
        $this->generate(User::class, 2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем получение модели.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::oldest();
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||          Подтверждаем получение последней по времени создания записи.          ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::oldest();
        $this->assertModelExists($model);
        $this->assertTrue($oldest->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        $model = Service::oldest();
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||          Подтверждаем получение null при отсутствии записей в таблице.         ||
        // ! ||--------------------------------------------------------------------------------||

        Service::getModel()::truncate();
        $model = Service::oldest();
        $this->assertNull($model);
    }

    /**
     * Есть ли метод, возвращающий модель по ее идентификатору?
     */
    public function test_find_return_model(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::find($user->getKey());
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::find($user->getKey());
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::find(364939);
        $this->assertNull($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                  Подтверждаем количество выполненных запросов.                 ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::find($user->getKey());
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Передаем null и подтверждаем, что запрос к БД не выполнен.           ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::find(null);
        $this->assertQueryExecutedCount(0);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов?
     */
    public function test_find_return_collection(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $users->pluck('id')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::find($ids);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::find($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::find($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->concat(['key', 4564646, null]);
        $actual = Service::find($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = ['key', 4564646, null];
        $condition = Service::find(...$ids)->isEmpty();
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::find($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов?
     */
    public function test_find_many(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $users->pluck('id')->all();

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::findMany($ids);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::findMany($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::findMany($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->concat(['key', 4564646, null]);
        $actual = Service::findMany($ids)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = ['key', 4564646, null];
        $condition = Service::findMany(...$ids)->isEmpty();
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::findMany($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий модель по ее идентификатору или выбрасывающий исключение?
     */
    public function test_find_return_model_or_fail(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::findOrFail($user->getKey());
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::findOrFail($user->getKey());
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор                             ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::findOrFail($user->getKey());
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Передаем null и подтверждаем, что запрос к БД не выполнен.           ||
        // ! ||--------------------------------------------------------------------------------||

        try {
            Service::findOrFail(null);
        } catch (ModelNotFoundException) {
            $this->resetQueryExecutedCount();
        }
        $this->assertQueryExecutedCount(0);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $this->expectException(ModelNotFoundException::class);
        Service::findOrFail(364939);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов
     * или выбрасывающий исключение при отсутствии хотябы одной модели из переданных идентификаторов?
     */
    public function test_find_return_collection_or_fail_all(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $users->pluck('id')->all();
        $all = true;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::findOrFail($ids, $all);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::findOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::findOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::findOrFail($ids, $all);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $this->expectException(ModelNotFoundException::class);
        $ids = $users->pluck('id')->concat(['key', 4564646, null]);
        Service::findOrFail($ids, $all);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов
     * или выбрасывающий исключение при отсутствии не одной записи?
     */
    public function test_find_return_collection_or_fail(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $users->pluck('id')->all();
        $all = false;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::findOrFail($ids, $all);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::findOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::findOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::findOrFail($ids, $all);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $this->expectException(ModelNotFoundException::class);
        $ids = $users->pluck('id')->concat(['key', 4564646, null]);
        $actual = Service::findOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $this->expectException(ModelNotFoundException::class);
        $ids = ['key', 4564646, null];
        Service::findOrFail($ids, $all);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов
     * или выбрасывающий исключение при отсутствии хотябы одной модели из переданных идентификаторов?
     */
    public function test_find_many_or_fail_all(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $users->pluck('id')->all();
        $all = true;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::findManyOrFail($ids, $all);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::findManyOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::findManyOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::findManyOrFail($ids, $all);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $this->expectException(ModelNotFoundException::class);
        $ids = $users->pluck('id')->concat(['key', 4564646, null]);
        Service::findManyOrFail($ids, $all);
    }

    /**
     * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов
     * или выбрасывающий исключение при отсутствии не одной записи?
     */
    public function test_find_many_or_fail(): void
    {
        $users = $this->generate(User::class, 3);
        $expected = $users->pluck('id')->all();
        $all = false;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $models = Service::findManyOrFail($ids, $all);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $actual = Service::findManyOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id');
        $actual = Service::findManyOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем массив идентификаторов                        ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::findManyOrFail($ids, $all);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем коллекцию идентификаторов                       ||
        // ! ||               вместе с отсутствующими в таблице идентификаторами.              ||
        // ! ||--------------------------------------------------------------------------------||

        $this->expectException(ModelNotFoundException::class);
        $ids = $users->pluck('id')->concat(['key', 4564646, null]);
        $actual = Service::findManyOrFail($ids, $all)->pluck('id')->all();
        $this->assertEquals($expected, $actual);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||            Передаем массив отсутствующих в таблице идентификаторов.            ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = ['key', 4564646, null];
        $this->expectException(ModelNotFoundException::class);
        Service::findManyOrFail($ids, $all);
    }

    /**
     * Есть ли метод, возвращающий модель по ее идентификатору или возвращающий новый экземпляр пустой модели?
     */
    public function test_find_or_new(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::findOrNew($user->getKey());
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::findOrNew($user->getKey());
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::findOrNew(364939);
        $this->assertFalse($user->is($model));
        $this->assertModelMissing($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор                             ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::findOrNew($user->getKey());
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Передаем null и подтверждаем, что запрос к БД не выполнен.           ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::findOrNew(null);
        $this->assertQueryExecutedCount(0);
    }

    /**
     * Есть ли метод, возвращающий модель по ее идентификатору или возвращающий результат выполнения переданной функции?
     */
    public function test_find_or(): void
    {
        $user = $this->generate(User::class);
        $callback = fn () => false;

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::findOr($user->getKey(), $callback);
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::findOr($user->getKey(), $callback);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                 Передаем отсутствующий в таблице идентификатор.                ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::findOr(364939, $callback);
        $this->assertFalse($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор                             ||
        // ! ||                 и подтверждаем количество выполненных запросов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::findOr($user->getKey(), $callback);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Передаем null и подтверждаем, что запрос к БД не выполнен.           ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::findOr(null, $callback);
        $this->assertQueryExecutedCount(0);
    }

    /**
     * Есть ли метод, возвращающий первую модель, совпадающую по аттрибутам,
     * или создающий новый экземпляр модели с такими аттрибутами, но не сохраняющий ее в таблице?
     */
    public function test_first_or_new(): void
    {
        $firstModelInTable = $this->generate(User::class);
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => $user->email,
        ];
        $model = Service::firstOrNew($attributes);
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||         Передаем массив аттрибутов, по которым необходимо вести поиск.         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => $user->email,
        ];
        $model = Service::firstOrNew($attributes);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||              Ничего не передаем. Получаем первую запись в таблице.             ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::firstOrNew();
        $this->assertTrue($firstModelInTable->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||         Передаем массив аттрибутов, по которым необходимо вести поиск,         ||
        // ! ||           и массив значений (аттрибутов), которые необходимо добавить          ||
        // ! ||         к аттрибутам, по которым велся поиск при создании новой модели.        ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => $user->name,
            'password' => $user->password,
        ];
        $model = Service::firstOrNew($attributes, $values);
        $this->assertModelMissing($model);
        $this->assertEquals(
            array_merge($attributes, $values),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||        Передаем коллекцию аттрибутов, по которым необходимо вести поиск,       ||
        // ! ||         и коллекцию значений (аттрибутов), которые необходимо добавить         ||
        // ! ||         к аттрибутам, по которым велся поиск при создании новой модели.        ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'email' => fake()->unique()->email(),
        ]);
        $values = collect([
            'name' => $user->name,
            'password' => $user->password,
        ]);
        $model = Service::firstOrNew($attributes, $values);
        $this->assertModelMissing($model);
        $this->assertEquals(
            $attributes->merge($values)->all(),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                       при получении существующей модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => $user->email,
        ];
        $this->resetQueryExecutedCount();
        Service::firstOrNew($attributes);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                     при создании нового экземпляра модели.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => $user->name,
            'password' => $user->password,
        ];
        $this->resetQueryExecutedCount();
        Service::firstOrNew($attributes, $values);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий первую запись, совпадающую по аттрибутам,
     * или создающий новую с такими аттрибутами?
     */
    public function test_first_or_create(): void
    {
        $firstModelInTable = $this->generate(User::class);
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => $user->email,
        ];
        $model = Service::firstOrCreate($attributes);
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||         Передаем массив аттрибутов, по которым необходимо вести поиск.         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => $user->email,
        ];
        $model = Service::firstOrCreate($attributes);
        $this->assertTrue($user->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||              Ничего не передаем. Получаем первую запись в таблице.             ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::firstOrCreate();
        $this->assertTrue($firstModelInTable->is($model));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||         Передаем массив аттрибутов, по которым необходимо вести поиск,         ||
        // ! ||           и массив значений (аттрибутов), которые необходимо добавить          ||
        // ! ||         к аттрибутам, по которым велся поиск при создании новой модели.        ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => $user->name,
            'password' => $user->password,
        ];
        $model = Service::firstOrCreate($attributes, $values);
        $this->assertModelExists($model);
        $this->assertEquals(
            array_merge($attributes, $values),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||        Передаем коллекцию аттрибутов, по которым необходимо вести поиск,       ||
        // ! ||         и коллекцию значений (аттрибутов), которые необходимо добавить         ||
        // ! ||         к аттрибутам, по которым велся поиск при создании новой модели.        ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'email' => fake()->unique()->email(),
        ]);
        $values = collect([
            'name' => $user->name,
            'password' => $user->password,
        ]);
        $model = Service::firstOrCreate($attributes, $values);
        $this->assertModelExists($model);
        $this->assertEquals(
            $attributes->merge($values)->all(),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                       при получении существующей модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => $user->email,
        ];
        $this->resetQueryExecutedCount();
        Service::firstOrCreate($attributes);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                              при создании модели.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => $user->name,
            'password' => $user->password,
        ];
        $this->resetQueryExecutedCount();
        Service::firstOrCreate($attributes, $values);
        $this->assertQueryExecutedCount(2);
    }

    /**
     * Есть ли метод, создающий запись или возвращающий первую запись,
     * совпадающую с переданными аттрибутами, в случае ошибки уникальности?
     */
    public function test_create_or_first(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => 'Admin',
            'password' => 'password',
        ];
        $model = Service::createOrFirst($attributes, $values);
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем аттрибуты в виде массивов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => 'Admin',
            'password' => 'password',
        ];
        $model = Service::createOrFirst($attributes, $values);
        $this->assertModelExists($model);
        $this->assertEquals(
            array_merge($attributes, $values),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем аттрибуты в виде коллекции.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'email' => fake()->unique()->email(),
        ]);
        $values = collect([
            'name' => 'Admin',
            'password' => 'password',
        ]);
        $model = Service::createOrFirst($attributes, $values);
        $this->assertModelExists($model);
        $this->assertEquals(
            $attributes->merge($values)->all(),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем аттрибуты, с уже существующим                     ||
        // ! ||                        в таблице уникальным аттрибутом.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::createOrFirst($attributes, $values);
        $this->assertModelExists($model);
        $this->assertCount(1, Service::getModel()::where('email', $attributes['email'])->get());
        $this->assertEquals(
            $attributes->merge($values)->all(),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                              при создании модели.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => 'Admin',
            'password' => 'password',
        ];
        $this->resetQueryExecutedCount();
        Service::createOrFirst($attributes, $values);
        $this->assertQueryExecutedCount(1);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                            при существовании модели.                           ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::createOrFirst($attributes, $values);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, создающий или обновляющий запись, совпадающую с переданными аттрибутами?
     */
    public function test_update_or_create(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => 'Admin',
            'password' => 'password',
        ];
        $model = Service::updateOrCreate($attributes, $values);
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем аттрибуты в виде массивов.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => 'Admin',
            'password' => 'password',
        ];
        $model = Service::updateOrCreate($attributes, $values);
        $this->assertModelExists($model);
        $this->assertEquals(
            array_merge($attributes, $values),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем аттрибуты в виде коллекции.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'email' => fake()->unique()->email(),
        ]);
        $values = collect([
            'name' => 'Admin',
            'password' => 'password',
        ]);
        $model = Service::updateOrCreate($attributes, $values);
        $this->assertModelExists($model);
        $this->assertEquals(
            $attributes->merge($values)->all(),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем аттрибуты, с уже существующим                     ||
        // ! ||                        в таблице уникальным аттрибутом.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $values = [
            'name' => 'User',
            'password' => 'qwerty',
        ];
        $model = Service::updateOrCreate($attributes, $values);
        $this->assertModelExists($model);
        $this->assertEquals(
            array_merge($attributes->all(), $values),
            $model->only(['email', 'name', 'password'])
        );

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                              при создании модели.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $values = [
            'name' => 'Dmitry',
            'password' => '123456',
        ];
        $this->resetQueryExecutedCount();
        Service::updateOrCreate($attributes, $values);
        $this->assertQueryExecutedCount(2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                             при обновлении модели.                             ||
        // ! ||--------------------------------------------------------------------------------||

        $values = [
            'name' => 'Admin',
            'password' => 'password',
        ];
        $this->resetQueryExecutedCount();
        Service::updateOrCreate($attributes, $values);
        $this->assertQueryExecutedCount(2);
    }

    /**
     * Есть ли метод, возвращающий построитель SQL запросов?
     */
    public function test_query(): void
    {
        $this->assertInstanceOf(Builder::class, Service::query());
    }

    /**
     * Есть ли метод, возвращающий все записи таблицы?
     */
    public function test_all(): void
    {
        $count = random_int(1, 10);
        $this->generate(User::class, $count);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $all = Service::all();
        $this->assertInstanceOf(Collection::class, $all);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Получаем все записи из таблицы.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $all = Service::all();
        $this->assertCount($count, $all);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::all();
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, возвращающий случайную модель из таблицы?
     */
    public function test_random(): void
    {
        $this->generate(User::class, 10);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::random();
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Получаем случайную запись из таблицы.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::random();
        $this->assertModelExists($model);
        $this->assertInstanceOf(User::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||              Проверяем возврат другой модели при повторном вызове.             ||
        // ! ||--------------------------------------------------------------------------------||

        $model->delete();
        $this->assertFalse($model->is(Service::random()));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::random();
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие модели в таблице по ее идентификатору или уникальному ключу?
     */
    public function test_has_one_with_one_param(): void
    {
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $condition = Service::hasOne($id);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                             Передаем идентификатор.                            ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $condition = Service::has($id);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                            Передаем уникальный ключ.                           ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->email;
        $condition = Service::hasOne($id);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем отсутствующий идентификатор.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $id = 'my_key';
        $condition = Service::hasOne($id);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $id = $user->getKey();
        $this->resetQueryExecutedCount();
        $condition = Service::hasOne($id);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие хотябы одной модели из переданных идентификаторов или уникальных ключей?
     */
    public function test_has_one_with_many_params(): void
    {
        $users = $this->generate(User::class, 3);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $condition = Service::hasOne(...$ids);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $condition = Service::has($ids);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем коллекцию уникальных ключей.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('email');
        $condition = Service::hasOne($ids);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем смешанные данные.                           ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = [
            28374,
            $users->first()->getKey(),
            $this->generate(User::class, false)->email,
        ];
        $condition = Service::hasOne($ids);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем отсутствующие в таблице ключи.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = ['key', 36485, null];
        $condition = Service::has($ids);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::hasOne($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие всех моделей в таблице по идентификатору или по уникальному ключу?
     */
    public function test_has_all(): void
    {
        $users = $this->generate(User::class, 3);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $condition = Service::hasAll(...$ids);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив идентификаторов.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $condition = Service::has($ids, true);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем коллекцию уникальных ключей.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('email');
        $condition = Service::hasAll($ids);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем смешанные данные.                           ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = [
            $users->get(1)->email,
            $users->first()->getKey(),
            $users->get(2)->getKey(),
        ];
        $condition = Service::hasAll($ids);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем отсутствующие в таблице ключи.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = ['key', 36485, $users->first()->email];
        $condition = Service::has($ids, true);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $ids = $users->pluck('id')->all();
        $this->resetQueryExecutedCount();
        Service::hasAll($ids);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, проверяющий наличие записи по переданному условию?
     */
    public function test_has_where(): void
    {
        $this->generate(User::class, 3);
        $user = $this->generate(User::class);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Подтверждаем возврат логического значения.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $condition = Service::hasWhere($column, $value);
        $this->assertIsBool($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем столбец и его значение.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $condition = Service::hasWhere($column, $value);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем столбец, оператор и значение.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $operator = '=';
        $value = $user->email;
        $condition = Service::hasWhere($column, $operator, $value);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                               Передаем замыкание.                              ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $operator = '=';
        $value = $user->email;
        $callback = fn ($query) => $query->where($column, $operator, $value);
        $condition = Service::hasWhere($callback);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем массив пар ключ-значений,                       ||
        // ! ||             где ключ - это имя столбца, а значение - его значение.             ||
        // ! ||--------------------------------------------------------------------------------||

        $column = ['email' => $user->email];
        $condition = Service::hasWhere($column);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||       Передаем массив, где каждое его значение представляет собой массив,      ||
        // ! ||                   состоящий из столбца, оператора и значения.                  ||
        // ! ||--------------------------------------------------------------------------------||

        $column = [
            ['email', '=', $user->email],
        ];
        $model = Service::hasWhere($column);
        $this->assertTrue($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Передаем отсутствующее в таблице значение.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = 'undefined';
        $condition = Service::hasWhere($column, $value);
        $this->assertFalse($condition);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $column = 'email';
        $value = $user->email;
        $this->resetQueryExecutedCount();
        Service::hasWhere($column, $value);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, создающий модель?
     */
    public function test_create(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем получение модели.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::create($attributes);
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::create($attributes);
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
        $model = Service::create($attributes);
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
        Service::create($attributes);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, создающий модель, только если она не существует в таблице?
     */
    public function test_create_if_not_exists(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Подтверждаем получение модели.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::createIfNotExists($attributes);
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $model = Service::createIfNotExists($attributes);
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
        $model = Service::createIfNotExists($attributes);
        $this->assertEquals(
            $attributes->all(),
            $model->only(['name', 'email', 'password'])
        );
        $this->assertModelExists($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Передаем существующие в таблице аттрибуты.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = $this->generate(User::class)->only(['name', 'email', 'password']);
        $model = Service::createIfNotExists($attributes);
        $this->assertNull($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||               при передаче не существующих в таблице аттрибутов.               ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'name' => fake()->name(),
            'email' => fake()->unique()->email(),
            'password' => fake()->password(),
        ];
        $this->resetQueryExecutedCount();
        Service::createIfNotExists($attributes);
        $this->assertQueryExecutedCount(2);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||                 при передаче существующих в таблице аттрибутов.                ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = $this->generate(User::class)->only(['name', 'email', 'password']);
        $this->resetQueryExecutedCount();
        $model = Service::createIfNotExists($attributes);
        $this->assertQueryExecutedCount(1);
    }

    /**
     * Есть ли метод, создающий группу моделей?
     */
    public function test_create_group(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методов коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $models = Service::createGroup($group);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $models = Service::createGroup($group);
        $this->assertCount(count($group), $models);
        $models->every(fn ($item) => $this->assertModelExists($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем коллекцию аттрибутов.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $group = collect([
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ]);
        $models = Service::createGroup($group);
        $this->assertCount(count($group), $models);
        $models->every(fn ($item) => $this->assertModelExists($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $this->resetQueryExecutedCount();
        Service::createGroup($group);
        $this->assertQueryExecutedCount(count($group));
    }

    /**
     * Есть ли метод, создающий группу не существующих в таблице моделей?
     */
    public function test_create_group_if_not_exists(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методов коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $models = Service::createGroupIfNotExists($group);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                           Передаем массив аттрибутов.                          ||
        // ! ||--------------------------------------------------------------------------------||

        $group = [
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ];
        $models = Service::createGroupIfNotExists($group);
        $this->assertCount(count($group), $models);
        $models->every(fn ($item) => $this->assertModelExists($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                         Передаем коллекцию аттрибутов.                         ||
        // ! ||--------------------------------------------------------------------------------||

        $group = collect([
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
            ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ]);
        $models = Service::createGroupIfNotExists($group);
        $this->assertCount(count($group), $models);
        $models->every(fn ($item) => $this->assertModelExists($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||              Передаем коллекцию существующих в таблице аттрибутов.             ||
        // ! ||--------------------------------------------------------------------------------||

        $models = Service::createGroupIfNotExists($group);
        $this->assertCount(0, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем количество выполненных запросов к БД               ||
        // ! ||            при передачи коллекции существующих в таблице аттрибутов.           ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::createGroupIfNotExists($group);
        $this->assertQueryExecutedCount(count($group));
    }

    /**
     * Есть ли метод, возвращающий фабрику модели?
     */
    public function test_factory(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Подтверждаем возврат методом экземпляра фабрики.                ||
        // ! ||--------------------------------------------------------------------------------||

        $factory = Service::factory();
        $this->assertInstanceOf(Service::getFactory(), $factory);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Подтверждаем возможность создания модели с помощью фабрики.          ||
        // ! ||--------------------------------------------------------------------------------||

        $factory = Service::factory();
        $model = $factory->create();
        $this->assertModelExists($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Передаем количество создаваемых моделей.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $count = 3;
        $factory = Service::factory($count);
        $models = $factory->create();
        $this->assertCount($count, $models);
        $models->each(fn ($item) => $this->assertModelExists($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем массив аттрибутов модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $state = [
            'email' => fake()->unique()->email(),
        ];
        $factory = Service::factory($state);
        $model = $factory->create();
        $this->assertModelExists($model);
        $this->assertEquals($state, $model->only(['email']));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем коллекцию аттрибутов модели.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $state = collect([
            'email' => fake()->unique()->email(),
        ]);
        $factory = Service::factory($state);
        $model = $factory->create();
        $this->assertModelExists($model);
        $this->assertEquals($state->all(), $model->only(['email']));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||           Передаем количество создаваемых моделей и аттрибуты модели.          ||
        // ! ||--------------------------------------------------------------------------------||

        $count = 3;
        $state = [
            'name' => fake()->name(),
        ];
        $factory = Service::factory($count, $state);
        $models = $factory->create();
        $models->each(fn ($item) => $this->assertModelExists($item));
        $models->each(fn ($item) => $this->assertEquals($state, $item->only(['name'])));
    }

    /**
     * Есть ли метод, генерирующий модели с помощью фабрики?
     */
    public function test_generate(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем возврат методом модели.                      ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::generate();
        $this->assertInstanceOf(Model::class, $model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Подтверждаем возврат методом коллекции.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $count = 3;
        $models = Service::generate($count);
        $this->assertInstanceOf(Collection::class, $models);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                   Создаем модель со сгенерированными данными.                  ||
        // ! ||--------------------------------------------------------------------------------||

        $model = Service::generate();
        $this->assertModelExists($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                       Передаем массив аттрибутов модели.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = [
            'email' => fake()->unique()->email(),
        ];
        $model = Service::generate($attributes);
        $this->assertModelExists($model);
        $this->assertEquals($attributes, $model->only(['email']));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Передаем коллекцию аттрибутов модели.                     ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'email' => fake()->unique()->email(),
        ]);
        $model = Service::generate($attributes);
        $this->assertModelExists($model);
        $this->assertEquals($attributes->all(), $model->only(['email']));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                    Передаем количество создаваемых моделей.                    ||
        // ! ||--------------------------------------------------------------------------------||

        $count = 3;
        $models = Service::generate($count);
        $this->assertCount($count, $models);
        $models->each(fn ($item) => $this->assertModelExists($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                Передаем флаг создания только экземпляра модели.                ||
        // ! ||--------------------------------------------------------------------------------||

        $create = false;
        $model = Service::generate($create);
        $this->assertModelMissing($model);

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                     Передаем количество создаваемых моделей                    ||
        // ! ||                    и флаг создания только экземпляра модели.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $count = 3;
        $create = false;
        $models = Service::generate($count, $create);
        $this->assertCount($count, $models);
        $models->each(fn ($item) => $this->assertModelMissing($item));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив аттрибутов модели                       ||
        // ! ||                    и флаг создания только экземпляра модели.                   ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'email' => fake()->unique()->email(),
        ]);
        $create = false;
        $model = Service::generate($attributes, $create);
        $this->assertModelMissing($model);
        $this->assertEquals($attributes->all(), $model->only(['email']));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Передаем массив аттрибутов модели                       ||
        // ! ||                        и количество создаваемых моделей.                       ||
        // ! ||--------------------------------------------------------------------------------||

        $attributes = collect([
            'name' => fake()->name(),
        ]);
        $count = 3;
        $models = Service::generate($attributes, $count);
        $this->assertCount($count, $models);
        $models->each(fn ($item) => $this->assertModelExists($item));
        $models->each(fn ($item) => $this->assertEquals($attributes->all(), $item->only(['name'])));

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        $count = 3;
        Service::generate($count);
        $this->assertQueryExecutedCount($count);
    }

    /**
     * Есть ли метод, очищающий таблицу?
     */
    public function test_truncate(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                      Подтверждаем полную очистку таблицы.                      ||
        // ! ||--------------------------------------------------------------------------------||

        Service::generate(3);
        $this->assertCount(3, Service::all());
        Service::truncate();
        $this->assertCount(0, Service::all());

        // ! ||--------------------------------------------------------------------------------||
        // ! ||               Подтверждаем количество выполненных запросов к БД.               ||
        // ! ||--------------------------------------------------------------------------------||

        $this->resetQueryExecutedCount();
        Service::truncate();
        $this->assertQueryExecutedCount(2);
    }

    /**
     * Есть ли метод, запускающий сидер моделей?
     */
    public function test_seed(): void
    {
        // ! ||--------------------------------------------------------------------------------||
        // ! ||                        Подтверждаем заполнение таблицы.                        ||
        // ! ||--------------------------------------------------------------------------------||

        $this->assertCount(0, Service::all());
        Service::seed();
        $this->assertNotCount(0, Service::all());
    }
}
