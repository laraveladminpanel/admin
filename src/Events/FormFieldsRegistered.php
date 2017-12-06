<?php

namespace LaravelAdminPanel\Events;

use Illuminate\Queue\SerializesModels;

class FormFieldsRegistered
{
    use SerializesModels;

    public $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;

        // @deprecate
        //
        event('admin.form-fields.registered', $fields);
    }
}
