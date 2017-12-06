<?php

namespace LaravelAdminPanel\Widgets;

use Arrilot\Widgets\AbstractWidget;
use Illuminate\Support\Str;
use LaravelAdminPanel\Facades\Admin;

class PageDimmer extends AbstractWidget
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
        $count = Admin::model('Page')->count();
        $string = trans_choice('admin.dimmer.page', $count);

        return view('admin::dimmer', array_merge($this->config, [
            'icon'   => 'admin-file-text',
            'title'  => "{$count} {$string}",
            'text'   => __('admin.dimmer.page_text', ['count' => $count, 'string' => Str::lower($string)]),
            'button' => [
                'text' => __('admin.dimmer.page_link_text'),
                'link' => route('admin.pages.index'),
            ],
            'image' => admin_asset('images/widget-backgrounds/03.jpg'),
        ]));
    }
}
