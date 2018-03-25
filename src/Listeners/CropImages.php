<?php

namespace LaravelAdminPanel\Listeners;

class CropImages
{
    /**
     * Crop the images for a given CRUD.
     *
     * @param $event
     *
     * @return void
     */
    public function handle($event)
    {
        $needCrop = $event->dataType->rows()
            ->whereType('image')
            ->where('details', 'like', '%cropper%')
            ->exists();

        if ($needCrop) {
            $event->model->cropPhotos($event);
        }
    }
}
