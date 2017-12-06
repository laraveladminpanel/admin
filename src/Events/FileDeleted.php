<?php

namespace LaravelAdminPanel\Events;

class FileDeleted
{
    public $path;

    public function __construct($path)
    {
        $this->path;
    }
}
