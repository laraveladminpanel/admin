<?php

namespace LaravelAdminPanel\Database\Types\Postgresql;

use LaravelAdminPanel\Database\Types\Common\CharType;

class CharacterType extends CharType
{
    const NAME = 'character';
    const DBTYPE = 'bpchar';
}
