<?php

use Illuminate\Database\Seeder;
use LaravelAdminPanel\Models\DataType;
use LaravelAdminPanel\Models\FormDesigner;

class FormDesignerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pageDataType = DataType::where('slug', 'pages')->firstOrFail();

        if (!$pageDataType->exists) {
            return false;
        }

        FormDesigner::firstOrCreate([
            'data_type_id' => $pageDataType->id,
            'options'      => json_encode([
                [
                    'class'  => 'col-md-7',
                    'panels' => [
                        [
                            'class'  => 'panel',
                            'title'  => 'Text Information',
                            'fields' => [
                                'title',
                                'excerpt',
                                'body',
                                'slug',
                                'status',
                            ],
                        ],
                        [
                            'class'  => 'panel panel-bordered panel-info',
                            'title'  => 'admin.post.seo_content',
                            'fields' => [
                                'meta_keywords',
                                'meta_description',
                            ],
                        ],
                    ],
                ],
                [
                    'class'  => 'col-md-5',
                    'panels' => [
                        [
                            'class'  => 'panel panel-bordered panel-primary',
                            'title'  => 'Image block',
                            'fields' => [
                                'image',
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }
}
