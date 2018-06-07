<?php

namespace LaravelAdminPanel\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use LaravelAdminPanel\Contracts\User;
use LaravelAdminPanel\Facades\Admin;

class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Handle all requested permission checks.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return bool
     */
    public function __call($name, $arguments)
    {
        if (count($arguments) < 2) {
            throw new \InvalidArgumentException('not enough arguments');
        }
        /** @var \LaravelAdminPanel\Contracts\User $user */
        $user = $arguments[0];

        /** @var $model */
        $model = $arguments[1];

        return $this->checkPermission($user, $model, $name);
    }

    /**
     * Check if user has an associated permission.
     *
     * @param \LaravelAdminPanel\Contracts\User $user
     * @param object                      $model
     * @param string                      $action
     *
     * @return bool
     */
    protected function checkPermission(User $user, $model, $action)
    {
        $dataType = Admin::model('DataType');
        $dataType = $dataType->where('model_name', get_class($model))->first();

        return $user->hasPermission($action.'_'.$dataType->slug);
    }
}
