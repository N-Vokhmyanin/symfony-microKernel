<?php

namespace Core\Traits\Event;

trait RestEventTrait
{
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getEntityClassName(): string
    {
        return get_class($this->entity);
    }
}