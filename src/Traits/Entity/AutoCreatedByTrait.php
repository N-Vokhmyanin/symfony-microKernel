<?php

namespace Core\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ThisTrait adds createdBy field to entity.
 */
trait AutoCreatedByTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Core\Entity\Users")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="user_id")
     */
    protected $createdBy;

    protected function getCreatedBy(): UserInterface
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserInterface $user): self
    {
        $this->createdBy = $user;
        return $this;
    }
}