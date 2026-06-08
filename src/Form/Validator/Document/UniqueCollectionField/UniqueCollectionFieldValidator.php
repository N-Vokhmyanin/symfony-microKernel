<?php

namespace Core\Form\Validator\Document\UniqueCollectionField;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueCollectionFieldValidator extends ConstraintValidator
{
    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueCollectionField) {
            throw new UnexpectedTypeException($constraint, UniqueCollectionField::class);
        }

        if (empty($value)) {
            return;
        }

        if (!is_string($value) && !is_numeric($value)) {
            throw new UnexpectedValueException($value, 'строка или число');
        }

        $repository = $this->documentManager->getRepository($constraint->entityClass);
        $queryBuilder = $repository->createQueryBuilder();

        $existingEntity = $queryBuilder
            ->field($constraint->collection)->elemMatch(
                $queryBuilder->expr()->field($constraint->field)->equals($value)
            )
            ->getQuery()
            ->getSingleResult();

        if ($existingEntity) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}