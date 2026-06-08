<?php

namespace Core\Event\Handlers;

use Core\Event\Listeners\Interfaces\EntityHandlerInterface;
use Core\Traits\Event\HandlerEventTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractEntityHandler implements EntityHandlerInterface
{
    use HandlerEventTrait;

    protected $entity;
    protected ContainerInterface $container;

    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }

    protected function getEntity(): object
    {
        return $this->entity;
    }


    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}