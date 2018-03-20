<?php

namespace LaravelAdminPanel\Events;

use Illuminate\Queue\SerializesModels;
use LaravelAdminPanel\Models\DataType;

class CrudDataDeleted
{
    use SerializesModels;

    public $dataType;

    public $data;

    public function __construct(DataType $dataType, $data)
    {
        $this->dataType = $dataType;

        $this->data = $data;

        event(new CrudDataChanged($dataType, $data, 'Deleted'));
    }
}
