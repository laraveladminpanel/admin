<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

class MultipleImagesHandler extends AbstractHandler
{
    protected $codename = 'multiple_images';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.multiple_images', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        $files = $request->file($row->field);

        $filesPath = [];

        $options = json_decode($row->details);

        if (isset($options->resize) && isset($options->resize->width) && isset($options->resize->height)) {
            $resize_width = $options->resize->width;
            $resize_height = $options->resize->height;
        } else {
            $resize_width = 1800;
            $resize_height = null;
        }

        foreach ($files as $key => $file) {
            $filename = Str::random(20);
            $path = $slug.'/'.date('FY').'/';
            array_push($filesPath, $path.$filename.'.'.$file->getClientOriginalExtension());
            $filePath = $path.$filename.'.'.$file->getClientOriginalExtension();

            $image = Image::make($file)->resize($resize_width, $resize_height,
                function (Constraint $constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode($file->getClientOriginalExtension(), 75);

            Storage::disk(config('admin.storage.disk'))->put($filePath, (string) $image, 'public');

            if (isset($options->thumbnails)) {
                foreach ($options->thumbnails as $thumbnails) {
                    if (isset($thumbnails->name) && isset($thumbnails->scale)) {
                        $scale = intval($thumbnails->scale) / 100;
                        $thumb_resize_width = $resize_width;
                        $thumb_resize_height = $resize_height;

                        if ($thumb_resize_width != null) {
                            $thumb_resize_width = $thumb_resize_width * $scale;
                        }

                        if ($thumb_resize_height != null) {
                            $thumb_resize_height = $thumb_resize_height * $scale;
                        }

                        $image = Image::make($file)->resize($thumb_resize_width, $thumb_resize_height,
                            function (Constraint $constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })->encode($file->getClientOriginalExtension(), 75);
                    } elseif (isset($options->thumbnails) && isset($thumbnails->crop->width) && isset($thumbnails->crop->height)) {
                        $crop_width = $thumbnails->crop->width;
                        $crop_height = $thumbnails->crop->height;
                        $image = Image::make($file)
                            ->fit($crop_width, $crop_height)
                            ->encode($file->getClientOriginalExtension(), 75);
                    }

                    Storage::disk(config('admin.storage.disk'))->put($path.$filename.'-'.$thumbnails->name.'.'.$file->getClientOriginalExtension(),
                        (string) $image, 'public'
                    );
                }
            }
        }

        return json_encode($filesPath);
    }
}
