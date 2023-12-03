<?php

namespace dmitryrogolev\Service\Tests\Feature\Services;

use dmitryrogolev\Service\Tests\Database\Factories\UserFactory;
use dmitryrogolev\Service\Tests\Database\Seeders\UserSeeder;
use dmitryrogolev\Service\Tests\Models\User;
use dmitryrogolev\Service\Tests\RefreshDatabase;
use dmitryrogolev\Service\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Facade;

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
        $this->resetQueryExecutedCount();
        
        // Получаем все записи из таблицы.
        $all = Service::all();
        $this->assertQueryExecutedCount(1);
        $this->assertInstanceOf(Collection::class, $all);
        $this->assertCount($count, $all);
    }

    /**
     * Есть ли метод, возвращающий случайную модель из таблицы?
     */
    public function test_random(): void
    {
        $this->generate(User::class, 10);
        $this->resetQueryExecutedCount();

        // Получаем случайную запись из таблицы.
        $model = Service::random();
        $this->assertQueryExecutedCount(1);
        $this->assertModelExists($model);
        $this->assertInstanceOf(User::class, $model);

        // Проверяем возврат другой модели при повторном вызове.
        $model->delete();
        $this->assertFalse($model->is(Service::random()));
    }

    /**
     * Есть ли метод, возвращающий модель по ее идентификатору?
     */
    public function test_find_return_model(): void
    {
        $user = $this->generate(User::class);

        // Передаем идентификатор.
        $this->resetQueryExecutedCount();
        $model = Service::find($user->getKey());
        $this->assertQueryExecutedCount(1);
        $this->assertTrue($user->is($model));

        // Передаем модель.
        $model = Service::find($user);
        $this->assertTrue($user->is($model));

        // Передаем отсутствующий в таблице идентификатор.
        $model = Service::find(364939);
        $this->assertNull($model);

        // Передаем отсутствующую в таблице модель.
        $model = Service::find($this->generate(User::class, false));
        $this->assertNull($model);

        // Передаем null.
        $this->resetQueryExecutedCount();
        $model = Service::find(null);
        $this->assertQueryExecutedCount(0);
        $this->assertNull($model);
    }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов?
    //  */
    // public function test_find_return_collection(): void
    // {
    //     $users = $this->generate(User::class, 3);
    //     $expected = $users->pluck('id')->all();

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $actual = Service::find($ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $actual = Service::find($ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $actual = Service::find(...$ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $actual = Service::find($ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей вместе с отсутствующими в таблице идентификаторами.
    //     $ids = $users->collect()->concat(['key', 4564646, null]);
    //     $actual = Service::find($ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив отсутствующих в таблице идентификаторов.
    //     $condition = Service::find('key', 4564646, null)->isEmpty();
    //     $this->assertTrue($condition);
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов?
    //  */
    // public function test_find_many(): void
    // {
    //     $users = $this->generate(User::class, 3);
    //     $expected = $users->pluck('id')->all();

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $actual = Service::findMany($ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $actual = Service::findMany($ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $actual = Service::findMany(...$ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $actual = Service::findMany($ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей вместе с отсутствующими в таблице идентификаторами.
    //     $ids = $users->collect()->concat(['key', 4564646, null]);
    //     $actual = Service::findMany($ids)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив отсутствующих в таблице идентификаторов.
    //     $condition = Service::findMany('key', 4564646, null)->isEmpty();
    //     $this->assertTrue($condition);
    // }

    // /**
    //  * Есть ли метод, возвращающий модель по ее идентификатору или выбрасывающий исключение?
    //  */
    // public function test_find_or_fail_model_all(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue($user->is(Service::findOrFail($user->getKey(), true)));

    //     // Передаем модель.
    //     $this->assertTrue($user->is(Service::findOrFail($user, true)));

    //     // Передаем отсутствующий в таблице идентификатор.
    //     $this->expectException(ModelNotFoundException::class);
    //     Service::findOrFail(364939, true);
    // }

    // /**
    //  * Есть ли метод, возвращающий модель по ее идентификатору или выбрасывающий исключение?
    //  */
    // public function test_find_or_fail_model(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue($user->is(Service::findOrFail($user->getKey(), false)));

    //     // Передаем модель.
    //     $this->assertTrue($user->is(Service::findOrFail($user, false)));

    //     // Передаем отсутствующий в таблице идентификатор.
    //     $this->expectException(ModelNotFoundException::class);
    //     Service::findOrFail(364939, false);
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов
    //  * или выбрасывающий исключение при отсутствии хотябы одной модели из переданных идентификаторов?
    //  */
    // public function test_find_or_fail_collection_all(): void
    // {
    //     $users = $this->generateUser(3);

    //     $expected = $users->pluck('id')->all();

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $actual = Service::findOrFail($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $actual = Service::findOrFail($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $actual = Service::findOrFail($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $actual = Service::findOrFail($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей вместе с отсутствующими в таблице идентификаторами.
    //     $this->expectException(ModelNotFoundException::class);
    //     $ids = $users->collect()->concat(['key', 4564646, null]);
    //     Service::findOrFail($ids, true);
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов
    //  * или выбрасывающий исключение при отсутствии хотябы одного совпадения?
    //  */
    // public function test_find_or_fail_collection(): void
    // {
    //     $users = $this->generateUser(3);

    //     $expected = $users->pluck('id')->all();

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $actual = Service::findOrFail($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $actual = Service::findOrFail($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $actual = Service::findOrFail($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $actual = Service::findOrFail($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей вместе с отсутствующими в таблице идентификаторами.
    //     $ids = $users->collect()->concat(['key', 4564646, null]);
    //     $actual = Service::findOrFail($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив отсутствующих в таблице идентификаторов.
    //     $this->expectException(ModelNotFoundException::class);
    //     Service::findOrFail(['key', 4564646, null], false);
    // }

    // /**
    //  * Есть ли метод, возвращающий модель по ее идентификатору или возвращающий пустую модель?
    //  */
    // public function test_find_or_new_model(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue($user->is(Service::findOrNew($user->getKey(), false)));

    //     // Передаем модель.
    //     $this->assertTrue($user->is(Service::findOrNew($user, false)));

    //     // Передаем отсутствующий в таблице идентификатор.
    //     $this->assertFalse(Service::findOrNew(364939, false)->exists);
    // }

    // /**
    //  * Есть ли метод, возвращающий модель по ее идентификатору или возвращающий пустую модель?
    //  */
    // public function test_find_or_new_model_all(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue($user->is(Service::findOrNew($user->getKey(), true)));

    //     // Передаем модель.
    //     $this->assertTrue($user->is(Service::findOrNew($user, true)));

    //     // Передаем отсутствующий в таблице идентификатор.
    //     $this->assertFalse(Service::findOrNew(364939, true)->exists);
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов или возвращающий пустую коллекцию?
    //  */
    // public function test_find_or_new_collection(): void
    // {
    //     $users = $this->generateUser(3);

    //     $expected = $users->pluck('id')->all();

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $actual = Service::findOrNew($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $actual = Service::findOrNew($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $actual = Service::findOrNew($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $actual = Service::findOrNew($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей вместе с отсутствующими в таблице идентификаторами.
    //     $ids = $users->collect()->concat(['key', 4564646, null]);
    //     $actual = Service::findOrNew($ids, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив отсутствующих в таблице идентификаторов.
    //     $this->assertTrue(Service::findOrNew(['key', 4564646, null], false)->isEmpty());
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов или возвращающий коллекцию пустых моделей?
    //  */
    // public function test_find_or_new_collection_all(): void
    // {
    //     $users = $this->generateUser(3);

    //     $expected = $users->pluck('id')->all();

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $actual = Service::findOrNew($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $actual = Service::findOrNew($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $actual = Service::findOrNew($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $actual = Service::findOrNew($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей вместе с отсутствующими в таблице идентификаторами.
    //     $expected = $users->pluck('id')->concat([null, null, null])->all();
    //     $ids = $users->collect()->concat(['key', 4564646, null]);
    //     $actual = Service::findOrNew($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив отсутствующих в таблице идентификаторов.
    //     $expected = [null, null, null];
    //     $ids = ['key', 4564646, null];
    //     $actual = Service::findOrNew($ids, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);
    // }

    // /**
    //  * Есть ли метод, возвращающий модель по ее идентификатору или возвращающий результат выполнения переданной функции?
    //  */
    // public function test_find_or_model(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue($user->is(Service::findOr($user->getKey(), fn () => false, false)));

    //     // Передаем модель.
    //     $this->assertTrue($user->is(Service::findOr($user, fn () => false, false)));

    //     // Передаем отсутствующий в таблице идентификатор.
    //     $this->assertFalse(Service::findOr(364939, fn () => false, false));
    // }

    // /**
    //  * Есть ли метод, возвращающий модель по ее идентификатору или возвращающий результат выполнения переданной функции?
    //  */
    // public function test_find_or_model_all(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue($user->is(Service::findOr($user->getKey(), fn () => false, true)));

    //     // Передаем модель.
    //     $this->assertTrue($user->is(Service::findOr($user, fn () => false, true)));

    //     // Передаем отсутствующий в таблице идентификатор.
    //     $this->assertFalse(Service::findOr(364939, fn () => false, true));
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов или возвращающий результат выполнения переданной функции?
    //  */
    // public function test_find_or_collection(): void
    // {
    //     $users = $this->generateUser(3);

    //     $expected = $users->pluck('id')->all();

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $actual = Service::findOr($ids, fn () => false, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $actual = Service::findOr($ids, fn () => false, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $actual = Service::findOr($ids, fn () => false, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $actual = Service::findOr($ids, fn () => false, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей вместе с отсутствующими в таблице идентификаторами.
    //     $ids = $users->collect()->concat(['key', 4564646, null]);
    //     $actual = Service::findOr($ids, fn () => false, false)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив отсутствующих в таблице идентификаторов.
    //     $this->assertFalse(Service::findOr(['key', 4564646, null], fn () => false, false));
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по коллекции идентификаторов или возвращающий коллекцию пустых моделей?
    //  */
    // public function test_find_or_collection_all(): void
    // {
    //     $users = $this->generateUser(3);

    //     $expected = $users->pluck('id')->all();

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $actual = Service::findOr($ids, fn () => false, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $actual = Service::findOr($ids, fn () => false, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $actual = Service::findOr($ids, fn () => false, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $actual = Service::findOr($ids, fn () => false, true)->pluck('id')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем коллекцию моделей вместе с отсутствующими в таблице идентификаторами.
    //     $expected = $users->pluck('name')->concat(['!!!', '!!!', '!!!'])->all();
    //     $ids = $users->collect()->concat(['key', 4564646, null]);
    //     $actual = Service::findOr($ids, fn () => new User(['name' => '!!!']), true)->pluck('name')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив отсутствующих в таблице идентификаторов.
    //     $expected = ['!!!', '!!!', '!!!'];
    //     $ids = ['key', 4564646, null];
    //     $actual = Service::findOr($ids, fn () => new User(['name' => '!!!']), true)->pluck('name')->all();
    //     $this->assertEquals($expected, $actual);
    // }

    // /**
    //  * Есть ли метод, возвращающий первую модель, совпадающую по аттрибутам,
    //  * или создающий новый экземпляр модели с такими аттрибутами, но не сохраняющий ее в таблице?
    //  */
    // public function test_first_or_new(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем массив аттрибутов, по которым необходимо вести поиск.
    //     $this->assertTrue($user->is(Service::firstOrNew($user)));

    //     // Передаем не существующие данные.
    //     $model = Service::firstOrNew(['email' => 'admin@admin.com']);
    //     $this->assertModelMissing($model);
    //     $this->assertFalse($user->is($model));
    //     $this->assertEquals('admin@admin.com', $model->email);

    //     // Передаем аттрибуты, которые необходимо добавить при создании нового экземпляра модели.
    //     $model = Service::firstOrNew(collect(['email' => 'admin@admin.com']), collect(['name' => 'Admin']));
    //     $this->assertModelMissing($model);
    //     $this->assertEquals('Admin', $model->name);
    //     $this->assertEquals('admin@admin.com', $model->email);
    // }

    // /**
    //  * Есть ли метод, возвращающий первую запись, совпадающую по аттрибутам,
    //  * или создающий новую с такими аттрибутами?
    //  */
    // public function test_first_or_create(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем массив аттрибутов, по которым необходимо вести поиск.
    //     $this->assertTrue($user->is(Service::firstOrCreate($user)));

    //     // Передаем не существующие данные.
    //     $model = Service::firstOrCreate([
    //         'name' => 'Admin',
    //         'email' => 'admin@admin.com',
    //         'password' => 'password',
    //     ]);
    //     $this->assertModelExists($model);
    //     $this->assertFalse($user->is($model));
    //     $this->assertEquals('admin@admin.com', $model->email);

    //     // Передаем аттрибуты, которые необходимо добавить при создании новой записи.
    //     $model = Service::firstOrCreate(collect(['email' => 'admin@admin.com']), collect(['name' => 'Admin', 'password' => 'password']));
    //     $this->assertModelExists($model);
    //     $this->assertEquals('Admin', $model->name);
    //     $this->assertEquals('admin@admin.com', $model->email);
    // }

    // /**
    //  * Есть ли метод, создающий запись или возвращающий первую запись,
    //  * совпадающую с переданными аттрибутами, в случае ошибки существования уникальных данных?
    //  */
    // public function test_create_or_first(): void
    // {
    //     // Передаем не существующие данные.
    //     $model = Service::createOrFirst([
    //         'name' => 'Admin',
    //         'email' => 'admin@admin.com',
    //         'password' => 'password',
    //     ]);
    //     $this->assertModelExists($model);
    //     $this->assertEquals('admin@admin.com', $model->email);

    //     // Повторно передаем те же самые данные.
    //     $model = Service::createOrFirst(collect([
    //         'name' => 'Admin',
    //         'email' => 'admin@admin.com',
    //         'password' => 'password',
    //     ]));
    //     $this->assertModelExists($model);
    //     $this->assertEquals('admin@admin.com', $model->email);
    //     $this->assertCount(1, Service::where('email', 'admin@admin.com'));
    // }

    // /**
    //  * Есть ли метод, создающий или обновляющий запись, совпадающую с переданными аттрибутами?
    //  */
    // public function test_update_or_create(): void
    // {
    //     // Создаем запись.
    //     $model = Service::updateOrCreate([
    //         'name' => 'Admin',
    //         'email' => 'admin@admin.com',
    //         'password' => 'password',
    //     ]);
    //     $this->assertModelExists($model);
    //     $this->assertEquals('admin@admin.com', $model->email);

    //     // Обновляем существующую запись.
    //     $model = Service::updateOrCreate(collect(['email' => 'admin@admin.com']), collect(['name' => 'Dmitry']));
    //     $this->assertModelExists($model);
    //     $this->assertEquals('Dmitry', $model->name);
    //     $this->assertEquals('admin@admin.com', $model->email);
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей по столбцу?
    //  */
    // public function test_where(): void
    // {
    //     $user = $this->generateUser(3)->first();

    //     // Передаем значение столбца.
    //     $this->assertTrue($user->is(Service::where('email', $user->email)->first()));

    //     // Передаем модель.
    //     $this->assertTrue($user->is(Service::where('email', '=', $user)->first()));

    //     // Передаем массив.
    //     $this->assertTrue($user->is(Service::where(['email' => $user->email])->first()));

    //     // Передаем отсутствующее в таблице значение.
    //     $this->assertTrue(Service::where('email', 'undefined')->isEmpty());
    // }

    // /**
    //  * Есть ли метод, возвращающий модель по столбцу.
    //  */
    // public function test_first_where(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем значение столбца.
    //     $this->assertTrue($user->is(Service::firstWhere('email', $user->email)));

    //     // Передаем модель.
    //     $this->assertTrue($user->is(Service::firstWhere('email', '=', $user)));

    //     // Передаем массив.
    //     $this->assertTrue($user->is(Service::firstWhere(['email' => $user->email])));

    //     // Передаем отсутствующее в таблице значение.
    //     $this->assertNull(Service::firstWhere('email', 'undefined'));
    // }

    // /**
    //  * Есть ли метод, возвращающий коллекцию моделей, не удовлетворяющих условию?
    //  */
    // public function test_where_not(): void
    // {
    //     $users = $this->generateUser(3);
    //     $user = $users->first();
    //     $expected = $users->slice(1)->pluck('email')->all();

    //     // Передаем значение столбца.
    //     $actual = Service::whereNot('email', $user->email)->pluck('email')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем модель.
    //     $actual = Service::whereNot('email', '=', $user)->pluck('email')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем массив.
    //     $actual = Service::whereNot(['email' => $user->email])->pluck('email')->all();
    //     $this->assertEquals($expected, $actual);

    //     // Передаем отсутствующее в таблице значение.
    //     $expected = $users->pluck('email')->all();
    //     $actual = Service::whereNot('email', 'undefined')->pluck('email')->all();
    //     $this->assertEquals($expected, $actual);
    // }

    // /**
    //  * Есть ли метод, возвращающий самую позднюю по времени создания модель?
    //  */
    // public function test_latest(): void
    // {
    //     $this->generateUser(2);
    //     $this->travel(5)->minutes();
    //     $user = $this->generateUser();

    //     $this->assertTrue($user->is(Service::latest()));
    // }

    // /**
    //  * Есть ли метод, возвращающий самую раннюю по времени создания модель?
    //  */
    // public function test_oldest(): void
    // {
    //     $user = $this->generateUser();
    //     $this->travel(5)->minutes();
    //     $this->generateUser(2);

    //     $this->assertTrue($user->is(Service::oldest()));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие модели в таблице по ее идентификатору?
    //  */
    // public function test_has_one_model_primary_key(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue(Service::hasOne($user->getKey()));

    //     // Передаем модель.
    //     $this->assertTrue(Service::hasOne($user));

    //     // Передаем отсутствующие данные.
    //     $this->assertFalse(Service::hasOne(364939));

    //     // Передаем модель, которая отсутствует в таблице.
    //     $this->assertFalse(Service::hasOne(User::factory()->make()));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие модели в таблице по ее столбцу?
    //  */
    // public function test_has_one_model_column(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем электронную почту.
    //     $this->assertTrue(Service::hasOne($user->email, 'email'));

    //     // Передаем массив.
    //     $this->assertTrue(Service::hasOne([
    //         'email' => $user->email,
    //     ]));

    //     // Передаем модель.
    //     $this->assertTrue(Service::hasOne($user, 'name'));

    //     // Передаем отсутствующие данные.
    //     $this->assertFalse(Service::hasOne('email@email.com', 'email'));

    //     // Передаем отсутствующую в таблице модель.
    //     $this->assertFalse(Service::hasOne(User::factory()->make(), 'name'));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие хотябы одной модели из переданных идентификаторов?
    //  */
    // public function test_has_one_collection(): void
    // {
    //     $users = $this->generateUser(3);

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $this->assertTrue(Service::hasOne($ids));

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $this->assertTrue(Service::hasOne($ids));

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $this->assertTrue(Service::hasOne($ids));

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $this->assertTrue(Service::hasOne($ids));

    //     // Передаем массив случайных значений.
    //     $this->assertFalse(Service::hasOne(['key', 4564646, null]));

    //     // Передаем массив моделей идентификаторов со случайными значениями.
    //     $ids = $users->pluck('id')->concat(['key', 4564646, null])->all();
    //     $this->assertTrue(Service::hasOne($ids));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие модели в таблице по ее идентификатору?
    //  */
    // public function test_has_all_model(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue(Service::hasAll($user->getKey()));

    //     // Передаем модель.
    //     $this->assertTrue(Service::hasAll($user));

    //     // Передаем случайное значение.
    //     $this->assertFalse(Service::hasAll(364939));

    //     // Передаем модель, которая отсутствует в таблице.
    //     $this->assertFalse(Service::hasAll(User::factory()->make()));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие всех моделей из переданных идентификаторов?
    //  */
    // public function test_has_all_collection(): void
    // {
    //     $users = $this->generateUser(3);

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $this->assertTrue(Service::hasAll($ids));

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $this->assertTrue(Service::hasAll(...$ids));

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $this->assertTrue(Service::hasAll($ids));

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $this->assertTrue(Service::hasAll($ids));

    //     // Передаем массив случайных значений.
    //     $this->assertFalse(Service::hasAll('key', 4564646, null));

    //     // Передаем массив моделей идентификаторов со случайными значениями.
    //     $ids = $users->pluck('id')->concat(['key', 4564646, null])->all();
    //     $this->assertFalse(Service::hasAll($ids));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие модели в таблице?
    //  */
    // public function test_has_model(): void
    // {
    //     $user = $this->generateUser();

    //     // Передаем идентификатор.
    //     $this->assertTrue(Service::has($user->getKey()));

    //     // Передаем модель.
    //     $this->assertTrue(Service::has($user));

    //     // Передаем случайное значение.
    //     $this->assertFalse(Service::has(364939));

    //     // Передаем модель, которая отсутствует в таблице.
    //     $this->assertFalse(Service::has(User::factory()->make()));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие хотябы одной модели из коллекции моделей в таблице?
    //  */
    // public function test_has_collection_one(): void
    // {
    //     $users = $this->generateUser(3);

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $this->assertTrue(Service::has($ids));

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $this->assertTrue(Service::has($ids));

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $this->assertTrue(Service::has($ids));

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $this->assertTrue(Service::has($ids));

    //     // Передаем массив случайных значений.
    //     $this->assertFalse(Service::has(['key', 4564646, null]));

    //     // Передаем массив моделей идентификаторов со случайными значениями.
    //     $ids = $users->pluck('id')->concat(['key', 4564646, null])->all();
    //     $this->assertTrue(Service::has($ids));
    // }

    // /**
    //  * Есть ли метод, проверяющий наличие всех моделей из переданных идентификаторов?
    //  */
    // public function test_has_collection_all(): void
    // {
    //     $users = $this->generateUser(3);

    //     // Передаем массив идентификаторов.
    //     $ids = $users->pluck('id')->all();
    //     $this->assertTrue(Service::has($ids, true));

    //     // Передаем массив моделей.
    //     $ids = $users->all();
    //     $this->assertTrue(Service::has($ids, true));

    //     // Передаем коллекцию идентификаторов.
    //     $ids = $users->pluck('id');
    //     $this->assertTrue(Service::has($ids, true));

    //     // Передаем коллекцию моделей.
    //     $ids = $users->collect();
    //     $this->assertTrue(Service::has($ids, true));

    //     // Передаем массив случайных значений.
    //     $this->assertFalse(Service::has(['key', 4564646, null], true));

    //     // Передаем массив моделей идентификаторов со случайными значениями.
    //     $ids = $users->pluck('id')->concat(['key', 4564646, null])->all();
    //     $this->assertFalse(Service::has($ids, true));
    // }

    // /**
    //  * Есть ли метод, создающий экземпляр модели, но не сохраняющий ее в таблицу?
    //  */
    // public function test_make(): void
    // {
    //     $model = Service::make([
    //         'name' => 'Admin',
    //         'email' => 'admin@admin.com',
    //         'password' => 'password',
    //     ]);

    //     $this->assertEquals('admin@admin.com', $model->email);
    //     $this->assertModelMissing($model);
    // }

    // /**
    //  * Есть ли метод, создающий модель, только если она не существует в таблице?
    //  *
    //  * @return void
    //  */
    // public function test_make_if_not_exists(): void
    // {
    //     $attributes = ['name' => 'Admin', 'slug' => 'admin'];

    //     $this->assertNotNull(Service::makeIfNotExists($attributes));
    //     Service::userModel()::create($attributes);
    //     $this->assertNull(Service::makeIfNotExists($attributes));
    // }

    // /**
    //  * Есть ли метод, создающий группу моделей?
    //  *
    //  * @return void
    //  */
    // public function test_make_group(): void
    // {
    //     $group = [
    //         ['name' => 'User', 'slug' => 'user'],
    //         ['name' => 'Moderator', 'slug' => 'moderator'],
    //         ['name' => 'Editor', 'slug' => 'editor'],
    //         ['name' => 'Admin', 'slug' => 'admin'],
    //     ];

    //     $models = Service::makeGroup($group);
    //     $this->assertCount(4, $models);
    //     $this->assertTrue($models->every(fn ($item) => ! $item->exists));
    // }

    // /**
    //  * Есть ли метод, создающий группу не существующих моделей?
    //  *
    //  * @return void
    //  */
    // public function test_make_group_if_not_exists(): void
    // {
    //     $group = [
    //         ['name' => 'User', 'slug' => 'user'],
    //         ['name' => 'Moderator', 'slug' => 'moderator'],
    //         ['name' => 'Editor', 'slug' => 'editor'],
    //         ['name' => 'Admin', 'slug' => 'admin'],
    //     ];

    //     $models = Service::makeGroupIfNotExists($group);
    //     $this->assertCount(4, $models);
    //     $this->assertTrue($models->every(fn ($item) => ! $item->exists));

    //     collect($group)->each(fn ($item) => Service::getModel()::create($item));

    //     $models = Service::makeGroupIfNotExists($group);
    //     $this->assertCount(0, $models);
    // }

    // /**
    //  * Есть ли метод, создающий модель и сохраняющий ее в таблице?
    //  *
    //  * @return void
    //  */
    // public function test_store(): void
    // {
    //     $this->assertModelExists(Service::store(['name' => 'Admin', 'slug' => 'admin']));
    //     $this->assertModelExists(Service::create(['name' => 'User', 'slug' => 'user']));
    // }

    // /**
    //  * Есть ли метод, создающий модель и сохраняющий ее в таблице,
    //  * только если она не существует в таблице?
    //  *
    //  * @return void
    //  */
    // public function test_store_if_not_exists(): void
    // {
    //     $this->assertModelExists(Service::store(['name' => 'Admin', 'slug' => 'admin']));
    //     $this->assertNull(Service::storeIfNotExists(['name' => 'Admin', 'slug' => 'admin']));

    //     $this->assertModelExists(Service::create(['name' => 'User', 'slug' => 'user']));
    //     $this->assertNull(Service::createIfNotExists(['name' => 'User', 'slug' => 'user']));
    // }

    // /**
    //  * Есть ли метод, создающий группу моделей?
    //  *
    //  * @return void
    //  */
    // public function test_store_group(): void
    // {
    //     $group = [
    //         ['name' => 'User', 'slug' => 'user'],
    //         ['name' => 'Moderator', 'slug' => 'moderator'],
    //         ['name' => 'Editor', 'slug' => 'editor'],
    //         ['name' => 'Admin', 'slug' => 'admin'],
    //     ];

    //     $models = Service::storeGroup($group);
    //     $this->assertCount(4, $models);
    //     $this->assertTrue($models->every(fn ($item) => $item->exists));

    //     Service::userModel()::truncate();

    //     $models = Service::createGroup($group);
    //     $this->assertCount(4, $models);
    //     $this->assertTrue($models->every(fn ($item) => $item->exists));
    // }

    // /**
    //  * Есть ли метод, создающий группу не существующих моделей?
    //  *
    //  * @return void
    //  */
    // public function test_store_group_if_not_exists(): void
    // {
    //     $group = [
    //         ['name' => 'User', 'slug' => 'user'],
    //         ['name' => 'Moderator', 'slug' => 'moderator'],
    //         ['name' => 'Editor', 'slug' => 'editor'],
    //         ['name' => 'Admin', 'slug' => 'admin'],
    //     ];

    //     $models = Service::storeGroupIfNotExists($group);
    //     $this->assertCount(4, $models);
    //     $this->assertTrue($models->every(fn ($item) => $item->exists));

    //     $models = Service::storeGroupIfNotExists($group);
    //     $this->assertCount(0, $models);

    //     Service::userModel()::truncate();

    //     $models = Service::createGroupIfNotExists($group);
    //     $this->assertNotCount(0, $models);
    //     $models->each(fn ($item) => $this->assertModelExists($item));

    //     $models = Service::createGroupIfNotExists($group);
    //     $this->assertCount(0, $models);
    // }

    // /**
    //  * Есть ли метод, возвращающий фабрику модели?
    //  *
    //  * @return void
    //  */
    // public function test_factory(): void
    // {
    //     $this->assertInstanceOf(Service::getFactory(), Service::factory());
    // }

    // /**
    //  * Есть ли метод, генерирующий модели с помощью фабрики?
    //  *
    //  * @return void
    //  */
    // public function test_generate(): void
    // {
    //     // Создаем модель со случайными данными.
    //     $this->assertModelExists(Service::generate());

    //     // Создаем модель с указанными аттрибутами.
    //     $user = Service::generate(['slug' => 'admin']);
    //     $this->assertEquals('admin', $user->getSlug());
    //     $this->assertModelExists($user);

    //     // Создаем сразу несколько моделей.
    //     $users = Service::generate(3);
    //     $this->assertCount(3, $users);
    //     $this->assertModelExists($users->first());

    //     // Создаем модель, но не сохраняем ее в таблицу.
    //     $this->assertModelMissing(Service::generate(false));

    //     // Создаем сразу несколько моделей с указанными аттрибутами.
    //     $users = Service::generate(['description' => 'Role'], 3);
    //     $this->assertCount(3, $users);
    //     $this->assertTrue($users->every(fn ($item) => $item->description === 'Role' && $item->exists));

    //     // Создаем сразу несколько моделей с указанными аттрибутами, но не сохраняем их в таблице.
    //     $users = Service::generate(['description' => 'Role'], 3, false);
    //     $this->assertCount(3, $users);
    //     $this->assertTrue($users->every(fn ($item) => $item->description === 'Role' && ! $item->exists));
    // }

    // /**
    //  * Есть ли метод, обновляющий роль?
    //  *
    //  * @return void
    //  */
    // public function test_update(): void
    // {
    //     $user = Service::generate(['slug' => 'user']);
    //     Service::update($user, ['slug' => 'admin']);
    //     $this->assertEquals('admin', $user->getSlug());

    //     $user = Service::generate(['slug' => 'moderator']);
    //     Service::fill($user, ['slug' => 'editor']);
    //     $this->assertEquals('editor', $user->getSlug());
    // }

    // /**
    //  * Есть ли метод, удаляющий модель?
    //  *
    //  * @return void
    //  */
    // public function test_delete(): void
    // {
    //     $user = Service::generate();
    //     $this->assertTrue(Service::delete($user));

    //     if (Service::usesSoftDeletes()) {
    //         $this->assertSoftDeleted($user);
    //     } else {
    //         $this->assertModelMissing($user);
    //     }
    // }

    // /**
    //  * Есть ли метод, очищающий таблицу?
    //  *
    //  * @return void
    //  */
    // public function test_truncate(): void
    // {
    //     Service::generate(3);
    //     $this->assertCount(3, Service::all());
    //     Service::truncate();
    //     $this->assertCount(0, Service::all());
    // }

    // /**
    //  * Есть ли метод, удаляющий модель?
    //  *
    //  * @return void
    //  */
    // public function test_force_delete(): void
    // {
    //     $user = Service::generate();
    //     $this->assertModelExists($user);
    //     Service::forceDelete($user);
    //     $this->assertModelMissing($user);
    // }

    // /**
    //  * Есть ли метод, восстанавливающий модель после программного удаления?
    //  *
    //  * @return void
    //  */
    // public function test_restore(): void
    // {
    //     if (! Service::usesSoftDeletes()) {
    //         $this->markTestSkipped('Программное удаление моделей отключено.');
    //     }

    //     $user = Service::generate();
    //     Service::delete($user);
    //     $this->assertSoftDeleted($user);
    //     Service::restore($user);
    //     $this->assertNotSoftDeleted($user);
    // }

    // /**
    //  * Есть ли метод, запускающий сидер ролей?
    //  *
    //  * @return void
    //  */
    // public function test_seed(): void
    // {
    //     $this->assertCount(0, Service::all());
    //     Service::seed();
    //     $this->assertNotCount(0, Service::all());
    // }
}

class ItemService extends \dmitryrogolev\Services\Service
{
    public function __construct()
    {
        $this->setModel(User::class);
        $this->setSeeder(UserSeeder::class);
    }
}

class Service extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ItemService::class;
    }
}
