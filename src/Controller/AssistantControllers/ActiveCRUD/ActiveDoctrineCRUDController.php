<?php

namespace Core\Controller\AssistantControllers\ActiveCRUD;

use Doctrine\Persistence\ManagerRegistry;
use Core\Controller\AssistantControllers\AbstractApiController;
use Core\Traits\Controller\RestCRUDTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ActiveDoctrineCRUDController extends AbstractApiController
{
    use RestCRUDTrait;

    /**
     * @throws \Exception
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ContainerInterface $container,
        ManagerRegistry $managerRegistry
    ) {
        if (is_null($this->entityClass) || is_null($this->typeClass)) {
            throw new \Exception('Не заданны переменные "entityClass" или "typeClass".');
        }

        $this->objectManager = $managerRegistry->getManager();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->entityRepository = $managerRegistry->getRepository($this->entityClass);

        parent::__construct($dispatcher, $container);
    }
}