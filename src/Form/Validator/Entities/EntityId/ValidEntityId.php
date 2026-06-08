<?php

namespace Core\Form\Validator\Entities\EntityId;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidEntityId extends Constraint
{
    public string $message = 'Сущность с идентификатором "{{ id }}" не существует.';
    public string $entityClass;

    public function getRequiredOptions(): array
    {
        return ['entityClass'];
    }

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }
}