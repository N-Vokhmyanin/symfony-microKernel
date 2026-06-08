<?php

namespace Core\Doctrine\Types\Enum;

use Core\Doctrine\DBAL\Types\AbstractEnumType;

class UsersRoleEnumType extends AbstractEnumType
{
    protected string $name = 'usersRoleEnumType';
    protected array $values = ['user', 'admin', 'support'];
}