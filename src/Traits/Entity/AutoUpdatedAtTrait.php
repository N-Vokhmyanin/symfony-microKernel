<?php

namespace Core\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ThisTrait adds updatedAt field to entity.
 */
trait AutoUpdatedAtTrait
{
    /**
     * @var \DateTimeInterface $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @return \DateTimeInterface
     */
    protected function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();
        return $this;
    }
}