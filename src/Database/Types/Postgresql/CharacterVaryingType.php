<?php

namespace LaravelAdminPanel\Database\Types\Postgresql;

use LaravelAdminPanel\Database\Types\Common\VarCharType;

class CharacterVaryingType extends VarCharType
{
    const NAME = 'character varying';
    const DBTYPE = 'varchar';
}
