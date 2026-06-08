<?php

namespace Core\Traits\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ThisTrait adds deletedAt field to entity.
 */
trait AutoDeletedAtTrait
{
    /**
     * @var \DateTimeInterface|null $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * @return \DateTimeInterface|null
     */
    protected function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    protected function setDeletedAt(): self
    {
        $this->deletedAt = new \DateTime();
        return $this;
    }

    public function softDelete(): self
    {
        return $this->setDeletedAt();
    }

    public function restore(): self
    {
        $this->deletedAt = null;
        return $this;
    }

    public function isSoftDeleted(): bool
    {
        return !is_null($this->deletedAt);
    }
}