# Service 

Сервис работы с моделью Eloquent Laravel.

Сервис добавляет новый уровень абстракции между контроллером и моделью. Вместо того, чтобы проводить операции с моделью в контроллере, вы выносите эту логику в сервис, тем самым улучшая читабельность кода и тем самым следуя принципу единственной ответственности.

## Содержание 



## Подключение 

Добавьте ссылку на репозиторий в файл `composer.json`.

    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:dmitry-rogolev/Helper.git"
        }
    ]

Подключите пакет с помощью команды:

    composer require dmitryrogolev/service

## Создание сервиса

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

Теперь, когда класс нашего сервиса создан и мы указали с какой моделью он работает, нам необходимо вынести логику работы с нашей моделью из контроллера в только что созданный специально для этого сервис.

Допустим, у нас есть контроллер `App\Http\Controllers\UserController`, в котором есть метод `update`, обновляющий данные пользователя.

    <?php 

    namespace App\Http\Controllers;

    use App\Models\User;

    class UserController extends Controller 
    {
        public function update(Request $request, User $user)
        {
            
        }
    }

## Использование 

