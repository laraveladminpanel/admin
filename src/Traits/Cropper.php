<?php

namespace LaravelAdminPanel\Traits;

use File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use LaravelAdminPanel\Models\DataType;

trait Cropper
{
    private $filesystem;
    private $folder;
    private $quality;
    private $request;
    private $dataType;
    private $model;

    /**
     * Save cropped photos.
     *
     * @param Illuminate\Http\Request                 $request
     * @param string                                  $slug
     * @param Illuminate\Database\Eloquent\Collection $dataType
     * @param Illuminate\Database\Eloquent\Model      $model
     *
     * @return bool
     */
    public function cropPhotos($event)
    {
        $this->filesystem = config('admin.storage.disk');
        $this->folder = config('admin.images.cropper.folder') . '/' . $event->slug;
        $this->quality = config('admin.images.cropper.quality', 100);

        $this->request = $event->request;
        $this->dataType = $event->dataType;
        $this->model = $event->model;

        $cropperImages = $this->dataType->rows()->whereType('image')
            ->where('details', 'like', '%cropper%')->get();

        foreach ($cropperImages as $dataRow) {
            $details = json_decode($dataRow->details);

            if (!isset($details->cropper)) {
                continue;
            }

            if (!$this->request->{$dataRow->field}) {
                continue;
            }

            $this->cropPhoto($details->cropper, $dataRow);
        }

        return true;
    }

    /**
     * Crop photo by coordinates.
     *
     * @param array                                   $cropper
     * @param Illuminate\Database\Eloquent\Collection $dataRow
     *
     * @return void
     */
    private function cropPhoto($cropper, $dataRow)
    {
        $folder = $this->folder;
        $disk = Storage::disk($this->filesystem);

        //If a folder is not exists, then make the folder
        if (!$disk->exists($folder)) {
            $disk->makeDirectory($folder);
        }

        $itemId = $this->model->id;

        foreach ($cropper as $cropParam) {
            $inputName = $dataRow->field.'_'.$cropParam->name;
            $params = json_decode($this->request->get($inputName));

            if (!is_object($params)) {
                return false;
            }

            $imageName = $this->request->{$dataRow->field};
            $image = Image::make($disk->path($imageName));

            $image->crop(
                (int) $params->w,
                (int) $params->h,
                (int) $params->x,
                (int) $params->y
            );

            $image->resize($cropParam->size->width, $cropParam->size->height);
            $photoName = $folder.'/'.$inputName.'_'.$itemId.'_'.$cropParam->size->name.'.'.$image->extension;

            if (isset($cropParam->watermark) && file_exists($cropParam->watermark)) {
                $watermark = Image::make(public_path() . '/' . $cropParam->watermark);
                $watermark->resize($cropParam->size->width, null);
                $image->insert($watermark);
            }

            $image->save($disk->path($photoName), $this->quality);

            if (!empty($cropParam->resize)) {
                foreach ($cropParam->resize as $cropParamResize) {
                    $image->resize($cropParamResize->width, $cropParamResize->height, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $photoName = $folder.'/'.$inputName.'_'.$itemId.'_'.$cropParamResize->name.'.'.$image->extension;
                    $image->save($disk->path($photoName), $this->quality);
                }
            }

            $this->model->{$dataRow->field} = $imageName;
            $this->model->save();
        }
    }

    /**
     * Get the cropped photo url.
     *
     * @param string $prefix
     * @param string $suffix
     *
     * @return string
     */
    public function getCroppedPhoto($column, $prefix, $suffix)
    {
        $extension = 'jpeg';
        if (isset($this->$column)) {
            $extension = pathinfo($this->$column, PATHINFO_EXTENSION) ?: $extension;
        }

        $photoName = config('admin.images.cropper.folder')
            .'/'.str_replace('_', '-', $this->getTable())
            .'/'.$column.'_'.$prefix.'_'.$this->id.'_'.$suffix.'.'.$extension;

        return Storage::disk($this->filesystem)->url($photoName);
    }
}
