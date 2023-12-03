<?php

namespace dmitryrogolev\Contracts;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
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
     * Возвращает модель(-и) по ее(их) идентификатору(-ам).
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model|null
     */
    public function find(mixed ...$id): Model|Collection|null;

    /**
     * Возвращает множество моделей по их идентификаторам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$ids Коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function findMany(mixed ...$ids): Collection;

    /**
     * Возвращает модель(-и) по ее(их) идентификатору(-ам) или выбрасывает исключение.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  $id Идентификатор или коллекция идентификаторов.
     * @param  bool  $all [true] Выбросить исключение в случае отсутствия хотябы одного из переданных идентификаторов?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findOrFail(mixed $id, bool $all = true): Model|Collection;

    /**
     * Возвращает модели по их идентификаторам или выбрасывает исключение.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  $ids Коллекция идентификаторов.
     * @param  bool  $all [true] Выбросить исключение в случае отсутствия хотябы одного из переданных идентификаторов?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findManyOrFail(mixed $ids, bool $all = true): Collection;

    /**
     * Возвращает модель по ее идентификатору или создает новый пустой экземпляр модели.
     *
     * @param  mixed  $id
     * @param  bool  $all [false] Заменить все отсутствующие идентификаторы на пустой экземпляр модели?
     */
    public function findOrNew($id, bool $all = false): Model|Collection;

    /**
     * Возвращает модель по ее идентификатору или возвращает результат выполнения переданной функции.
     *
     * @param  mixed  $id
     * @param  bool  $all [false] Выполнить ли для каждой отсутствующей модели переданную функцию?
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function findOr($id, Closure $callback, bool $all = false): mixed;

    /**
     * Возвращает первую совпадающую по аттрибутам запись или создает новый экземпляр модели с такими аттрибутами, но не сохраняет ее в таблице.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function firstOrNew(Arrayable|Model|array $attributes = [], Arrayable|Model|array $values = []): Model;

    /**
     * Возвращает первую запись, совпадающую по аттрибутам, или создает новую с такими аттрибутами.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function firstOrCreate(Arrayable|Model|array $attributes = [], Arrayable|Model|array $values = []): Model;

    /**
     * Создает запись. В случае ошибки существования уникальных данных, возвращает первую запись с такими аттрибутами.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function createOrFirst(Arrayable|Model|array $attributes = [], Arrayable|Model|array $values = []): Model;

    /**
     * Создает или обновляет запись, совпадающую с переданными аттрибутами.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function updateOrCreate(Arrayable|Model|array $attributes, Arrayable|Model|array $values = []): Model;

    /**
     * Возвращает коллекцию моделей по столбцу.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     */
    public function where($column, $operator = null, $value = null): Collection;

    /**
     * Возвращает первую модель из коллекции, удовлетворяющую условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     */
    public function firstWhere($column, $operator = null, $value = null): ?Model;

    /**
     * Возвращает коллекцию, которая не удовлетворяет условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     */
    public function whereNot($column, $operator = null, $value = null): Collection;

    /**
     * Возвращает самую позднюю по времени создания модель.
     */
    public function latest(): ?Model;

    /**
     * Возвращает самую раннюю по времени создания модель.
     */
    public function oldest(): ?Model;

    /**
     * Проверяет наличие хотябы одной модели в таблице по ее идентификатору или переданному по столбцу.
     *
     * @param  mixed  $value Идентификатор или значение столбца.
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column Имя столбца. По умолчанию: первичный ключ.
     */
    public function hasOne($value, $column = null): bool;

    /**
     * Проверяет наличие всех моделей в таблице по их идентификаторам.
     *
     * @param  mixed  $id
     */
    public function hasAll(...$id): bool;

    /**
     * Проверяет наличие модели в таблице по ее идентификатору.
     *
     * @param  mixed  $id
     */
    public function has($id, bool $all = false): bool;

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
