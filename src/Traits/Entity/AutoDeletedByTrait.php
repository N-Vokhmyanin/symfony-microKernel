<?php

namespace Core\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;
use Core\Entity\Users;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ThisTrait adds deletedBy field to entity.
 */
trait AutoDeletedByTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Core\Entity\Users")
     * @ORM\JoinColumn(name="deleted_by", referencedColumnName="user_id", nullable=true)
     */
    protected $deletedBy;

    protected function getDeletedBy(): ?Users
    {
        return $this->deletedBy;
    }

    protected function setDeletedBy(UserInterface $user): self
    {
        $this->deletedBy = $user;
        return $this;
    }
}