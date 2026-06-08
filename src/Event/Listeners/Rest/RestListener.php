<?php

namespace Core\Event\Listeners\Rest;

use Core\Event\Listeners\Interfaces\EntityHandlerEventsInterface;
use Core\Event\Listeners\Interfaces\EntityHandlerInterface;
use Core\Event\Listeners\Rest\Events\RestAfterCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestAfterUpdatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeCreatedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeDeletedEvent;
use Core\Event\Listeners\Rest\Events\RestBeforeUpdatedEvent;
use Core\Event\Listeners\Rest\Events\RestEventInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RestListener
 * Класс, слушатель пользовательских событий.
 * Служит звеном пересылающим все события в хелперы сущностей с неймспейсом Core\Event\Handlers\Rest
 *
 * @warning Изменения не вносить, только если добавлять новые хелперы для событий.
 */
class RestListener
{
    private array $comparisonList;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->comparisonList = $container->getParameter('rest_listener_comparison_List');
        $this->container = $container;
    }

    /**
     * Пользовательское событие после создания сущности.
     *
     * @param RestBeforeCreatedEvent $event
     * @return void
     * @throws \Exception
     */
    public function onEntityBeforeCreated(RestBeforeCreatedEvent $event): void
    {
        $this->getEntityHandler($event, function (EntityHandlerEventsInterface $restHandler) use ($event) {
            $restHandler->eventEntityBeforeCreated($event);
        });
    }

    /**
     * Пользовательское событие после создания сущности.
     *
     * @param RestAfterCreatedEvent $event
     * @return void
     * @throws \Exception
     */
    public function onEntityAfterCreated(RestAfterCreatedEvent $event): void
    {
       $this->getEntityHandler($event, function (EntityHandlerEventsInterface $restHandler) use ($event) {
            $restHandler->eventEntityAfterCreated($event);
        });
    }

    /**
     * Пользовательское событие до обновления сущности.
     *
     * @param RestBeforeUpdatedEvent $event
     * @return void
     * @throws \Exception
     */
    public function onEntityBeforeUpdated(RestBeforeUpdatedEvent $event): void
    {
        $this->getEntityHandler($event, function (EntityHandlerEventsInterface $restHandler) use ($event) {
            $restHandler->eventEntityBeforeUpdated($event);
        });
    }

    /**
     * Пользовательское событие после обновления сущности.
     *
     * @param RestAfterUpdatedEvent $event
     * @return void
     * @throws \Exception
     */
    public function onEntityAfterUpdated(RestAfterUpdatedEvent $event): void
    {
        $this->getEntityHandler($event, function (EntityHandlerEventsInterface $restHandler) use ($event) {
            $restHandler->eventEntityAfterUpdated($event);
        });
    }

    /**
     * Пользовательское событие после удаления сущности.
     *
     * @param RestBeforeDeletedEvent $event
     * @return void
     * @throws \Exception
     */
    public function onEntityBeforeDeleted(RestBeforeDeletedEvent $event): void
    {
        $this->getEntityHandler($event, function (EntityHandlerEventsInterface $restHandler) use ($event) {
            $restHandler->eventEntityBeforeDeleted($event);
        });
    }

    /**
     * Пользовательское событие после удаления сущности.
     *
     * @param RestAfterDeletedEvent $event
     * @return void
     * @throws \Exception
     */
    public function onEntityAfterDeleted(RestAfterDeletedEvent $event): void
    {
        $this->getEntityHandler($event, function (EntityHandlerEventsInterface $restHandler) use ($event) {
            $restHandler->eventEntityAfterDeleted($event);
        });
    }

    /**
     * Получение класса хендлера для обработки события.
     *
     * @param RestEventInterface $event
     * @param callable $restHandler
     * @return void
     * @throws \Exception
     */
    private function getEntityHandler(RestEventInterface $event, callable $restHandler): void
    {
        $entityClassName = $event->getEntityClassName();
        if (array_key_exists($entityClassName, $this->comparisonList)) {
            $handler = $this->comparisonList[$entityClassName];
            $handlerInstance = new $handler();
            if ($handlerInstance instanceof EntityHandlerInterface
                && $handlerInstance instanceof EntityHandlerEventsInterface) {
                $handlerInstance->setEntity($event->getEntity());
                $handlerInstance->setContainer($this->container);

                $restHandler($handlerInstance);
                return;
            }

            throw new \Exception(sprintf('Handler class %s not follow the interface RestHandlerInterface', $entityClassName));
        }
    }
}