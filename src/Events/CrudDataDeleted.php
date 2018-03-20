<?php

namespace LaravelAdminPanel\Events;

use Illuminate\Queue\SerializesModels;
use LaravelAdminPanel\Models\DataType;

class CrudDataDeleted
{
    use SerializesModels;

    public $dataType;
    public $data;
    public $ids;

    public function __construct(DataType $dataType, $data, $ids)
    {
        $this->dataType = $dataType;
        $this->data = $data;
        $this->ids = $ids;

        event(new CrudDataChanged($dataType, $data, 'Deleted'));
    }
}
