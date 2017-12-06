<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

class ImageHandler extends AbstractHandler
{
    protected $codename = 'image';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.image', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        if (!$request->hasFile($row->field)) {
            return null;
        }

        $file = $request->file($row->field);
        $options = json_decode($row->details);

        $filename = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
        $filename_counter = 1;

        $path = $slug.'/'.date('FY').'/';

        // Make sure the filename does not exist, if it does make sure to add a number to the end 1, 2, 3, etc...
        while (Storage::disk(config('admin.storage.disk'))->exists($path.$filename.'.'.$file->getClientOriginalExtension())) {
            $filename = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension()).(string) ($filename_counter++);
        }

        $fullPath = $path.$filename.'.'.$file->getClientOriginalExtension();

        if (isset($options->resize) && isset($options->resize->width) && isset($options->resize->height)) {
            $resize_width = $options->resize->width;
            $resize_height = $options->resize->height;
        } else {
            $resize_width = 1800;
            $resize_height = null;
        }

        $image = Image::make($file)->resize($resize_width, $resize_height,
            function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode($file->getClientOriginalExtension(), 75);

        if ($this->is_animated_gif($file)) {
            Storage::disk(config('admin.storage.disk'))->put($fullPath, file_get_contents($file), 'public');
            $fullPathStatic = $path.$filename.'-static.'.$file->getClientOriginalExtension();
            Storage::disk(config('admin.storage.disk'))->put($fullPathStatic, (string) $image, 'public');
        } else {
            Storage::disk(config('admin.storage.disk'))->put($fullPath, (string) $image, 'public');
        }

        if (isset($options->thumbnails)) {
            foreach ($options->thumbnails as $thumbnails) {
                if (isset($thumbnails->name) && isset($thumbnails->scale)) {
                    $scale = intval($thumbnails->scale) / 100;
                    $thumb_resize_width = $resize_width;
                    $thumb_resize_height = $resize_height;

                    if ($thumb_resize_width != null && $thumb_resize_width != 'null') {
                        $thumb_resize_width = intval($thumb_resize_width * $scale);
                    }

                    if ($thumb_resize_height != null && $thumb_resize_height != 'null') {
                        $thumb_resize_height = intval($thumb_resize_height * $scale);
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

        return $fullPath;
    }

    protected function is_animated_gif($filename)
    {
        $raw = file_get_contents($filename);

        $offset = 0;
        $frames = 0;
        while ($frames < 2) {
            $where1 = strpos($raw, "\x00\x21\xF9\x04", $offset);
            if ($where1 === false) {
                break;
            } else {
                $offset = $where1 + 1;
                $where2 = strpos($raw, "\x00\x2C", $offset);
                if ($where2 === false) {
                    break;
                } else {
                    if ($where1 + 8 == $where2) {
                        $frames++;
                    }
                    $offset = $where2 + 1;
                }
            }
        }

        return $frames > 1;
    }
}
