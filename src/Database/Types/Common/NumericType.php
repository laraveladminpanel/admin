<?php

namespace LaravelAdminPanel\Database\Types\Common;

use Doctrine\DBAL\Types\DecimalType as DoctrineDecimalType;

class NumericType extends DoctrineDecimalType
{
    const NAME = 'numeric';

    public function getName()
    {
        return static::NAME;
    }
}
