<?php

namespace Core\Traits\Entity;

/**
 * ThisTrait adds updated{At/By} field to entity.
 */
trait AutoUpdatedTrait
{
    use AutoUpdatedAtTrait, AutoUpdatedByTrait;
}