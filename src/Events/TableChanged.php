<?php

namespace LaravelAdminPanel\Events;

use Illuminate\Queue\SerializesModels;

class TableChanged
{
    use SerializesModels;

    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
