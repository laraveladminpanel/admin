<?php

namespace LaravelAdminPanel\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use LaravelAdminPanel\Database\Types\Type;

class TinyIntType extends Type
{
    const NAME = 'tinyint';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        $commonIntegerTypeDeclaration = call_protected_method($platform, '_getCommonIntegerTypeDeclarationSQL', $field);

        return 'tinyint'.$commonIntegerTypeDeclaration;
    }
}
