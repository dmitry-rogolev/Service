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

        // При передачи модели в метод find класса "Illuminate\Database\Eloquent\Model",
        // она приводится к массиву, т.к. реализует интерфейс "Illuminate\Contracts\Support\Arrayable".
        // Для предотвращения этого мы заменяем модели на их идентификаторы.
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

        // При передачи модели в метод findMany класса "Illuminate\Database\Eloquent\Model",
        // она приводится к массиву, т.к. реализует интерфейс "Illuminate\Contracts\Support\Arrayable".
        // Для предотвращения этого мы заменяем модели на их идентификаторы.
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

        // При передачи модели в метод findOrFail класса "Illuminate\Database\Eloquent\Model",
        // она приводится к массиву, т.к. реализует интерфейс "Illuminate\Contracts\Support\Arrayable".
        // Для предотвращения этого мы заменяем модели на их идентификаторы.
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

        // При передачи модели в метод findOrFail класса "Illuminate\Database\Eloquent\Model",
        // она приводится к массиву, т.к. реализует интерфейс "Illuminate\Contracts\Support\Arrayable".
        // Для предотвращения этого мы заменяем модели на их идентификаторы.
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

        // При передачи модели в метод findOrNew класса "Illuminate\Database\Eloquent\Model",
        // она приводится к массиву, т.к. реализует интерфейс "Illuminate\Contracts\Support\Arrayable".
        // Для предотвращения этого мы заменяем модели на их идентификаторы.
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

        // При передачи модели в метод findOr класса "Illuminate\Database\Eloquent\Model",
        // она приводится к массиву, т.к. реализует интерфейс "Illuminate\Contracts\Support\Arrayable".
        // Для предотвращения этого мы заменяем модели на их идентификаторы.
        [$id] = $this->replaceModelsWithTheirIds($ids);

        // Предотвращает выполнение запроса к БД при передачи null в метод findOr.
        if (is_null($id)) {
            return $callback();
        }

        return $this->model::findOr($id, $callback);
    }

    /**
     * Возвращает первую совпадающую по аттрибутам запись или создает новый экземпляр модели с такими аттрибутами, но не сохраняет ее в таблице.
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
     * Возвращает первую запись, совпадающую по аттрибутам, или создает новую с такими аттрибутами.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function firstOrCreate(Arrayable|Model|array $attributes = [], Arrayable|Model|array $values = []): Model
    {
        if ($attributes instanceof Model) {
            $attributes = $attributes->getAttributes();
        }

        if ($values instanceof Model) {
            $values = $values->getAttributes();
        }

        if ($attributes instanceof Arrayable) {
            $attributes = $attributes->toArray();
        }

        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return $this->model::firstOrCreate($attributes, $values);
    }

    /**
     * Создает запись. В случае ошибки существования уникальных данных, возвращает первую запись с такими аттрибутами.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function createOrFirst(Arrayable|Model|array $attributes = [], Arrayable|Model|array $values = []): Model
    {
        if ($attributes instanceof Model) {
            $attributes = $attributes->getAttributes();
        }

        if ($values instanceof Model) {
            $values = $values->getAttributes();
        }

        if ($attributes instanceof Arrayable) {
            $attributes = $attributes->toArray();
        }

        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return $this->model::createOrFirst($attributes, $values);
    }

    /**
     * Создает или обновляет запись, совпадающую с переданными аттрибутами.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|\Illuminate\Database\Eloquent\Model|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
     * по которым ведется поиск, при создании нового экземпляра модели.
     */
    public function updateOrCreate(Arrayable|Model|array $attributes, Arrayable|Model|array $values = []): Model
    {
        if ($attributes instanceof Model) {
            $attributes = $attributes->getAttributes();
        }

        if ($values instanceof Model) {
            $values = $values->getAttributes();
        }

        if ($attributes instanceof Arrayable) {
            $attributes = $attributes->toArray();
        }

        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        return $this->model::updateOrCreate($attributes, $values);
    }

    /**
     * Возвращает коллекцию моделей по столбцу.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     */
    public function where($column, $operator = null, $value = null): Collection
    {
        if ($operator instanceof Model) {
            $operator = $operator->getAttribute($column);
        }

        if ($value instanceof Model) {
            $value = $value->getAttribute($column);
        }

        return $this->model::where($column, $operator, $value)->get();
    }

    /**
     * Возвращает первую модель из коллекции, удовлетворяющую условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     */
    public function firstWhere($column, $operator = null, $value = null): ?Model
    {
        if ($operator instanceof Model) {
            $operator = $operator->getAttribute($column);
        }

        if ($value instanceof Model) {
            $value = $value->getAttribute($column);
        }

        return $this->model::firstWhere($column, $operator, $value);
    }

    /**
     * Возвращает коллекцию, которая не удовлетворяет условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     */
    public function whereNot($column, $operator = null, $value = null): Collection
    {
        // Если передать массив, где ключами являются имена столбцов,
        // а значениями - значения этих столбцов, в метод whereNot,
        // то будет возвращен результат, как при вызове метода where.
        // Поэтому необходимо по отдельности передать ключ-значение в метод whereNot.
        if (is_array($column) || $column instanceof ArrayAccess) {
            $query = $this->model::query();

            foreach ($column as $k => $v) {
                $query->whereNot($k, $v);
            }

            return $query->get();
        }

        if ($operator instanceof Model) {
            $operator = $operator->getAttribute($column);
        }

        if ($value instanceof Model) {
            $value = $value->getAttribute($column);
        }

        return $this->model::whereNot($column, $operator, $value)->get();
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
     * Приводит переданную модель к массиву.
     *
     * @return array<string, mixed>
     */
    protected function modelToArray(Model $model): array
    {
        return $model->getAttributes();
    }

    /**
     * Приводит класс, реализующий интерфейс "Illuminate\Contracts\Support\Arrayable" к массиву.
     *
     * @return array<mixed, mixed>
     */
    protected function arrayableToArray(Arrayable $arrayable): array
    {
        return $arrayable->toArray();
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
                $value = $this->modelToArray($v);
            } elseif ($v instanceof Arrayable) {
                $value = $this->arrayableToArray($v);
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
        foreach ($models as $k => $v) {
            if ($v instanceof Model) {
                $models[$k] = $v->getKey();
            }
        }

        return $models;
    }
}
