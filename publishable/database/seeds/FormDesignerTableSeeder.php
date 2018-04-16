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
        $dataType = DataType::where('slug', 'posts')->firstOrFail();

        if (!$dataType->exists) {
            return false;
        }

        FormDesigner::firstOrCreate([
            'data_type_id' => $dataType->id,
            'options'      => json_encode([
                [
                    'class'  => 'col-md-8',
                    'panels' => [
                        [
                            'class'  => 'panel',
                            'title'  => '<i class="admin-character"></i> Post Title The title for your post',
                            'fields' => [
                                'title',
                            ],
                        ],
                        [
                            'class'  => 'panel',
                            'title'  => 'Post Content',
                            'fields' => [
                                'body',
                            ],
                        ],
                        [
                            'class'  => 'panel',
                            'title'  => 'Excerpt <small>Small description of this post</small>',
                            'fields' => [
                                'excerpt',
                            ],
                        ],
                    ],
                ],
                [
                    'class'  => 'col-md-4',
                    'panels' => [
                        [
                            'class'  => 'panel panel-warning',
                            'title'  => 'Post Details',
                            'fields' => [
                                'slug',
                                'status',
                                'category_id',
                                'featured',
                            ],
                        ],
                        [
                            'class'  => 'panel panel-primary',
                            'title'  => 'Post Image',
                            'fields' => [
                                'image',
                            ],
                        ],
                        [
                            'class'  => 'panel panel-info',
                            'title'  => 'admin.post.seo_content',
                            'fields' => [
                                'meta_keywords',
                                'meta_description',
                                'seo_title',
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    }
}
