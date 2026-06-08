<?php

namespace Core\Services\Entity;

use Exception;

abstract class AbstractEntityService
{
    protected $entity = null;

    /**
     * Установка сущности сервиса.
     *
     * @param $entity
     * @return $this
     */
    public function setEntity($entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Получение сущности сервиса.
     *
     * @return object
     * @throws Exception
     */
    protected function getEntity(): object
    {
        if (is_null($this->entity)) {
            throw new Exception('Entity not add');
        }

        return $this->entity;
    }
}