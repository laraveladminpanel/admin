<?php

namespace LaravelAdminPanel\Database\Types\Postgresql;

use LaravelAdminPanel\Database\Types\Common\DoubleType;

class DoublePrecisionType extends DoubleType
{
    const NAME = 'double precision';
    const DBTYPE = 'float8';
}
