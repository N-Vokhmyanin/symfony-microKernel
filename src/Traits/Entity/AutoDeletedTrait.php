<?php

namespace Core\Traits\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ThisTrait adds deleted{By/At} field to entity.
 */
trait AutoDeletedTrait
{
    use AutoDeletedAtTrait, AutoDeletedByTrait;

    public function softDelete(UserInterface $user): self
    {
        $this->setDeletedAt();
        $this->setDeletedBy($user);
        return $this;
    }

    public function restore(): self
    {
        $this->deletedAt = null;
        $this->deletedBy = null;

        return $this;
    }

    public function isSoftDeleted(): bool
    {
        return !is_null($this->deletedAt);
    }
}