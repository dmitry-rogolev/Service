<?php

namespace dmitryrogolev\Services;

use ArrayAccess;
use Closure;
use dmitryrogolev\Contracts\Servicable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;

/**
 * Сервис работы с моделью.
 */
abstract class Service implements Servicable
{
    use Macroable;

    /**
     * Имя модели таблицы.
     */
    protected string $model;

    /**
     * Имя сидера модели.
     */
    protected string $seeder;

    /**
     * Имя фабрики модели.
     */
    protected string $factory;

    /**
     * Список имен столбцов с уникальными значениями,
     * которые можно использовать для идентификации.
     *
     * @var array<int, string>
     */
    protected array $uniqueKeys = [];

    /**
     * Возвращает имя модели сервиса.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Изменяет имя модели сервиса.
     */
    protected function setModel(string $model): static
    {
        if (class_exists($model) && is_a($model, Model::class, true)) {
            $this->model = $model;
        }

        return $this;
    }

    /**
     * Возвращает имя сидера модели.
     */
    public function getSeeder(): ?string
    {
        return isset($this->seeder) ? $this->seeder : null;
    }

    /**
     * Изменяет имя сидера модели.
     */
    protected function setSeeder(string $seeder): static
    {
        if (class_exists($seeder) && is_a($seeder, Seeder::class, true)) {
            $this->seeder = $seeder;
        }

        return $this;
    }

    /**
     * Возвращает имя фабрики модели.
     */
    public function getFactory(): string
    {
        return isset($this->factory) ? $this->factory : $this->model::factory()::class;
    }

    /**
     * Возвращает список имен столбцов с уникальными значениями,
     * которые можно использовать для идентификации.
     *
     * @return array<int, string>
     */
    public function getUniqueKeys(): array
    {
        return isset($this->uniqueKeys) ? $this->uniqueKeys : [];
    }

    /**
     * Изменяет имя фабрики модели.
     */
    protected function setFactory(string $factory): static
    {
        if (class_exists($factory) && is_a($factory, Factory::class, true)) {
            $this->factory = $factory;
        }

        return $this;
    }

    /**
     * Возвращает коллекцию моделей по их идентификаторам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereKey(mixed ...$id): Collection
    {
        $ids = $this->toFlattenArray($id);

        // Заменяем модели на их идентификаторы.
        $ids = $this->replaceModelsWithTheirIds($ids);

        return $this->model::whereKey($ids)->get();
    }

    /**
     * Возвращает коллекцию всех моделей, за исключением тех, которые имеют переданные идентификаторы.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereKeyNot(mixed ...$id): Collection
    {
        $ids = $this->toFlattenArray($id);

        // Заменяем модели на их идентификаторы.
        $ids = $this->replaceModelsWithTheirIds($ids);

        return $this->model::whereKeyNot($ids)->get();
    }

    /**
     * Возвращает коллекцию моделей по ее уникальному ключу.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$key Ключ или коллекция уникальных ключей.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereUniqueKey(mixed ...$key): Collection
    {
        $values = $this->toFlattenArray($key);

        // Заменяем модели на значения уникальных аттрибутов.
        $values = $this->replaceModelsWithTheirUniqueKeys($values);

        // Строим запрос на получение записей по первичному ключу
        // или по другим уникальным столбцам.
        return tap($this->model::whereKey($values), function ($query) use ($values) {
            foreach ($this->uniqueKeys as $key) {
                $query->orWhereIn($key, $values);
            }
        })->get();
    }

    /**
     * Возвращает все записи, за исключением тех, которые содержат переданные уникальные ключи.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$key Ключ или коллекция уникальных ключей.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereUniqueKeyNot(mixed ...$key): Collection
    {
        $values = $this->toFlattenArray($key);

        // Заменяем модели на значения уникальных аттрибутов и отфильтровываем пустые значения.
        $values = array_filter(
            $this->replaceModelsWithTheirUniqueKeys($values)
        );

        // Строим запрос на получение записей, первичные ключи
        // или уникальные столбцы которых не содержатся в переданных ключах.
        return tap($this->model::whereKeyNot($values), function ($query) use ($values) {
            foreach ($this->uniqueKeys as $key) {
                $query->whereNotIn($key, $values);
            }
        })->get();
    }

    /**
     * Возвращает первую модель, имеющую переданный уникальный ключ.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$key Ключ или коллекция уникальных ключей.
     */
    public function firstWhereUniqueKey(mixed ...$key): ?Model
    {
        $values = $this->toFlattenArray($key);

        // Заменяем модели на значения уникальных аттрибутов.
        $values = $this->replaceModelsWithTheirUniqueKeys($values);

        // Строим запрос на получение записей по первичному ключу
        // или по другим уникальным столбцам.
        return tap($this->model::whereKey($values), function ($query) use ($values) {
            foreach ($this->uniqueKeys as $key) {
                $query->orWhereIn($key, $values);
            }
        })->first();
    }

    /**
     * Возвращает коллекцию моделей по столбцу.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function where(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): Collection
    {
        return $this->model::where(...func_get_args())->get();
    }

    /**
     * Возвращает первую модель из коллекции, удовлетворяющую условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     */
    public function firstWhere(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): ?Model
    {
        return $this->model::firstWhere(...func_get_args());
    }

    /**
     * Возвращает коллекцию, которая не удовлетворяет условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereNot(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): Collection
    {
        // Если передать массив, где ключами являются имена столбцов,
        // а значениями - значения этих столбцов, в метод whereNot,
        // то будет возвращен результат, как при вызове метода where (добавляется двойное отрицание).
        // Например: "select * from "users" where not (not "email" = ? and not "name" = ?)".
        // Поэтому необходимо по отдельности передать ключ-значение в метод whereNot.
        if (is_array($column)) {
            $query = $this->model::query();

            foreach ($column as $k => $v) {
                // Если ключ представлен числом, а значение массивом,
                // то будем считать, что значение содержит столбец, оператор и значение.
                // Например: $column = [ [ 'email', '=', 'email@email.com' ] ].
                if (is_numeric($k) && is_array($v)) {
                    $query->whereNot(...array_values($v));
                }

                // Иначе будем считать, что ключ - это столбец, а значение - значение этого столбца.
                // Например: $column = [ 'email' => 'email@email.com' ].
                else {
                    $query->whereNot($k, $v);
                }
            }

            return $query->get();
        }

        return $this->model::whereNot(...func_get_args())->get();
    }

    /**
     * Возвращает самую позднюю по времени создания модель.
     */
    public function latest(): ?Model
    {
        return $this->model::latest()->first();
    }

    /**
     * Возвращает самую раннюю по времени создания модель.
     */
    public function oldest(): ?Model
    {
        return $this->model::oldest()->first();
    }

    /**
     * Возвращает модель(-и) по ее(их) идентификатору(-ам).
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model|null
     */
    public function find(mixed ...$id): Model|Collection|null
    {
        $ids = $this->toFlattenArray($id);

        // Возвращаем коллекцию моделей, если передано множество идентификаторов.
        if (count($ids) > 1) {
            return $this->findMany($ids);
        }

        // Заменяем модели на их идентификаторы.
        [$id] = $this->replaceModelsWithTheirIds($ids);

        // Предотвращает выполнение запроса к БД при передачи null в метод find.
        if (is_null($id)) {
            return null;
        }

        return $this->model::find($id);
    }

    /**
     * Возвращает множество моделей по их идентификаторам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  ...$ids Коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function findMany(mixed ...$ids): Collection
    {
        $ids = $this->toFlattenArray($ids);

        // Заменяем модели на их идентификаторы.
        $ids = $this->replaceModelsWithTheirIds($ids);

        return $this->model::findMany($ids);
    }

    /**
     * Возвращает модель(-и) по ее(их) идентификатору(-ам) или выбрасывает исключение.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  $id Идентификатор или коллекция идентификаторов.
     * @param  bool  $all [true] Выбросить исключение в случае отсутствия хотябы одного из переданных идентификаторов?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findOrFail(mixed $id, bool $all = true): Model|Collection
    {
        $ids = $this->toFlattenArray($id);

        // Возвращаем коллекцию моделей, если передано множество идентификаторов.
        if (count($ids) > 1) {
            return $this->findManyOrFail($ids, $all);
        }

        // Заменяем модели на их идентификаторы.
        [$id] = $this->replaceModelsWithTheirIds($ids);

        // Предотвращает выполнение запроса к БД при передачи null в метод findOrFail.
        if (is_null($id)) {
            throw (new ModelNotFoundException)->setModel($this->model, $id);
        }

        return $this->model::findOrFail($id);
    }

    /**
     * Возвращает модели по их идентификаторам или выбрасывает исключение.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array|string|int  $ids Коллекция идентификаторов.
     * @param  bool  $all [true] Выбросить исключение в случае отсутствия хотябы одного из переданных идентификаторов?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findManyOrFail(mixed $ids, bool $all = true): Collection
    {
        $ids = $this->toFlattenArray($ids);

        // Заменяем модели на их идентификаторы.
        $ids = $this->replaceModelsWithTheirIds($ids);

        // Метод findOrFail выбрасывает исключение,
        // если хотя бы один идентификатор из переданных отсутствует в таблице.
        // Поэтому если необходимо выбросить исключение только тогда,
        // когда не найдено не одной записи, мы выбрасываем исключение сами.
        if (! $all) {
            $result = $this->findMany($ids);

            if ($result->isEmpty()) {
                throw (new ModelNotFoundException)->setModel($this->model, $ids);
            }

            return $result;
        }

        return $this->model::findOrFail($ids);
    }

    /**
     * Возвращает модель по ее идентификатору или создает новый пустой экземпляр модели.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string|int  $id Идентификатор.
     */
    public function findOrNew(mixed $id): Model
    {
        $ids = $this->toFlattenArray($id);

        // Заменяем модели на их идентификаторы.
        [$id] = $this->replaceModelsWithTheirIds($ids);

        // Предотвращает выполнение запроса к БД при передачи null в метод findOrNew.
        if (is_null($id)) {
            return $this->model::newModelInstance();
        }

        return $this->model::findOrNew($id);
    }

    /**
     * Возвращает модель по ее идентификатору или возвращает результат выполнения переданной функции.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string|int  $id Идентификатор.
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function findOr(mixed $id, Closure $callback): mixed
    {
        $ids = $this->toFlattenArray($id);

        // Заменяем модели на их идентификаторы.
        [$id] = $this->replaceModelsWithTheirIds($ids);

        // Предотвращает выполнение запроса к БД при передачи null в метод findOr.
        if (is_null($id)) {
            return $callback();
        }

        return $this->model::findOr($id, $callback);
    }

    /**
     * Возвращает первую запись, соответствующую атрибутам, или создает ее экземпляр.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function firstOrNew(mixed $attributes = [], mixed $values = []): Model
    {
        [$attributes, $values] = $this->paramsToArray(compact('attributes', 'values'));

        return $this->model::firstOrNew($attributes, $values);
    }

    /**
     * Возвращает первую запись, соответствующую атрибутам. Если запись не найдена, создает ее.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function firstOrCreate(mixed $attributes = [], mixed $values = []): Model
    {
        [$attributes, $values] = $this->paramsToArray(compact('attributes', 'values'));

        return $this->model::firstOrCreate($attributes, $values);
    }

    /**
     * Пытается создать запись. Если происходит нарушение ограничения уникальности, попытается найти соответствующую запись.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function createOrFirst(mixed $attributes = [], mixed $values = []): Model
    {
        [$attributes, $values] = $this->paramsToArray(compact('attributes', 'values'));

        return $this->model::createOrFirst($attributes, $values);
    }

    /**
     * Создает или обновляет запись, соответствующую атрибутам, и заполняет ее значениями.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели. При существовании записи, эти аттрибуты будут переданы для обновления.
     */
    public function updateOrCreate(mixed $attributes = [], mixed $values = []): Model
    {
        [$attributes, $values] = $this->paramsToArray(compact('attributes', 'values'));

        return $this->model::updateOrCreate($attributes, $values);
    }

    /**
     * Возвращает построитель SQL запросов.
     */
    public function query(): Builder
    {
        return $this->model::query();
    }

    /**
     * Возвращает все модели.
     */
    public function all(): Collection
    {
        return $this->model::all();
    }

    /**
     * Возвращает случайную модель из таблицы.
     */
    public function random(): ?Model
    {
        return $this->model::query()->inRandomOrder()->first();
    }

    /**
     * Проверяет наличие хотябы одной модели в таблице по ее идентификатору или переданному по столбцу.
     *
     * @param  mixed  $value Идентификатор или значение столбца.
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column Имя столбца. По умолчанию: первичный ключ.
     */
    public function hasOne($value, $column = null): bool
    {
        $values = ! is_array($value) ? Arr::wrap($value) : $value;

        foreach ($values as $key => $value) {
            if (is_string($key) && $this->firstWhere($key, $value)) {
                return true;
            }

            if (! is_null($column) && $this->firstWhere($column, $value)) {
                return true;
            }

            if (is_null($column) && $this->find($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет наличие всех моделей в таблице по их идентификаторам.
     *
     * @param  mixed  $id
     */
    public function hasAll(...$id): bool
    {
        $ids = Arr::flatten($id);

        foreach ($ids as $id) {
            if (! $this->find($id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяет наличие модели в таблице по ее идентификатору.
     *
     * @param  mixed  $id
     */
    public function has($id, bool $all = false): bool
    {
        return $all ? $this->hasAll($id) : $this->hasOne($id);
    }

    /**
     * Создать экземпляр модели, но не сохранять ее в таблицу.
     */
    public function make(array $attributes = []): Model
    {
        return $this->model::make($attributes);
    }

    /**
     * Создать модель, только если она не существует в таблице.
     */
    public function makeIfNotExists(array $attributes = []): ?Model
    {
        return $this->make($attributes);
    }

    /**
     * Создать группу моделей.
     */
    public function makeGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection
    {
        $result = new Collection;

        foreach ($group as $attributes) {
            if (is_array($attributes) && ($model = $ifNotExists ? $this->makeIfNotExists($attributes) : $this->make($attributes))) {
                $result->push($model);
            }
        }

        return $result;
    }

    /**
     * Создать группу не существующих в таблице моделей.
     */
    public function makeGroupIfNotExists(ArrayAccess|array $group): Collection
    {
        return $this->makeGroup($group, true);
    }

    /**
     * Создать модель и сохранить ее в таблицу.
     */
    public function store(array $attributes = []): Model
    {
        return $this->model::create($attributes);
    }

    /**
     * Создать модель и сохранить ее в таблицу.
     */
    public function create(array $attributes = []): Model
    {
        return $this->store($attributes);
    }

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     */
    public function storeIfNotExists(array $attributes = []): ?Model
    {
        return $this->store($attributes);
    }

    /**
     * Создать модель и сохранить ее в таблицу, если ее не существует.
     */
    public function createIfNotExists(array $attributes = []): ?Model
    {
        return $this->storeIfNotExists($attributes);
    }

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     */
    public function storeGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection
    {
        $result = new Collection;

        foreach ($group as $attributes) {
            if (is_array($attributes) && ($model = $ifNotExists ? $this->storeIfNotExists($attributes) : $this->store($attributes))) {
                $result->push($model);
            }
        }

        return $result;
    }

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     */
    public function createGroup(ArrayAccess|array $group, bool $ifNotExists = false): Collection
    {
        return $this->storeGroup($group, $ifNotExists);
    }

    /**
     * Создать группу не существующих моделей и сохранить ее в таблицу.
     */
    public function storeGroupIfNotExists(ArrayAccess|array $group): Collection
    {
        return $this->storeGroup($group, true);
    }

    /**
     * Создать группу не существующих моделей и сохранить ее в таблицу.
     */
    public function createGroupIfNotExists(ArrayAccess|array $group): Collection
    {
        return $this->storeGroupIfNotExists($group);
    }

    /**
     * Возвращает фабрику модели.
     *
     * @param  \Closure|array|int|null  $count
     * @param  \Closure|array|null  $state
     */
    public function factory($count = null, $state = []): Factory
    {
        return $this->model::factory($count, $state);
    }

    /**
     * Генерирует модели с помощью фабрики.
     *
     * @param  \Closure|array|int|bool|null  $attributes
     * @param  \Closure|int|bool|null  $count
     */
    public function generate($attributes = [], $count = null, bool $create = true): Model|Collection
    {
        $attributes = value($attributes);
        $count = value($count);

        if (is_int($attributes)) {
            $count = $attributes;
            $attributes = [];
        }

        if (is_bool($attributes)) {
            $create = $attributes;
            $attributes = [];
        }

        if (is_bool($count)) {
            $create = $count;
            $count = null;
        }

        $factory = $this->factory($count);

        return $create ? $factory->create($attributes) : $factory->make($attributes);
    }

    /**
     * Обновляет модель.
     */
    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * Обновляет модель.
     */
    public function fill(Model $model, array $attributes): Model
    {
        return $this->update($model, $attributes);
    }

    /**
     * Удаляет модель.
     */
    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Очищает таблицу.
     */
    public function truncate(): void
    {
        $this->model::truncate();
    }

    /**
     * Удаляет модель.
     */
    public function forceDelete(Model $model): ?bool
    {
        return $model->forceDelete();
    }

    /**
     * Восстанавливает модель.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function restore($model): bool
    {
        return $model->restore();
    }

    /**
     * Запускает сидер модели.
     */
    public function seed(): void
    {
        app($this->seeder)->run();
    }

    /**
     * Приводит переданной значение к выровненному массиву.
     *
     * @return array<int, mixed>
     */
    protected function toFlattenArray(mixed $value): array
    {
        return Arr::flatten([$value]);
    }

    /**
     * Приводит переданные параметры функции к массиву.
     *
     * @return array<int, array>
     */
    protected function paramsToArray(array $params): array
    {
        $result = [];

        foreach ($params as $v) {
            $value = [];

            if ($v instanceof Model) {
                $value = $v->getAttributes();
            } elseif ($v instanceof Arrayable) {
                $value = $v->toArray();
            } elseif (is_array($v)) {
                $value = $v;
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * Заменяет модели на их идентификаторы.
     *
     * @return array<int, mixed>
     */
    protected function replaceModelsWithTheirIds(array $models): array
    {
        $result = array_map(fn ($item) => $item instanceof Model ? $item->getKey() : $item, $models);

        return array_values($result);
    }

    /**
     * Заменяет модели на их уникальные ключи.
     *
     * @return array<int, mixed>
     */
    protected function replaceModelsWithTheirUniqueKeys(array $models): array
    {
        $result = [];

        foreach ($models as $model) {
            if ($model instanceof Model) {
                $keys = [$model->getKey()];

                foreach ($this->uniqueKeys as $key) {
                    $keys[] = $model->getAttribute($key);
                }

                $result[] = $keys;
            } else {
                $result[] = $model;
            }
        }

        return Arr::flatten($result);
    }
}
