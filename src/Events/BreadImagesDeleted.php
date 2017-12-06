<?php

namespace LaravelAdminPanel\Events;

use Illuminate\Queue\SerializesModels;

class CrudImagesDeleted
{
    use SerializesModels;

    public $data;

    public $images;

    public function __construct($data, $images)
    {
        $this->data = $data;

        $this->images = $images;
    }
}
