<?php

namespace Core\Form\Validator\Document\UniqueField;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueField extends Constraint
{
    public string $message = 'Значение "{{ value }}" уже используется.';

    public string $field;
    public string $entityClass;

    public function getRequiredOptions(): array
    {
        return ['field', 'entityClass'];
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