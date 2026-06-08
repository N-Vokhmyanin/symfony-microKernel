<?php

namespace Core\Traits\Entity;

/**
 * ThisTrait adds created{At/By} field to entity.
 */
trait AutoCreatedTrait
{
    use AutoCreatedAtTrait, AutoCreatedByTrait;
}