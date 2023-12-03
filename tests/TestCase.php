<?php

namespace dmitryrogolev\Service\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Количество выполненных запросов к БД.
     *
     * @var integer
     */
    protected int $queryExecutedCount = 0;

    public function setUp(): void 
    {
        parent::setUp();

        $this->registerListeners();
    }

    /**
     * Возвращает сгенерированную с помощью фабрики модель.
     */
    protected function generate(string $class, array|int|bool $count = null, array|bool $state = [], bool $create = true): Model|Collection
    {
        if (is_array($count)) {
            $state = $count;
            $count = null;
        }

        if (is_bool($count)) {
            $create = $count;
            $count = null;
        }

        if (is_bool($state)) {
            $create = $state;
            $state = [];
        }

        $factory = $class::factory($count, $state);

        return $create ? $factory->create() : $factory->make();
    }

    /**
     * Зарегистрировать слушатели событий.
     *
     * @return void
     */
    protected function registerListeners(): void 
    {
        DB::listen(fn () => $this->queryExecutedCount++);
    }

    /**
     * Сбросить количество выполненных запросов к БД.
     *
     * @return void
     */
    protected function resetQueryExecutedCount(): void 
    {
        $this->queryExecutedCount = 0;
    }

    /**
     * Подтвердить количество выполненных запросов к БД.
     */
    protected function assertQueryExecutedCount(int $expectedCount, string|null $message = ''): void 
    {
        $this->assertEquals($expectedCount, $this->queryExecutedCount, $message);
    }
}
