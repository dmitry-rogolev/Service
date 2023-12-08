# Service 

Сервис работы с моделью Eloquent Laravel.

Сервис добавляет новый уровень абстракции между контроллером и моделью. Вместо того, чтобы проводить операции с моделью в контроллере, вы выносите эту логику в сервис, тем самым улучшая читабельность кода и тем самым следуя принципу единственной ответственности.

## Содержание 

1. [Подключение](#подключение)
2. [Создание сервиса](#создание-сервиса)
    
    - [Создание класса](#создание-класса)
    - [Вынесение логики из контроллера](#вынесение-логики-из-контроллера)
    - [Ресурсный сервис](#ресурсный-сервис)
    - [Создание фасада](#создание-фасада)

3. [Доступные методы](#доступные-методы)

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

