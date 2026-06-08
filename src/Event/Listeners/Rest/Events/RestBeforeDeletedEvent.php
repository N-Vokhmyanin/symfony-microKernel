<?php

namespace Core\Event\Listeners\Rest\Events;

use Core\Traits\Event\RestEventTrait;
use Symfony\Contracts\EventDispatcher\Event;

class RestBeforeDeletedEvent extends Event implements RestEventInterface
{
    use RestEventTrait;

    public const EVENT_NAME = 'rest.entity.before.deleted';

    private $id;

    public function __construct($entity, $id)
    {
        $this->entity = $entity;
        $this->id = $id;
    }

    public function getRequestId()
    {
        return $this->id;
    }
}