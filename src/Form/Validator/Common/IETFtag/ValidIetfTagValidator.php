<?php

namespace Core\Form\Validator\Common\IETFtag;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidIetfTagValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint ValidIetfTag */
        if (is_null($value) || '' === $value) {
            return;
        }

        // Проверка формата на соответствие IETF BCP 47
        if (!preg_match('/^[a-zA-Z]{2,3}(-[a-zA-Z]{4})?(-[a-zA-Z]{2}|\d{3})?(-[a-zA-Z]{5,8})?$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}