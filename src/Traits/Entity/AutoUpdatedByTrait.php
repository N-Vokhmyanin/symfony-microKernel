<?php

namespace Core\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;
use Core\Entity\Users;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ThisTrait adds updatedBy field to entity.
 */
trait AutoUpdatedByTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Core\Entity\Users")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="user_id", nullable=true)
     */
    public $updatedBy;

    public function getUpdatedBy(): ?Users
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(UserInterface $user): self
    {
        $this->updatedBy = $user;
        return $this;
    }
}