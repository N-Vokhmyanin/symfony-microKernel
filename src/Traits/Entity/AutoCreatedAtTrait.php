<?php

namespace Core\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ThisTrait adds createdAt field to entity.
 */
trait AutoCreatedAtTrait
{
    /**
     * @var \DateTimeInterface $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @return \DateTimeInterface
     */
    protected function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();
        return $this;
    }
}