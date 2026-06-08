<?php

namespace Core\Event\Listeners\Rest\Events;

use Core\Traits\Event\RestEventTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class RestBeforeUpdatedEvent extends Event implements RestEventInterface
{
    use RestEventTrait;

    public const EVENT_NAME = 'rest.entity.before.updated';

    private Request $request;

    public function __construct($entity, Request $request)
    {
        $this->entity = $entity;
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}