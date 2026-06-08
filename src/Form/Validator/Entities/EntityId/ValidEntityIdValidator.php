<?php

namespace Core\Form\Validator\Entities\EntityId;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidEntityIdValidator extends ConstraintValidator
{
    private ManagerRegistry $entityManager;

    public function __construct(ManagerRegistry $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint ValidEntityId */
        if (is_null($value) || $value === '') {
            return;
        }

        $entity = $this->entityManager->getRepository($constraint->entityClass)->find($value);
        if (is_null($entity)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ id }}', $value)
                ->addViolation();
        }
    }
}