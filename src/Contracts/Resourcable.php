<?php

namespace dmitryrogolev\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Сервис для работы с ресурсом.
 */
interface Resourcable
{
    /**
     * Возвращает результат для метода ресурсного контроллера "index".
     */
    public function index(): Collection;

    /**
     * Возвращает результат для метода ресурсного контроллера "show".
     *
     * @param  mixed  $id
     */
    public function show($id): ?Model;
}
