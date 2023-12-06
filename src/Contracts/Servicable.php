<?php

namespace dmitryrogolev\Contracts;

use ArrayAccess;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис работы с моделью.
 */
interface Servicable
{
    /**
     * Возвращает имя модели сервиса.
     */
    public function getModel(): string;

    /**
     * Возвращает имя сидера модели.
     */
    public function getSeeder(): ?string;

    /**
     * Возвращает имя фабрики модели.
     */
    public function getFactory(): string;

    /**
     * Столбцы таблицы, содержащие уникальные данные.
     *
     * @return array<int, string>
     */
    public function uniqueKeys(): array;

    /**
     * Возвращает коллекцию моделей по их идентификаторам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereKey(mixed ...$id): Collection;

    /**
     * Возвращает коллекцию всех моделей, за исключением тех, которые имеют переданные идентификаторы.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereKeyNot(mixed ...$id): Collection;

    /**
     * Возвращает коллекцию моделей по ее уникальному ключу.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) столбец(-цы).
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereUniqueKey(mixed ...$id): Collection;

    /**
     * Возвращает все записи, за исключением тех, которые содержат переданные уникальные ключи.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) столбец(-цы).
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereUniqueKeyNot(mixed ...$id): Collection;

    /**
     * Возвращает первую модель, имеющую переданный уникальный ключ.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) столбец(-цы).
     */
    public function firstWhereUniqueKey(mixed ...$id): ?Model;

    /**
     * Возвращает коллекцию моделей по столбцу.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function where(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): Collection;

    /**
     * Возвращает первую модель из коллекции, удовлетворяющую условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     */
    public function firstWhere(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): ?Model;

    /**
     * Возвращает коллекцию, которая не удовлетворяет условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereNot(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): Collection;

    /**
     * Возвращает самую позднюю по времени создания модель.
     */
    public function latest(): ?Model;

    /**
     * Возвращает самую раннюю по времени создания модель.
     */
    public function oldest(): ?Model;

    /**
     * Возвращает модель(-и) по ее(их) идентификатору(-ам).
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model|null
     */
    public function find(mixed ...$id): Model|Collection|null;

    /**
     * Возвращает множество моделей по их идентификаторам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$ids Коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function findMany(mixed ...$ids): Collection;

    /**
     * Возвращает модель(-и) по ее(их) идентификатору(-ам) или выбрасывает исключение.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  $id Идентификатор или коллекция идентификаторов.
     * @param  bool  $all [true] Выбросить исключение в случае отсутствия хотябы одного из переданных идентификаторов?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findOrFail(mixed $id, bool $all = true): Model|Collection;

    /**
     * Возвращает модели по их идентификаторам или выбрасывает исключение.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  $ids Коллекция идентификаторов.
     * @param  bool  $all [true] Выбросить исключение в случае отсутствия хотябы одного из переданных идентификаторов?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findManyOrFail(mixed $ids, bool $all = true): Collection;

    /**
     * Возвращает модель по ее идентификатору или создает новый пустой экземпляр модели.
     *
     * @param  string|int  $id Идентификатор.
     */
    public function findOrNew(mixed $id): Model;

    /**
     * Возвращает модель по ее идентификатору или возвращает результат выполнения переданной функции.
     *
     * @param  string|int  $id Идентификатор.
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function findOr(mixed $id, Closure $callback): mixed;

    /**
     * Возвращает первую запись, соответствующую атрибутам, или создает ее экземпляр.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function firstOrNew(mixed $attributes = [], mixed $values = []): Model;

    /**
     * Возвращает первую запись, соответствующую атрибутам. Если запись не найдена, создает ее.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function firstOrCreate(mixed $attributes = [], mixed $values = []): Model;

    /**
     * Пытается создать запись. Если происходит нарушение ограничения уникальности, попытается найти соответствующую запись.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function createOrFirst(mixed $attributes = [], mixed $values = []): Model;

    /**
     * Создает или обновляет запись, соответствующую атрибутам, и заполняет ее значениями.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели. При существовании записи, эти аттрибуты будут переданы для обновления.
     */
    public function updateOrCreate(mixed $attributes = [], mixed $values = []): Model;

    /**
     * Возвращает построитель SQL запросов.
     */
    public function query(): Builder;

    /**
     * Возвращает все модели.
     */
    public function all(): Collection;

    /**
     * Возвращает случайную модель из таблицы.
     */
    public function random(): ?Model;

    /**
     * Проверяет наличие хотябы одной модели в таблице по ее идентификатору или уникальному ключу.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) ключ(-и).
     */
    public function hasOne(mixed ...$id): bool;

    /**
     * Проверяет наличие всех моделей в таблице по их идентификаторам или уникальным столбцам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) ключ(-и).
     */
    public function hasAll(mixed ...$id): bool;

    /**
     * Проверяет наличие модели в таблице по ее идентификатору или уникальному столбцу.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  $id Идентификатор(-ы) или уникальный(-е) ключ(-и).
     * @param  bool  $all [false] Проверять наличие всех моделей из переданных идентификаторов?
     */
    public function has(mixed $id, bool $all = false): bool;

    /**
     * Создать экземпляр модели, но не сохранять ее в таблицу.
     */
    public function make(array $attributes = []): Model;

    /**
     * Создать модель, только если она не существует в таблице.
     */
    public function makeIfNotExists(array $attributes = []): ?Model;

    /**
     * Создать группу моделей.
     */
    public function makeGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу не существующих в таблице моделей.
     */
    public function makeGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Создать модель и сохранить ее в таблицу.
     */
    public function store(array $attributes = []): Model;

    /**
     * Создать модель и сохранить ее в таблицу.
     */
    public function create(array $attributes = []): Model;

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     */
    public function storeIfNotExists(array $attributes = []): ?Model;

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     */
    public function createIfNotExists(array $attributes = []): ?Model;

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     */
    public function storeGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     */
    public function createGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection;

    /**
     * Создать группу не существующих моделей и сохранить ее в таблицу.
     */
    public function storeGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Создать группу не существующих моделей и сохранить ее в таблицу.
     */
    public function createGroupIfNotExists(ArrayAccess|array $group): Collection;

    /**
     * Возвращает фабрику модели.
     *
     * @param  \Closure|array|int|null  $count
     * @param  \Closure|array|null  $state
     */
    public function factory($count = null, $state = []): Factory;

    /**
     * Генерирует модели с помощью фабрики.
     *
     * @param  \Closure|array|int|bool|null  $attributes
     * @param  \Closure|int|bool|null  $count
     */
    public function generate($attributes = [], $count = null, bool $create = true): Model|Collection;

    /**
     * Обновляет модель.
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * Обновляет модель.
     */
    public function fill(Model $model, array $attributes): Model;

    /**
     * Удаляет модель.
     */
    public function delete(Model $model): ?bool;

    /**
     * Очищает таблицу.
     */
    public function truncate(): void;

    /**
     * Удаляет модель.
     */
    public function forceDelete(Model $model): ?bool;

    /**
     * Восстанавливает модель.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function restore($model): bool;

    /**
     * Запускает сидер ролей.
     */
    public function seed(): void;
}
