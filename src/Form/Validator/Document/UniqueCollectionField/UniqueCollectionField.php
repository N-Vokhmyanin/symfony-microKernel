<?php

namespace Core\Form\Validator\Document\UniqueCollectionField;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueCollectionField extends Constraint
{
    public string $message = 'Значение "{{ value }}" уже используется.';

    public string $field;
    public string $collection;
    public string $entityClass;

    public function getRequiredOptions(): array
    {
        return ['field', 'collection', 'entityClass'];
    }

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }
}