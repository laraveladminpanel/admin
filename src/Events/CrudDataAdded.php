<?php

namespace LaravelAdminPanel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use LaravelAdminPanel\Models\DataType;

class CrudDataAdded
{
    use SerializesModels;

    public $request;
    public $slug;
    public $dataType;
    public $data;

    public function __construct(Request $request, $slug, DataType $dataType, Model $data)
    {
        $this->request = $request;
        $this->slug = $slug;
        $this->dataType = $dataType;
        $this->data = $data;

        event(new CrudDataChanged($dataType, $data, 'Added'));
    }
}
