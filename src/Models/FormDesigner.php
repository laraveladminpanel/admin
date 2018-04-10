<?php

namespace LaravelAdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class FormDesigner extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'form_designer';

    public $timestamps = false;

    protected $fillable = ['data_type_id', 'options'];

    public function dataTypeId()
    {
        return $this->belongsTo(DataType::class, 'data_type_id', 'id');
    }

    /**
     * Get the column of options and decode it from json to array.
     *
     * @return array
     */
    public function getOptions()
    {
        return json_decode($this->options);
    }
}
