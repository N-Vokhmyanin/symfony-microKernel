<?php

namespace Core\Services\Helpers\Patterns;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class BaseDto
 * Базовый класс реализации DTO объектов.
 */
abstract class BaseDto
{
    /**
     * @throws ReflectionException
     */
    public function __construct(array $params = [])
    {
        $refClass = new ReflectionClass(static::class);
        foreach ($refClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $property = $reflectionProperty->getName();

            $type = $reflectionProperty->getType();

            $typeProperty = @$type->getName();
            if (isset($params[$property])) {
                if (!empty($typeProperty)) {
                    if ($type->isBuiltin()) {
                        $this->{$property} = $params[$property];
                    } else {
                        $this->{$property} = new $typeProperty($params[$property]);
                    }
                } else {
                    $this->{$property} = $params[$property];
                }
            } else {
                if ($type->allowsNull()) {
                    $this->{$property} = null;
                } else {
                    throw new ReflectionException(sprintf('Undefined property: %s', $property));
                }
            }
        }
    }

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
            $data[$name] = $value instanceof BaseDto ? $value->toArray() : $value;
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