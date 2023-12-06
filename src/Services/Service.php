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
     * Столбцы таблицы, содержащие уникальные данные.
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
    protected function setModel(string $model = null): static
    {
        if (! is_null($model) && class_exists($model) && is_a($model, Model::class, true)) {
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
    protected function setSeeder(string $seeder = null): static
    {
        if (! is_null($seeder) && class_exists($seeder) && is_a($seeder, Seeder::class, true)) {
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
    protected function setFactory(string $factory = null): static
    {
        if (! is_null($factory) && class_exists($factory) && is_a($factory, Factory::class, true)) {
            $this->factory = $factory;
        }

        return $this;
    }

    /**
     * Столбцы таблицы, содержащие уникальные данные.
     *
     * @return array<int, string>
     */
    public function uniqueKeys(): array
    {
        // Если поле с уникальными столбцами пустое и у модели есть метод "uniqueKeys",
        // получаем от него имена столбцов, содержащие уникальные данные в таблице.
        if (empty($this->uniqueKeys) && method_exists($this->model, 'uniqueKeys')) {
            $this->uniqueKeys = app($this->model)->uniqueKeys();
        }

        return $this->uniqueKeys;
    }

    /**
     * Создает новый экземпляр сервиса работы с моделью.
     *
     * @param  string|null  $model Имя класса модели.
     * @param  string|null  $seeder Имя класса сидера.
     * @param  string|null  $factory Имя класса фабрики, генерирующий модель.
     */
    public function __construct(string $model = null, string $seeder = null, string $factory = null)
    {
        $this->setModel($model)
            ->setSeeder($seeder)
            ->setFactory($factory);
    }

    /**
     * Создает экземпляр модели, но не сохраняет ее в таблицу.
     */
    public function make(Arrayable|array $attributes = []): Model
    {
        $attributes = $this->toArray($attributes);

        return $this->model::make($attributes);
    }

    /**
     * Создает экземпляр модели, только если она не существует в таблице.
     */
    public function makeIfNotExists(Arrayable|array $attributes = []): ?Model
    {
        return ! $this->hasWhere($attributes) ? $this->make($attributes) : null;
    }

    /**
     * Создать группу экземпляров моделей.
     *
     * @param  \ArrayAccess|\Illuminate\Contracts\Support\Arrayable|array  $group Группа аттрибутов.
     * @param  bool  $ifNotExists [false] Создавать модели, только если их не существует в таблице?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function makeGroup(ArrayAccess|Arrayable|array $group, bool $ifNotExists = false): Collection
    {
        $group = $this->arrayableToArray($group);

        $result = new Collection;

        // Перебираем группу аттрибутов и создаем экземпляры моделей.
        // Если ifNotExists = true, создаем модели только если их не существует в таблице.
        foreach ($group as $attributes) {
            $model = $ifNotExists ? $this->makeIfNotExists($attributes) : $this->make($attributes);

            if (! is_null($model)) {
                $result->push($model);
            }
        }

        return $result;
    }

    /**
     * Создать группу экземпляров моделей.
     *
     * @param  \ArrayAccess|\Illuminate\Contracts\Support\Arrayable|array  $group Группа аттрибутов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function makeGroupIfNotExists(ArrayAccess|Arrayable|array $group): Collection
    {
        return $this->makeGroup($group, true);
    }

    /**
     * Возвращает коллекцию моделей по их идентификаторам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereKey(mixed ...$id): Collection
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        return $this->model::whereKey($ids)->get();
    }

    /**
     * Возвращает коллекцию всех моделей, за исключением тех, которые имеют переданные идентификаторы.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereKeyNot(mixed ...$id): Collection
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        return $this->model::whereKeyNot($ids)->get();
    }

    /**
     * Возвращает коллекцию моделей по ее уникальному ключу.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) столбец(-цы).
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereUniqueKey(mixed ...$id): Collection
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Строим запрос на получение всех записей, за исключением тех,
        // которые имеют переданные первичные или уникальные ключи.
        return $this->buildQueryWhereUniqueKey($ids)->get();
    }

    /**
     * Возвращает все записи, за исключением тех, которые содержат переданные уникальные ключи.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) столбец(-цы).
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereUniqueKeyNot(mixed ...$id): Collection
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Строим запрос на получение всех записей, за исключением тех,
        // которые имеют переданные первичные или уникальные ключи.
        return $this->buildQueryWhereUniqueKeyNot($ids)->get();
    }

    /**
     * Возвращает первую модель, имеющую переданный уникальный ключ.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) столбец(-цы).
     */
    public function firstWhereUniqueKey(mixed ...$id): ?Model
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Строим запрос на получение записей по первичному ключу
        // или по другим уникальным столбцам и получаем первую запись.
        return $this->buildQueryWhereUniqueKey($ids)->first();
    }

    /**
     * Возвращает коллекцию моделей по столбцу.
     *
     * @param  \Closure|string|\Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function where(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): Collection
    {
        $column = $this->arrayableToArray($column);

        return $this->model::where($column, $operator, $value, $boolean)->get();
    }

    /**
     * Возвращает первую модель из коллекции, удовлетворяющую условию.
     *
     * @param  \Closure|string|\Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Contracts\Database\Query\Expression  $column
     */
    public function firstWhere(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): ?Model
    {
        $column = $this->arrayableToArray($column);

        return $this->model::firstWhere($column, $operator, $value, $boolean);
    }

    /**
     * Возвращает коллекцию, которая не удовлетворяет условию.
     *
     * @param  \Closure|string|\Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Contracts\Database\Query\Expression  $column
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function whereNot(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): Collection
    {
        $column = $this->arrayableToArray($column);

        return $this->buildQueryWhereNot(...func_get_args())->get();
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
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор или коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model|null
     */
    public function find(mixed ...$id): Model|Collection|null
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Предотвращает выполнение запроса к БД при передачи null в метод find.
        if (empty($ids)) {
            return null;
        }

        // Возвращаем коллекцию моделей, если передано множество идентификаторов.
        if (count($ids) > 1) {
            return $this->findMany($ids);
        }

        return $this->model::find($ids[0]);
    }

    /**
     * Возвращает множество моделей по их идентификаторам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$ids Коллекция идентификаторов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function findMany(mixed ...$ids): Collection
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($ids)
        ));

        return $this->model::findMany($ids);
    }

    /**
     * Возвращает модель(-и) по ее(их) идентификатору(-ам) или выбрасывает исключение.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  $id Идентификатор или коллекция идентификаторов.
     * @param  bool  $all [true] Выбросить исключение в случае отсутствия хотябы одного из переданных идентификаторов?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findOrFail(mixed $id, bool $all = true): Model|Collection
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Возвращаем коллекцию моделей, если передано множество идентификаторов.
        if (count($ids) > 1) {
            return $this->findManyOrFail($ids, $all);
        }

        // Предотвращает выполнение запроса к БД при передачи null в метод findOrFail.
        if (empty($ids)) {
            throw (new ModelNotFoundException)->setModel($this->model, $ids);
        }

        return $this->model::findOrFail($ids[0]);
    }

    /**
     * Возвращает модели по их идентификаторам или выбрасывает исключение.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  $ids Коллекция идентификаторов.
     * @param  bool  $all [true] Выбросить исключение в случае отсутствия хотябы одного из переданных идентификаторов?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findManyOrFail(mixed $ids, bool $all = true): Collection
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($ids)
        ));

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
     * @param  string|int  $id Идентификатор.
     */
    public function findOrNew(mixed $id): Model
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Предотвращает выполнение запроса к БД при передачи null в метод findOrNew.
        if (empty($ids)) {
            return $this->model::newModelInstance();
        }

        return $this->model::findOrNew($ids[0]);
    }

    /**
     * Возвращает модель по ее идентификатору или возвращает результат выполнения переданной функции.
     *
     * @param  string|int  $id Идентификатор.
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function findOr(mixed $id, Closure $callback): mixed
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Предотвращает выполнение запроса к БД при передачи null в метод findOr.
        if (empty($ids)) {
            return $callback();
        }

        return $this->model::findOr($ids[0], $callback);
    }

    /**
     * Возвращает первую запись, соответствующую атрибутам, или создает ее экземпляр.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
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
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
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
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
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
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, по которым ведется поиск.
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $values Аттрибуты, которые необходимо добавить к аттрибутам,
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
     * Возвращает все модели из таблицы.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
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
     * Проверяет наличие хотябы одной модели в таблице по ее идентификатору или уникальному ключу.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) ключ(-и).
     */
    public function hasOne(mixed ...$id): bool
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Строим запрос на получение записей по первичному ключу
        // или по другим уникальным столбцам и проверяем существование хотябы одной записи.
        return $this->buildQueryWhereUniqueKey($ids)->exists();
    }

    /**
     * Проверяет наличие всех моделей в таблице по их идентификаторам или уникальным столбцам.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  ...$id Идентификатор(-ы) или уникальный(-е) ключ(-и).
     */
    public function hasAll(mixed ...$id): bool
    {
        // Удаляем пустые и повторяющиеся значения.
        $ids = array_unique(array_filter(
            $this->toFlattenArray($id)
        ));

        // Строим запрос на получение записей по первичному ключу
        // или по другим уникальным столбцам и получаем количество таких записей.
        $count = $this->buildQueryWhereUniqueKey($ids)->count();

        // Подтверждаем, что в таблице существуют все переданные идентификаторы.
        return $count === count($ids);
    }

    /**
     * Проверяет наличие модели в таблице по ее идентификатору или уникальному столбцу.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string|int  $id Идентификатор(-ы) или уникальный(-е) ключ(-и).
     * @param  bool  $all [false] Проверять наличие всех моделей из переданных идентификаторов?
     */
    public function has(mixed $id, bool $all = false): bool
    {
        return $all ? $this->hasAll($id) : $this->hasOne($id);
    }

    /**
     * Проверяет наличие записи в таблице по переданному условию.
     *
     * @param  \Closure|string|\Illuminate\Contracts\Support\Arrayable|array|\Illuminate\Contracts\Database\Query\Expression  $column
     */
    public function hasWhere(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): bool
    {
        $column = $this->arrayableToArray($column);

        return $this->model::where($column, $operator, $value, $boolean)->exists();
    }

    /**
     * Создать модель и сохранить ее в таблицу.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, доступные к массовому присвоению.
     */
    public function create(Arrayable|array $attributes = []): Model
    {
        $attributes = $this->arrayableToArray($attributes);

        return $this->model::create($attributes);
    }

    /**
     * Создать модель, только если ее не существует в таблице.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $attributes Аттрибуты, доступные к массовому присвоению.
     */
    public function createIfNotExists(Arrayable|array $attributes = []): ?Model
    {
        return ! $this->hasWhere($attributes) ? $this->create($attributes) : null;
    }

    /**
     * Создать группу моделей и сохранить ее в таблицу.
     *
     * @param  \ArrayAccess|\Illuminate\Contracts\Support\Arrayable|array  $group Группа аттрибутов.
     * @param  bool  $ifNotExists [false] Создавать модели, только если их не существует в таблице?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function createGroup(ArrayAccess|Arrayable|array $group, bool $ifNotExists = false): Collection
    {
        $group = $this->arrayableToArray($group);

        $result = new Collection;

        // Перебираем группу аттрибутов и создаем модели.
        // Если ifNotExists = true, создаем модели только если их не существует в таблице.
        foreach ($group as $attributes) {
            $model = $ifNotExists ? $this->createIfNotExists($attributes) : $this->create($attributes);

            if (! is_null($model)) {
                $result->push($model);
            }
        }

        return $result;
    }

    /**
     * Создает группу не существующих в таблице моделей.
     *
     * @param  \ArrayAccess|\Illuminate\Contracts\Support\Arrayable|array  $group Группа аттрибутов.
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function createGroupIfNotExists(ArrayAccess|Arrayable|array $group): Collection
    {
        return $this->createGroup($group, true);
    }

    /**
     * Возвращает экземпляр фабрики модели.
     *
     * @param  \Closure|\Illuminate\Contracts\Support\Arrayable|array|int|null  $count Количество моделей, которое необходимо создать.
     * @param  \Closure|\Illuminate\Contracts\Support\Arrayable|array|null  $state Аттрибуты, которые необходимо передать модели в конструктор.
     */
    public function factory(mixed $count = null, mixed $state = []): Factory
    {
        [$count, $state] = $this->arrayableParamsToArray([$count, $state]);

        return $this->model::factory($count, $state);
    }

    /**
     * Генерирует модели с помощью фабрики.
     *
     * @param  \Closure|\Illuminate\Contracts\Support\Arrayable|array|bool|null  $attributes Аттрибуты, которые необходимо передать модели в конструктор.
     * @param  int|bool|null  $count Количество моделей, которое необходимо создать.
     * @param  bool  $create [true] Сохранить ли созданный экземпляр модели в таблице?
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Eloquent\Model
     */
    public function generate(mixed $attributes = [], int|bool $count = null, bool $create = true): Model|Collection
    {
        if (is_bool($count)) {
            $create = $count;
            $count = null;
        }

        if (is_int($attributes)) {
            $count = $attributes;
            $attributes = [];
        }

        if (is_bool($attributes)) {
            $create = $attributes;
            $attributes = [];
        }

        $factory = $this->factory($count, $attributes);

        return $create ? $factory->create() : $factory->make();
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
     * Строит запрос на получение моделей по уникальным столбцам.
     */
    protected function buildQueryWhereUniqueKey(array $ids): Builder
    {
        return tap($this->model::whereKey($ids), function ($query) use ($ids) {
            foreach ($this->uniqueKeys() as $key) {
                $query->orWhereIn($key, $ids);
            }
        });
    }

    /**
     * Строит запрос на получение всех моделей, за исключением тех, которые содержат уникальные ключи.
     */
    protected function buildQueryWhereUniqueKeyNot(array $ids): Builder
    {
        return tap($this->model::whereKeyNot($ids), function ($query) use ($ids) {
            foreach ($this->uniqueKeys() as $key) {
                $query->whereNotIn($key, $ids);
            }
        });
    }

    /**
     * Строит запрос на получение всех моделей, за исключением тех, которые удовлетворяют условию.
     *
     * @param  \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression  $column
     */
    protected function buildQueryWhereNot(mixed $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): Builder
    {
        return tap($this->model::query(), function ($query) use ($column, $operator, $value, $boolean) {
            // Если передать массив, где ключами являются имена столбцов,
            // а значениями - значения этих столбцов, в метод whereNot,
            // то будет возвращен результат, как при вызове метода where (добавляется двойное отрицание).
            // Например: "select * from "users" where not (not "email" = ? and not "name" = ?)".
            // Поэтому необходимо по отдельности передать ключ-значение в метод whereNot.
            if (is_array($column)) {
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
            } else {
                $query->whereNot($column, $operator, $value, $boolean);
            }
        });
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
     * Приводит переданное значение к массиву.
     */
    protected function toArray(mixed $value): array
    {
        return $value instanceof Arrayable ? $value->toArray() : Arr::wrap($value);
    }

    /**
     * Приводит переданное значение к массиву,
     * если оно реализует интерфейс "Illuminate\Contracts\Support\Arrayable".
     */
    protected function arrayableToArray(mixed $value): mixed
    {
        return $value instanceof Arrayable ? $value->toArray() : $value;
    }

    /**
     * Перебирает переданные параметры и приводит к массиву все классы,
     * реализующие интерфейс "Illuminate\Contracts\Support\Arrayable".
     *
     * @return array<int, mixed>
     */
    protected function arrayableParamsToArray(array $params): array
    {
        return array_map(fn ($item) => $this->arrayableToArray($item), $params);
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
}
