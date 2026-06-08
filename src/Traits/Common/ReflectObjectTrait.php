<?php

namespace Core\Traits\Common;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

trait ReflectObjectTrait
{
     /**
     * Получить ассоциативный массив.
     *
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $refClass = new ReflectionClass(static::class);

        $data = [];
        foreach ($refClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $value = $this->{$property->getName()};
            $data[$name] = $value;
        }

        return $data;
    }

    /**
     * Получить JSON.
     *
     * @return string
     * @throws ReflectionException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}