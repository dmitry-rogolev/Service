<?php

namespace dmitryrogolev\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис для работы с ресурсом.
 */
trait Resourcable
{
    /**
     * Возвращает коллекцию всех моделей таблицы.
     */
    public function index(): Collection
    {
        return $this->all();
    }

    /**
     * Создает модель с переданными аттрибутами.
     */
    public function store(Arrayable|array $attributes): Model
    {
        return $this->create($attributes);
    }

    /**
     * Возвращает модель по ее идентификатору.
     */
    public function show(Model|string|int $id): ?Model
    {
        if ($id instanceof Model) {
            return $id;
        }

        return $this->find($id);
    }

    /**
     * Обновляет модель переданными аттрибутами.
     */
    public function update(Model|string|int $model, Arrayable|array $attributes): Model
    {
        return tap($this->show($model), function ($model) use ($attributes) {
            $model->fill(
                $this->arrayableToArray($attributes)
            );
            $model->save();
        });
    }

    /**
     * Удаляет модель из таблицы или программно удаляет модель.
     */
    public function destroy(Model|string|int $model): void
    {
        $model = $this->show($model);

        $model->delete();
    }

    /**
     * Восстанавливает программно удаленную модель.
     */
    public function restore(Model|string|int $id): Model
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        return tap($this->model::onlyTrashed()->find($id), function ($model) {
            $model->restore();
        });
    }

    /**
     * Удаляет модель из таблицы.
     */
    public function forceDestroy(Model|string|int $model): void
    {
        $model = $this->show($model);

        $model->forceDelete();
    }
}
