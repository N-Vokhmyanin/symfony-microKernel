<?php

namespace Core\Doctrine\EventListeners;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class LifecycleEventListener
 *
 * Класс подписчик на события ORM.
 */
class LifecycleEventListener
{
    private ?UserInterface $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    /**
     * Слушатель события перед созданием сущности.
     *
     * @param PrePersistEventArgs $args
     * @return void
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->wrapperCheckFieldEvents($args, 'createdBy', function ($entity) {
            if (!is_null($this->user)) {
                $entity->setCreatedBy($this->user);
            }
        });

        $this->wrapperCheckFieldEvents($args, 'updatedBy', function ($entity) {
            if (!is_null($this->user)) {
                $entity->setUpdatedBy($this->user);
            }
        });
    }

    /**
     * Слушатель события перед обновлением сущности.
     *
     * @param PreUpdateEventArgs $args
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->wrapperCheckFieldEvents($args, 'updatedBy', function ($entity) {
            if (!is_null($this->user)) {
                $entity->setUpdatedBy($this->user);
            }
        });
    }

    /**
     * Слушатель события перед удалением сущности.
     *
     * @param PreRemoveEventArgs $args
     * @return void
     */
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $this->wrapperCheckFieldEvents($args, 'deletedAt', function ($entity) use ($args) {
            if (!is_null($this->user)) {
                $entity->softDelete($this->user);

                $om = $args->getObjectManager();
                $om->persist($entity);
                $om->flush();

                // Прерываем выполнение метода удаления
                $om->getUnitOfWork()->detach($entity);
            }
        });
    }

    /**
     * Обертка над проверкой поля сущности и работа с ней.
     *
     * @param LifecycleEventArgs $args
     * @param string $hasFieldName - Описанное поле сущности.
     * @param callable $entityCallable - Функция обертка для действий над сущностью прошедшей проверку @example fn(object $entity) => {$entity->getId()}.
     * @return void
     */
    private function wrapperCheckFieldEvents(LifecycleEventArgs $args, string $hasFieldName, callable $entityCallable): void
    {
        $entity = $args->getObject();
        $classMetadata = $args->getObjectManager()->getClassMetadata(get_class($entity));
        if ($classMetadata->hasField($hasFieldName) || $classMetadata->hasAssociation($hasFieldName)) {
            $entityCallable($entity);
        }
    }
}