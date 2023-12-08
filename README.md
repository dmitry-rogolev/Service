# Service 

Сервис работы с моделью Eloquent Laravel.

Сервис добавляет новый уровень абстракции между контроллером и моделью. Вместо того, чтобы проводить операции с моделью в контроллере, вы выносите эту логику в сервис, тем самым улучшая читабельность кода и тем самым следуя принципу единственной ответственности.

## Содержание 

1. [Подключение](#подключение)
2. [Создание сервиса](#создание-сервиса)
    
    - [Создание класса](#создание-класса)
    - [Конфигурация класса](#конфигурация-класса)

        + [Указание модели](#указание-модели)
        + [Указание сидера](#указание-сидера-не-обязательно)

    - [Вынесение логики из контроллера](#вынесение-логики-из-контроллера)
    - [Ресурсный сервис](#ресурсный-сервис)
    - [Создание фасада](#создание-фасада)

3. [Доступные методы](#доступные-методы)

    - [Список](#список)
    - [Подробное описание](#подробное-описание)

## Подключение 

Добавьте ссылку на репозиторий в файл `composer.json`.

    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:dmitry-rogolev/Service.git"
        }
    ]

Подключите пакет с помощью команды:

    composer require dmitryrogolev/service

## Создание сервиса

### Создание класса

После подключения пакета, нам необходимо создать класс сервиса для своей модели. В этом примере мы будем использовать модель пользователя `App\Models\User`, вы можете заменить ее на любую другую. 

Пакет предоставляет абстрактный класс `dmitryrogolev\Services\Service`, в котором уже определен основной функционал по работе с моделью. Нам необходимо расширить его, указав с какой именно моделью мы работаем.

Итак, создадим класс сервиса для модели `App\Models\User` и поместим его, например, в папку `app/Services`.

    <?php 

    namespace App\Services;

    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {

    }

### Конфигурация класса

#### Указание модели

Добавим ему защищенное поле `$model`, хранящее имя нашей модели. 

    <?php 

    namespace App\Services;

    use App\Models\User;
    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {
        /**
         * Имя модели.
         */
        protected string $model = User::class;
    }

Альтернативно, мы можем передать имя модели в конструктор сервиса первым параметром.

    <?php 

    namespace App\Services;

    use App\Models\User;
    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {
        public function __construct()
        {
            parent::__construct(User::class);
        }
    }

Или мы можем передать имя модели в сеттер.

    <?php 

    namespace App\Services;

    use App\Models\User;
    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {
        public function __construct()
        {
            parent::__construct();

            $this->setModel(User::class);
        }
    }

#### Указание сидера [Не обязательно]

Добавим ему защищенное поле `$seeder`, хранящее имя сидера нашей модели. 

    <?php 

    namespace App\Services;

    use Database\Seeders\UserSeeder;
    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {
        /**
        * Имя сидера модели.
        */
        protected string $seeder = UserSeeder::class;
    }

Альтернативно, мы можем передать имя сидера модели в конструктор сервиса вторым параметром.

    <?php 

    namespace App\Services;

    use App\Models\User;
    use Database\Seeders\UserSeeder;
    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {
        public function __construct()
        {
            parent::__construct(User::class, UserSeeder::class);
        }
    }

Или мы можем передать имя модели в сеттер.

    <?php 

    namespace App\Services;

    use Database\Seeders\UserSeeder;
    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {
        public function __construct()
        {
            parent::__construct();

            $this->setSeeder(UserSeeder::class);
        }
    }

### Вынесение логики из контроллера

Теперь, когда класс нашего сервиса создан и мы указали с какой моделью он работает, нам необходимо вынести логику работы с нашей моделью из контроллера в только что созданный специально для этого сервис.

Допустим, у нас есть контроллер `App\Http\Controllers\UserController`, в котором есть метод `update`, обновляющий данные пользователя.

    <?php 

    namespace App\Http\Controllers;

    use App\Models\User;
    use App\Http\Requests\User\UpdateRequest;
    use App\Http\Resources\UserResource;

    class UserController extends Controller 
    {
        public function update(UpdateRequest $request, User $user): UserResource
        {
            // Проверяем на валидность данные от клиента.
            $validated = $request->validated();

            // Обновляем данные пользователя.
            $user->fill($validated);

            // Сохраняем изменения.
            $user->save();

            // Возвращаем обновленную модель пользователя.
            return new UserResource($user);
        }
    }

Нам необходимо вынести логику, связанную с изменением модели в сервис. Для этого создадим метод `update` в нашем сервисе.

    <?php 

    namespace App\Services;

    use App\Models\User;
    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {
        /**
         * Имя модели.
         */
        protected string $model = User::class;

        /** 
         * Обновляет модель пользователя.
         */
        public function update(User $user, array $attributes): User 
        {
            // Обновляем данные пользователя.
            $user->fill($validated);

            // Сохраняем изменения.
            $user->save();
            
            // Возвращаем обновленную модель пользователя.
            return $user;
        }
    }

Теперь метод `update` контроллера `App\Http\Controllers\UserController` будет выглядеть так: 

    <?php 

    namespace App\Http\Controllers;

    use App\Models\User;
    use App\Http\Requests\User\UpdateRequest;
    use App\Http\Resources\UserResource;
    use App\Services\UserService;

    class UserController extends Controller 
    {
        protected UserService $service;

        public function __construct()
        {
            parent::__construct();

            $this->service = new UserService();
        }

        public function update(UpdateRequest $request, User $user): UserResource
        {
            // Проверяем на валидность данные от клиента.
            $validated = $request->validated();

            // Обновляем модель пользователя.
            $this->service->update($user, $validated);

            // Возвращаем обновленную модель пользователя.
            return new UserResource($user);
        }
    }

Такую операцию будет необходимо проделать для каждого метода контроллера, где используется модель. Это делается для того, чтобы перенести ответственность по обработке модели из контроллера в специальный сервис. Это облегчает читабельность кода и его рефакторинг. 

Вы можете возразить, что излишне создавать целый класс для того, чтобы заменить две строчки кода на одну, и вы будете правы. Если брать код в целом, то заместо того, чтобы уменьшать размер этого кода, мы наоборот увеличиваем его, создавая новый класс. Но наша задача не в том, чтобы уменьшить размер кода, хотя, если говорить о методе `update` контроллера `App\Http\Controllers\UserController`, то мы действительно его уменьшили, а в том, чтобы вынести логику по работе с моделью из контроллера, для чего и служит, собственно, созданный нами сервис. 

### Ресурсный сервис

Если вы используете ресурсный контроллер, то вы можете реализовать интерфейс `dmitryrogolev\Contracts\Resourcable` в своем сервисе, который содержит основные методы ресурсного контроллера, а именно: 

- `index` - Возвращает коллекцию всех моделей таблицы;
- `store` - Создает модель с переданными аттрибутами;
- `show` - Возвращает модель по ее идентификатору;
- `update` - Обновляет модель переданными аттрибутами;
- `destroy` - Удаляет модель из таблицы или программно удаляет модель;
- `restore` - Восстанавливает программно удаленную модель;
- `forceDestroy` - Удаляет модель из таблицы.

Если у вас простая логика по работе с моделью, то вы можете подключить трейт `dmitryrogolev\Traits\Resourcable`, в котором определены выше перечисленные методы.

### Создание фасада

Как было показано выше в примере, перед тем как пользоваться сервисом, нам необходимо было создать экземпляр этого сервиса в конструкторе, записать ссылку на него в поле класса и только потом мы смогли воспользоваться им. Это не очень удобно. Хотелось бы воспользоваться сервисом не создавая самостоятельно его экземпляра и вызывать его методы статически. Для этого есть решение - фасад.

Давайте создадим класс фасада, который мы назовем `UserService` и поместим в папку `app\Facades`. Он должен расширять класс `Illuminate\Support\Facades\Facade`.

    <?php

    namespace App\Facades;

    use Illuminate\Support\Facades\Facade;

    class UserService extends Facade
    {
        
    }

Теперь необходимо добавить метод `getFacadeAccessor`, который должен возвращать имя класса нашего сервиса.

    <?php

    namespace App\Facades;

    use Illuminate\Support\Facades\Facade;

    class Service extends Facade
    {
        protected static function getFacadeAccessor()
        {
            return \App\Services\UserService::class;
        }
    }

На этом создание фасада закончено. Теперь, используя фасад, мы будем вызывать методы сервиса статически. Если вернуться к примеру выше, то наш контроллер теперь будет выглядеть так: 

    <?php 

    namespace App\Http\Controllers;

    use App\Models\User;
    use App\Http\Requests\User\UpdateRequest;
    use App\Http\Resources\UserResource;
    use App\Facades\UserService;

    class UserController extends Controller 
    {
        public function update(UpdateRequest $request, User $user): UserResource
        {
            // Проверяем на валидность данные от клиента.
            $validated = $request->validated();

            // Обновляем модель пользователя.
            UserService::update($user, $validated);

            // Возвращаем обновленную модель пользователя.
            return new UserResource($user);
        }
    }

## Доступные методы

### Список

В классе `dmitryrogolev\Services\Service` определено множество методов, облегчающих работу с моделью. Вот полный их список: 

- [getModel](#getmodel) - Возвращает имя модели сервиса.
- [getSeeder](#getseeder) - Возвращает имя сидера модели.
- [getFactory](#getfactory) - Возвращает имя фабрики модели.
- [uniqueKeys](#uniquekeys) - Столбцы таблицы, содержащие уникальные данные.
- [all](#all) - Возвращает все модели из таблицы.
- [create](#create) - Создает модель и сохраняет ее в таблицу.
- [createIfNotExists](#createifnotexists) - Создает модель только если ее не существует в таблице.
- [createGroup](#creategroup) - Создает группу моделей.
- [createGroupIfNotExists](#creategroupifnotexists) - Создает группу не существующих в таблице моделей.
- [createOrFirst](#createorfirst) - Пытается создать запись. Если происходит нарушение ограничения уникальности, попытается найти соответствующую запись.
- [factory](#factory) - Возвращает экземпляр фабрики модели.
- [find](#find) - Возвращает модель(-и) по ее(их) идентификатору(-ам).
- [findMany](#findmany) - Возвращает множество моделей по их идентификаторам.
- [findManyOrFail](#findmanyorfail) - Возвращает коллекцию моделей по их идентификаторам или выбрасывает исключение.
- [findOr](#findor) - Возвращает модель по ее идентификатору или возвращает результат выполнения переданной функции.
- [findOrFail](#findorfail) - Возвращает модель(-и) по ее(их) идентификатору(-ам) или выбрасывает исключение.
- [findOrNew](#findornew) - Возвращает модель по ее идентификатору или создает новый пустой экземпляр модели.
- [firstOrCreate](#firstorcreate) - Возвращает первую запись, соответствующую атрибутам. Если запись не найдена, создает ее.
- [firstOrNew](#firstornew) - Возвращает первую запись, соответствующую атрибутам, или создает ее экземпляр, не сохраняя в таблице.
- [firstWhere](#firstwhere) - Возвращает первую модель из коллекции, удовлетворяющую условию.
- [firstWhereUniqueKey](#firstwhereuniquekey) - Возвращает первую модель, имеющую переданный уникальный ключ.
- [generate](#generate) - Генерирует модели с помощью фабрики.
- [has](#has) - Проверяет наличие модели в таблице по ее идентификатору или уникальному столбцу.
- [hasAll](#hasall) - Проверяет наличие всех моделей в таблице по их идентификаторам или уникальным столбцам.
- [hasOne](#hasone) - Проверяет наличие хотябы одной модели в таблице по ее идентификатору или уникальному ключу.
- [hasWhere](#haswhere) - Проверяет наличие записи в таблице по переданному условию.
- [latest](#latest) - Возвращает самую позднюю по времени создания модель.
- [make](#make) - Создает экземпляр модели, но не сохраняет ее в таблицу.
- [makeGroup](#makegroup) - Создает группу экземпляров моделей.
- [makeGroupIfNotExists](#makegroupifnotexists) - Создать группу не существующих в таблице экземпляров моделей.
- [makeIfNotExists](#makeifnotexists) - Создает экземпляр модели, только если она не существует в таблице.
- [oldest](#oldest) - Возвращает самую раннюю по времени создания модель.
- [query](#query) - Возвращает построитель SQL запросов.
- [random](#random) - Возвращает случайную модель из таблицы.
- [seed](#seed) - Запускает сидер модели.
- [truncate](#truncate) - Очищает таблицу.
- [updateOrCreate](#updateorcreate) - Создает или обновляет запись, соответствующую атрибутам, и заполняет ее значениями.
- [where](#where) - Возвращает коллекцию моделей по столбцу.
- [whereKey](#wherekey) - Возвращает коллекцию моделей по их идентификаторам.
- [whereKeyNot](#wherekeynot) - Возвращает коллекцию всех моделей, за исключением тех, которые имеют переданные идентификаторы.

### Подробное описание

#### `getModel`

Возвращает имя модели сервиса.

    $modelClass = Service::getModel(); // string
    $model = new $model;

#### `getSeeder` 

Возвращает имя сидера модели.

    $seederClass = Service::getSeeder(); // string
    $seeder = new $seederClass;

#### `getFactory`

Возвращает имя фабрики модели.

    $factoryClass = Service::getFactory(); // string
    $factory = new $factoryClass;

#### `uniqueKeys`

Возвращает столбцы таблицы, содержащие уникальные данные в виде массива.

    $uniqueKeys = Service::uniqueKeys(); // array

Некоторые методы сервиса используют эти ключи для поиска записей. Например, метод `find` ищет записи по их идентификаторам (первичным ключам), а метод `whereUniqueKey` ищет не только по первичному ключу, но и по столбцам, хранящие уникальные значения. Такими столбцами могут выступать: 'email', 'phone', 'token' и др. 

Эти ключи можно определить непосредственно в сервисе с помощью одноименного поля

    <?php 

    namespace App\Services;

    use dmitryrogolev\Services\Service;

    class UserService extends Service 
    {
        /**
         * Столбцы таблицы, содержащие уникальные данные.
         *
         * @var array<int, string>
         */
        protected array $uniqueKeys = [];
    }

Или создав метод `uniqueKeys` в модели, который будет возвращать массив ключей.

    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Foundation\Auth\User as Model;

    class User extends Model
    {
        use HasFactory;

        /**
        * Возвращает столбцы, которые содержат уникальные данные.
        *
        * @return array<int, string>
        */
        public function uniqueKeys()
        {
            return [
                'email',
            ];
        }
    }

#### `all`

Возвращает все модели из таблицы в виде коллекции.

    $models = Service::all(); // Illuminate\Database\Eloquent\Collection

#### `create`

Создать модель и сохранить ее в таблицу.

    $attributes = [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
    ];
    $model = Service::create($attributes); // Illuminate\Database\Eloquent\Model

#### `createIfNotExists`

Создает модель только если ее не существует в таблице. Если запись с такими аттрибутами существует, возвращает `null`. Поиск записей осуществляется с помощью метода `where`.

    $attributes = [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
    ];
    $model = Service::createIfNotExists($attributes); // Illuminate\Database\Eloquent\Model

#### `createGroup`

Создает группу моделей и возвращает ее в виде коллекции.

    $group = [
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
    ];
    $models = Service::createGroup($group); // Illuminate\Database\Eloquent\Collection

#### `createGroupIfNotExists` 

Создает группу не существующих в таблице моделей и возвращает ее в виде коллекции. Поиск записей осуществляется с помощью метода `where`. Если все записи существуют, вернется пустая коллекция.

    $group = [
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
    ];
    $models = Service::createGroupIfNotExists($group); // Illuminate\Database\Eloquent\Collection

#### `createOrFirst`

Пытается создать запись. Если происходит нарушение ограничения уникальности, попытается найти соответствующую запись.

Первым параметром передаются аттрибуты, по которым будет вестись поиск записи, в случае ее существования. Вторым параметром передаются аттрибуты, которые нужно добавить к первым аттрибутам при попытке создания модели.

    $attributes = [
        'email' => 'admin@example.com',
    ];
    $values = [
        'name' => 'Admin',
        'password' => 'password',
    ];
    $model = Service::createOrFirst($attributes, $values); // Illuminate\Database\Eloquent\Model

#### `factory`

Возвращает экземпляр фабрики модели.

    $factory = Service::factory(); // Illuminate\Database\Eloquent\Factories\Factory

    $model = $factory->create();

#### `find`

Возвращает модель(-и) по ее(их) идентификатору(-ам).

При передачи одного идентификатора вернется одна модель.

    $model = Service::find(1); // Illuminate\Database\Eloquent\Model

При передачи массива идентификаторов вернется коллекция моделей.

    $models = Service::find([1, 2, 3]); // Illuminate\Database\Eloquent\Collection

#### `findMany`

Возвращает множество моделей по их идентификаторам.

    $models = Service::findMany([1, 2, 3]); // Illuminate\Database\Eloquent\Collection

#### `findManyOrFail` 

Возвращает коллекцию моделей по их идентификаторам или выбрасывает исключение. По умолчанию в случае отсутствия хотя бы одной записи из переданных идентификаторов, будет выброшено исключение.

    $models = Service::findManyOrFail([1, 2, 3]); // Illuminate\Database\Eloquent\Collection

Если передать `false` вторым параметром, исключение будет выброшено только тогда, когда не найдена ни одна запись из переданных идентификаторов.

    $models = Service::findManyOrFail([1, 2, 3], false); // Illuminate\Database\Eloquent\Collection

#### `findOr`

Возвращает модель по ее идентификатору или возвращает результат выполнения переданной функции.

    $model = Service::findOr($id, function () {
        abort(404);
    }); // Illuminate\Database\Eloquent\Model

#### `findOrFail`

Возвращает модель(-и) по ее(их) идентификатору(-ам) или выбрасывает исключение. По умолчанию в случае отсутствия хотя бы одной записи из переданных идентификаторов, будет выброшено исключение.

Если передать один идентификатор, вернется одна модель.

    $model = Service::findOrFail(1); // Illuminate\Database\Eloquent\Model

Если передать множество идентификаторов, вернется коллекция моделей.

    $models = Service::findOrFail([1, 2, 3]); // Illuminate\Database\Eloquent\Collection

Если передать `false` вторым параметром, исключение будет выброшено только тогда, когда не найдена ни одна запись из переданных идентификаторов.

    $models = Service::findOrFail([1, 2, 3], false); // Illuminate\Database\Eloquent\Collection

#### `findOrNew` 

Возвращает модель по ее идентификатору или создает новый пустой экземпляр модели.

    $model = Service::findOrNew(1); // Illuminate\Database\Eloquent\Model

#### `firstOrCreate`

Возвращает первую запись, соответствующую атрибутам. Если запись не найдена, создает ее.

Первым параметром передаются аттрибуты, по которым будет вестись поиск записи. Вторым параметром передаются аттрибуты, которые нужно добавить к первым аттрибутам при создании модели.

    $attributes = [
        'email' => fake()->unique()->email(),
    ];
    $values = [
        'name' => $user->name,
        'password' => $user->password,
    ];
    $model = Service::firstOrCreate($attributes, $values); // Illuminate\Database\Eloquent\Model

#### `firstOrNew`

Возвращает первую запись, соответствующую атрибутам, или создает ее экземпляр, не сохраняя ее в таблице.

Первым параметром передаются аттрибуты, по которым будет вестись поиск записи. Вторым параметром передаются аттрибуты, которые нужно добавить к первым аттрибутам при создании модели.

    $attributes = [
        'email' => fake()->unique()->email(),
    ];
    $values = [
        'name' => $user->name,
        'password' => $user->password,
    ];
    $model = Service::firstOrNew($attributes, $values); // Illuminate\Database\Eloquent\Model

#### `firstWhere`

Возвращает первую модель из коллекции, удовлетворяющую условию. Если запись не найдена, возвращает `null`.

Метод принимает те же параметры, что и метод [where](#where).

    $column = 'email';
    $operator = '=';
    $value = 'user@example.com';
    Service::firstWhere($column, $operator, $value); // Illuminate\Database\Eloquent\Model

#### `firstWhereUniqueKey`

Возвращает первую модель, имеющую переданный первичный ключ или уникальный ключ. Если запись не найдена, возвращает `null`.

Данный метод использует для поиска записей уникальные ключи. Смотрите [uniqueKeys](#uniquekeys).

Передаем идентификатор.

    $model = Service::firstWhereUniqueKey(1); // Illuminate\Database\Eloquent\Model

Передаем уникальный ключ.

    $model = Service::firstWhereUniqueKey('admin@example.com'); //Illuminate\Database\Eloquent\Model

Передаем множество идентификаторов.

    $model = Service::firstWhereUniqueKey([1, 2, 3]); // Illuminate\Database\Eloquent\Model

Передаем множество уникальных ключей.

    $keys = [
        'admin@example.com', 
        'user@example.com', 
    ];
    $model = Service::firstWhereUniqueKey($keys); // Illuminate\Database\Eloquent\Model

Передаем отсутствующий идентификатор.

    $model = Service::firstWhereUniqueKey(345345); // null

#### `generate`

Генерирует модели с помощью фабрики.

    $model = Service::generate(); // Illuminate\Database\Eloquent\Model

Первым параметром метод принимает аттрибуты для создания моделей, а вторым количество создаваемых моделей. При этом возвращается коллекция созданных моделей.

    $attributes = [
        'name' => fake()->name(),
    ];
    $count = 3;
    $models = Service::generate($attributes, $count); // Illuminate\Database\Eloquent\Collection

Количество моделей можно опустить, тогда вернется одна модель.

    attributes = [
        'name' => fake()->name(),
    ];
    $model = Service::generate($attributes); // Illuminate\Database\Eloquent\Model

Если нужно передать только количество создаваемых моделей без указания аттрибутов, можно передать их первым параметром.

    $count = 3;
    $models = Service::generate($count); // Illuminate\Database\Eloquent\Collection

По умолчанию, метод создает экземпляр модели и сохраняет его в таблице. Если вам нужно просто создать экземпляр, передайте методу `false`.

    $create = false;

    // Первый способ
    $model = Service::generate($create);

    // Второй способ
    $model = Service::generate($attributes_or_count, $create);

    // Третий способ
    $model = Service::generate($attributes, $count, $create);

#### `has`

Проверяет наличие модели в таблице по ее идентификатору или уникальному столбцу.

Данный метод использует для поиска записей уникальные ключи. Смотрите [uniqueKeys](#uniquekeys).

По умолчанию метод возвращает `false` только тогда, когда не найдена ни одна запись из переданных идентификаторов или уникальных ключей. 

    Service::has('admin@example.com'); // bool

    Service::has([1, 2, 3]); // bool

Если вторым параметром передать `true`, метод вернет `false` даже в том случае, если хотя бы один идентификатор или уникальный ключ не найден.

    Service::has([1, 2, 3], true); // bool

#### `hasAll`

Проверяет наличие всех моделей в таблице по их идентификаторам или уникальным столбцам.

Данный метод использует для поиска записей уникальные ключи. Смотрите [uniqueKeys](#uniquekeys).

Вернет `false` даже в том случае, если хотя бы один идентификатор или уникальный ключ не найден.

    Service::hasAll([
        'admin@example.com', 
        'user@example.com', 
        'moderator@example.com', 
    ]); // bool

    Service::hasAll([1, 2, 3]); // bool

#### `hasOne`

Проверяет наличие хотябы одной модели в таблице по ее идентификатору или уникальному ключу.

Данный метод использует для поиска записей уникальные ключи. Смотрите [uniqueKeys](#uniquekeys).

Возвращает `false` только тогда, когда не найдена ни одна запись из переданных идентификаторов или уникальных ключей. 

    Service::hasOne('admin@example.com'); // bool

    Service::hasOne([1, 2, 3]); // bool

#### `hasWhere`

Проверяет наличие записи в таблице по переданному условию.

Метод принимает те же параметры, что и метод [where](#where).

Возвращает `true`, если хотябы одна запись найдена.

    $column = 'email';
    $operator = '=';
    $value = 'user@example.com';
    Service::hasWhere($column, $operator, $value); // bool

#### `latest`

Возвращает самую позднюю по времени создания модель. Если таблица пустая, вернется `null`.

    $model = Service::latest(); // Illuminate\Database\Eloquent\Model

#### `make`

Создает экземпляр модели, но не сохраняет ее в таблицу.

Первым параметром принимает аттрибуты, с которыми нужно создать экземпляр модели.

    $attributes = [
        'name' => 'Admin',
        'email' => 'admin@admin.com',
        'password' => 'password',
    ];
    $model = Service::make($attributes); // Illuminate\Database\Eloquent\Model

#### `makeGroup`

Создает группу экземпляров моделей, но не сохраняет их в таблицу.

    $group = [
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
    ];
    $models = Service::makeGroup($group); // Illuminate\Database\Eloquent\Collection

#### `makeGroupIfNotExists`

Создать группу не существующих в таблице экземпляров моделей.

    $group = [
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
    ];
    $models = Service::makeGroup($group); // Illuminate\Database\Eloquent\Collection

#### `makeIfNotExists`

Создает экземпляр модели, только если она не существует в таблице. Если модель существует, возвращает `null`.
    
    $attributes = [
        'name' => 'Admin',
        'email' => 'admin@admin.com',
        'password' => 'password',
    ];
    $model = Service::makeIfNotExists($attributes); // Illuminate\Database\Eloquent\Model

#### `oldest`

Возвращает самую раннюю по времени создания модель. Если таблица пустая, вернется `null`.

    $model = Service::oldest(); // Illuminate\Database\Eloquent\Model

#### `query`

Возвращает построитель SQL запросов.

    $builder = Service::query(); // Illuminate\Database\Eloquent\Builder

#### `random`

Возвращает случайную модель из таблицы.

    $model = Service::random(); // Illuminate\Database\Eloquent\Model

#### `seed`

Запускает сидер модели.

Перед использованием, вы должны убедиться, что вы передали сервису имя класса сидера модели. Смотрите [Указание сидера](#указание-сидера-не-обязательно).

    Service::seed();

#### `truncate`

Очищает таблицу.

    Service::truncate();

#### `updateOrCreate`

Создает или обновляет запись, соответствующую атрибутам, и заполняет ее значениями.

Первым параметром метод принимает аттрибуты, по которым необходимо вести поиск. Вторым параметром передаются аттрибуты, которые необходимо добавить к первым при создании модели.

Если запись найдена, она будет изменена аттрибутами из второго параметра.

    $attributes = [
        'email' => fake()->unique()->email(),
    ];
    $values = [
        'name' => 'Admin',
        'password' => 'password',
    ];
    $model = Service::updateOrCreate($attributes, $values); // Illuminate\Database\Eloquent\Model

#### `where`

Возвращает коллекцию моделей по столбцу.

Первым параметром принимается столбец, по которому необходимо вести поиск. Вторым - оператор сравнения. Третьим - искомое значение столбца.

    $model = Service::where('email', '=', 'admin@example.com'); // Illuminate\Database\Eloquent\Collection

Оператор '`=`' можно опустить, он будет назначен по умолчанию.

    $model = Service::where('email', 'admin@example.com'); // Illuminate\Database\Eloquent\Collection

Также вместо столбца можно передать массив двух видов: 

1. Массив вида ключ-значение, где ключ - это столбец, а значение - значение этого столбца.

        $column = ['email' => 'admin@example.com'];
        $model = Service::where($column); // Illuminate\Database\Eloquent\Collection

2. Массив, состоящий из массивов, содержащих столбец, оператор и значение.

        $column = [
            ['email', '=', 'admin@example.com'], 
        ];
        $model = Service::where($column); // Illuminate\Database\Eloquent\Collection

#### `whereKey`

Возвращает коллекцию моделей по их идентификаторам.

    $models = Service::whereKey(1); // Illuminate\Database\Eloquent\Collection
    
    $models = Service::whereKey([1, 2, 3]); // Illuminate\Database\Eloquent\Collection

#### `whereKeyNot` 

Возвращает коллекцию всех моделей, за исключением тех, которые имеют переданные идентификаторы.

    $models = Service::whereKeyNot(1); // Illuminate\Database\Eloquent\Collection
    
    $models = Service::whereKeyNot([1, 2, 3]); // Illuminate\Database\Eloquent\Collection

#### `whereNot`

Возвращает коллекцию, которая не удовлетворяет условию.

Метод принимает те же параметры, что и метод [where](#where).

    $column = 'email';
    $operator = '=';
    $value = 'user@example.com';
    Service::whereNot($column, $operator, $value); // Illuminate\Database\Eloquent\Collection


