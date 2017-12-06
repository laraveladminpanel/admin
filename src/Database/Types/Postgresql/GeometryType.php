<?php

namespace LaravelAdminPanel\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use LaravelAdminPanel\Database\Types\Type;

class GeometryType extends Type
{
    const NAME = 'geometry';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        return 'geometry';
    }
}
