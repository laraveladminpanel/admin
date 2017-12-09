<?php

namespace LaravelAdminPanel\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use LaravelAdminPanel\Events\FileDeleted;
use LaravelAdminPanel\FormFields\AbstractHandler;
use LaravelAdminPanel\Traits\AlertsMessages;
use Validator;

abstract class BaseController extends Controller
{
    use DispatchesJobs,
        ValidatesRequests,
        AuthorizesRequests,
        AlertsMessages;

    public function getSlug(Request $request)
    {
        if (isset($this->slug)) {
            $slug = $this->slug;
        } else {
            $slug = explode('.', $request->route()->getName())[1];
        }

        return $slug;
    }

    public function insertUpdateData($request, $slug, $rows, $data)
    {
        $multi_select = [];

        /*
         * Prepare Translations and Transform data
         */
        $translations = is_crud_translatable($data)
            ? $data->prepareTranslations($request)
            : [];

        foreach ($rows as $row) {
            $options = json_decode($row->details);

            if ($row->type == 'relationship' && $options->type != 'belongsToMany') {
                continue;
            }

            // if the field for this row is absent from the request, continue
            // checkboxes will be absent when unchecked, thus they are the exception
            if (!$request->has($row->field) && $row->type !== 'checkbox') {
                continue;
            }

            $handler = AbstractHandler::initial($row->type);
            $content = $handler->getContentBasedOnType($request, $slug, $row);

            // If the content is null then keep the current
            if (is_null($content) && in_array($row->type, ['image', 'multiple_images', 'file', 'password'])) {
                continue;
            }

            /*
             * merge ex_images and upload images
             */
            if ($row->type == 'multiple_images' && !is_null($content)) {
                if (isset($data->{$row->field})) {
                    $ex_files = json_decode($data->{$row->field}, true);
                    if (!is_null($ex_files)) {
                        $content = json_encode(array_merge($ex_files, json_decode($content)));
                    }
                }
            }

            if ($row->type == 'relationship' && $options->type == 'belongsToMany') {
                // Only if select_multiple is working with a relationship
                $multi_select[] = ['model' => $options->model, 'content' => $content, 'table' => $options->pivot_table];
            } else {
                $data->{$row->field} = $content;
            }
        }

        $data->save();

        // Save translations
        if (count($translations) > 0) {
            $data->saveTranslations($translations);
        }

        foreach ($multi_select as $sync_data) {
            $data->belongsToMany($sync_data['model'], $sync_data['table'])->sync($sync_data['content']);
        }

        return $data;
    }

    public function validateCrud($request, $data)
    {
        $rules = [];
        $messages = [];

        foreach ($data as $row) {
            $options = json_decode($row->details);

            if (isset($options->validation)) {
                if (isset($options->validation->rule)) {
                    if (!is_array($options->validation->rule)) {
                        $rules[$row->field] = explode('|', $options->validation->rule);
                    } else {
                        $rules[$row->field] = $options->validation->rule;
                    }
                }

                if (isset($options->validation->messages)) {
                    foreach ($options->validation->messages as $key => $msg) {
                        $messages[$row->field.'.'.$key] = $msg;
                    }
                }
            }
        }

        return Validator::make($request, $rules, $messages);
    }

    public function deleteFileIfExists($path)
    {
        if (Storage::disk(config('admin.storage.disk'))->exists($path)) {
            Storage::disk(config('admin.storage.disk'))->delete($path);
            event(new FileDeleted($path));
        }
    }
}
