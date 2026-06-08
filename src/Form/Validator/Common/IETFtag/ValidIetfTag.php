<?php

namespace Core\Form\Validator\Common\IETFtag;

use Symfony\Component\Validator\Constraint;

class ValidIetfTag extends Constraint
{
    public string $message = 'Значение «{{ value }}» не является допустимым тегом IETF.';

    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }
}