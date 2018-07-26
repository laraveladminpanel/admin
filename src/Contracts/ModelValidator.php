<?php

namespace LaravelAdminPanel\Contracts;

use Illuminate\Http\Request;

interface ModelValidator
{
    public function validate(Request $request);
}
