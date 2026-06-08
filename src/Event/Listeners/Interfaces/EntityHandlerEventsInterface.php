<?php

namespace Core\Event\Listeners\Interfaces;

use Core\Event\Listeners\Rest\Events\RestAfterCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterUpdatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeUpdatedEvent;

interface EntityHandlerEventsInterface
{
    /**
     * Пользовательское событие до создания сущности.
     *
     * @param RestBeforeCreatedEvent $event
     * @return void
     */
    public function eventEntityBeforeCreated(RestBeforeCreatedEvent $event): void;

    /**
     * Пользовательское событие после создания сущности.
     *
     * @param RestAfterCreatedEvent $event
     * @return void
     */
    public function eventEntityAfterCreated(RestAfterCreatedEvent $event): void;

    /**
     * Пользовательское событие до обновления сущности.
     *
     * @param RestBeforeUpdatedEvent $event
     * @return void
     */
    public function eventEntityBeforeUpdated(RestBeforeUpdatedEvent $event): void;

    /**
     * Пользовательское событие после обновления сущности.
     *
     * @param RestAfterUpdatedEvent $event
     * @return void
     */
    public function eventEntityAfterUpdated(RestAfterUpdatedEvent $event): void;

    /**
     * Пользовательское событие до удаления сущности.
     *
     * @param RestBeforeDeletedEvent $event
     * @return void
     */
    public function eventEntityBeforeDeleted(RestBeforeDeletedEvent $event): void;

    /**
     * Пользовательское событие после удаления сущности.
     *
     * @param RestAfterDeletedEvent $event
     * @return void
     */
    public function eventEntityAfterDeleted(RestAfterDeletedEvent $event): void;
}