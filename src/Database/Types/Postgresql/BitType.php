<?php

namespace LaravelAdminPanel\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use LaravelAdminPanel\Database\Types\Type;

class BitType extends Type
{
    const NAME = 'bit';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        $length = empty($field['length']) ? 1 : $field['length'];

        return "bit({$length})";
    }
}
