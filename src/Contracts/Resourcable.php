<?php

namespace dmitryrogolev\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис для работы с ресурсом.
 */
interface Resourcable
{
    /**
     * Возвращает коллекцию всех моделей таблицы.
     */
    public function index(): Collection;

    /**
     * Создает модель с переданными аттрибутами.
     */
    public function store(Arrayable|array $attributes): Model;

    /**
     * Возвращает модель по ее идентификатору.
     */
    public function show(Model|string|int $id): ?Model;

    /**
     * Обновляет модель переданными аттрибутами.
     */
    public function update(Model|string|int $model, Arrayable|array $attributes): Model;

    /**
     * Удаляет модель из таблицы или программно удаляет модель.
     */
    public function destroy(Model|string|int $model): void;

    /**
     * Восстанавливает программно удаленную модель.
     */
    public function restore(Model|string|int $id): Model;

    /**
     * Удаляет модель из таблицы.
     */
    public function forceDestroy(Model|string|int $model): void;
}
