<?php

namespace Core\Traits\Event;

use Core\Event\Listeners\Rest\Events\RestAfterCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterUpdatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeUpdatedEvent;

/**
 * @class HandlerEventTrait
 * Трейт реализующий пустые основные методы событий сущности.
 * Методы переопределяются в классе наследнике.
 */
trait HandlerEventTrait
{
    public function eventEntityAfterCreated(RestAfterCreatedEvent $event): void
    {
    }

    public function eventEntityAfterUpdated(RestAfterUpdatedEvent $event): void
    {
    }

    public function eventEntityAfterDeleted(RestAfterDeletedEvent $event): void
    {
    }

    public function eventEntityBeforeCreated(RestBeforeCreatedEvent $event): void
    {
    }

    public function eventEntityBeforeUpdated(RestBeforeUpdatedEvent $event): void
    {
    }

    public function eventEntityBeforeDeleted(RestBeforeDeletedEvent $event): void
    {
    }
}