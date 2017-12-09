<?php

namespace LaravelAdminPanel\Widgets;

use Arrilot\Widgets\AbstractWidget;
use Illuminate\Support\Str;
use LaravelAdminPanel\Facades\Admin;

class PostDimmer extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $count = Admin::model('Post')->count();
        $string = trans_choice('admin.dimmer.post', $count);

        return view('admin::dimmer', array_merge($this->config, [
            'icon'   => 'admin-news',
            'title'  => "{$count} {$string}",
            'text'   => __('admin.dimmer.post_text', ['count' => $count, 'string' => Str::lower($string)]),
            'button' => [
                'text' => __('admin.dimmer.post_link_text'),
                'link' => route('admin.posts.index'),
            ],
            'image' => admin_asset('images/widget-backgrounds/02.jpg'),
        ]));
    }
}
