<?php

namespace dmitryrogolev\Service\Tests\Models;

use dmitryrogolev\Service\Tests\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Model;

/**
 * Модель пользователя.
 */
class User extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * Таблица БД, ассоциированная с моделью.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Атрибуты, для которых разрешено массовое присвоение значений.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
    ];

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

    /**
     * Создайте новый экземпляр фабрики для модели.
     *
     * @return \dmitryrogolev\Service\Tests\Database\Factories\UserFactory<static>
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
