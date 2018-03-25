<?php

namespace LaravelAdminPanel\Traits;

use File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
    public function cropPhotos(Request $request, $slug, Collection $dataType, Model $model)
    {
        $this->filesystem = config('admin.storage.disk');
        $this->folder = config('admin.images.cropper.folder').'/'.$slug;
        $this->quality = config('admin.images.cropper.quality', 100);
        $this->request = $request;
        $this->dataType = $dataType;
        $this->model = $model;

        foreach ($this->getPhotosWithDetails() as $dataRow) {
            $details = json_decode($dataRow->details);

            if (!isset($details->crop)) {
                return false;
            }
            if (!$request->{$dataRow->field}) {
                return false;
            }

            $this->cropPhoto($details->crop, $dataRow);
        }

        return true;
    }

    /**
     * Crop photo by coordinates.
     *
     * @param array                                   $crop
     * @param Illuminate\Database\Eloquent\Collection $dataRow
     *
     * @return void
     */
    private function cropPhoto($crop, $dataRow)
    {
        $folder = $this->folder;

        //If a folder is not exists, then make the folder
        if (!Storage::disk($this->filesystem)->exists($folder)) {
            Storage::disk($this->filesystem)->makeDirectory($folder);
        }

        $itemId = $this->model->id;

        foreach ($crop as $cropParam) {
            $inputName = $dataRow->field.'_'.$cropParam->name;
            $params = json_decode($this->request->get($inputName));

            if (!is_object($params)) {
                return false;
            }

            $path = $this->request->{$dataRow->field};
            $img = Image::make(Storage::disk($this->filesystem)->path($path));

            $img->crop(
                (int) $params->w,
                (int) $params->h,
                (int) $params->x,
                (int) $params->y
            );

            $img->resize($cropParam->size->width, $cropParam->size->height);
            $photoName = $folder.'/'.$cropParam->name.'_'.$itemId.'_'.$cropParam->size->name.'.jpg';
            $img->save(Storage::disk($this->filesystem)->path($photoName), $this->quality);

            if (!empty($cropParam->resize)) {
                foreach ($cropParam->resize as $cropParamResize) {
                    $img->resize($cropParamResize->width, $cropParamResize->height, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $photoName = $folder.'/'.$cropParam->name.'_'.$itemId.'_'.$cropParamResize->name.'.jpg';
                    $img->save(Storage::disk($this->filesystem)->path($photoName), $this->quality);
                }
            }
        }
    }

    /**
     * Get the photos with details.
     *
     * @return Illuminate\Database\Eloquent\Collection $dataType
     */
    public function getPhotosWithDetails()
    {
        return $this->dataType
            ->where('type', '=', 'image')
            ->where('details', '!=', null);
    }

    /**
     * Get the cropped photo url.
     *
     * @param string $prefix
     * @param string $suffix
     *
     * @return string
     */
    public function getCroppedPhoto($prefix, $suffix)
    {
        $photoName = config('admin.images.cropper.folder')
            .'/'.str_replace('_', '-', $this->getTable())
            .'/'.$prefix.'_'.$this->id.'_'.$suffix.'.jpg';

        return Storage::disk($this->filesystem)->url($photoName);
    }
}
