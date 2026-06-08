<?php

namespace Core\Event\Listeners\Rest\Events;

interface RestEventInterface
{
    /**
     * Получение модели сущности.
     *
     * @return object
     */
    public function getEntity(): object;

    /**
     * Получение пути класса сущности.
     *
     * @return string
     */
    public function getEntityClassName(): string;
}