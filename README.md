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

### Подробное описание

#### `getModel`

Возвращает имя модели сервиса.

    $modelClass = Service::getModel(); 
    $model = new $model;

#### `getSeeder` 

Возвращает имя сидера модели.

    $seederClass = Service::getSeeder();
    $seeder = new $seederClass;

#### `getFactory`

Возвращает имя фабрики модели.

    $factoryClass = Service::getFactory();
    $factory = new $factoryClass;

#### `uniqueKeys`

Возвращает столбцы таблицы, содержащие уникальные данные в виде массива.

    $uniqueKeys = Service::uniqueKeys();

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

Возвращает все модели из таблицы в виде коллекции `Illuminate\Database\Eloquent\Collection`.

    $models = Service::all();

#### `create`

Создать модель и сохранить ее в таблицу.

    $attributes = [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
    ];
    $model = Service::create($attributes);

#### `createIfNotExists`

Создает модель только если ее не существует в таблице. Если запись с такими аттрибутами существует, возвращает `null`. Поиск записей осуществляется с помощью метода `where`.

    $attributes = [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
    ];
    $model = Service::createIfNotExists($attributes);

#### `createGroup`

Создает группу моделей и возвращает ее в виде коллекции `Illuminate\Database\Eloquent\Collection`.

    $group = [
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
    ];
    $models = Service::createGroup($group);

#### `createGroupIfNotExists` 

Создает группу не существующих в таблице моделей и возвращает ее в виде коллекции `Illuminate\Database\Eloquent\Collection`. Поиск записей осуществляется с помощью метода `where`. Если все записи существуют, вернется пустая коллекция.

    $group = [
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
        ['name' => fake()->name(), 'email' => fake()->unique()->email(), 'password' => 'password'],
    ];
    $models = Service::createGroupIfNotExists($group);

