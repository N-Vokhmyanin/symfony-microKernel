<?php

namespace Core\Event\Handlers\Rest;

use Core\Event\Handlers\AbstractEntityHandler;
use Core\Event\Listeners\Interfaces\EntityHandlerEventsInterface;
use Core\Event\Listeners\Rest\Events\RestAfterCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterUpdatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeDeletedEvent;
use Core\Services\Example\ExampleService;
use Core\Services\Api\Example\DTO\Request\MessagesRequestDTO;

/**
 * @method ExampleDocument getEntity()
 */
class ExampleDocumentEvents extends AbstractEntityHandler implements EntityHandlerEventsInterface
{
    /**
     * @throws \Exception
     */
    public function eventEntityBeforeCreated(RestBeforeCreatedEvent $event): void
    {
        $this->getExampleService()->newEntityPrepare();
    }

    /**
     * @throws \Exception
     */
    public function eventEntityAfterCreated(RestAfterCreatedEvent $event): void
    {
        $this->getExampleService()->setWebhook();
    }

    public function eventEntityAfterUpdated(RestAfterUpdatedEvent $event): void
    {
    }

    /**
     * @throws \Exception
     */
    public function eventEntityBeforeDeleted(RestBeforeDeletedEvent $event): void
    {
        $this->getEntity()->setStatus(MessagesRequestDTO::MESSAGE_TYPE_UNDELIVERED);
    }

    /**
     * @throws \Exception
     */
    public function eventEntityAfterDeleted(RestAfterDeletedEvent $event): void
    {
        $this->getExampleService()->deleteWebhook();
    }

    /**
     * Получение сервиса каналов с предустановленной сущностью.
     *
     * @return ExampleService
     */
    private function getExampleService(): ExampleService
    {
        return $this->getContainer()->get(ExampleService::class)->setEntity($this->getEntity());
    }
}