<?php

namespace Core\Event\Listeners\Interfaces;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface EntityHandlerInterface
{
    /**
     * Установка класса сущности.
     *
     * @param $entity
     * @return void
     */
    public function setEntity($entity): void;

    /**
     * Установка контейнера.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container): void;
}