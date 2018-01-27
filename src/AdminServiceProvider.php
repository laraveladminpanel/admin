<?php

namespace LaravelAdminPanel;

use Arrilot\Widgets\Facade as Widget;
use Arrilot\Widgets\ServiceProvider as WidgetServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Intervention\Image\ImageServiceProvider;
use Larapack\DoctrineSupport\DoctrineSupportServiceProvider;
use LaravelAdminPanel\Events\FormFieldsRegistered;
use LaravelAdminPanel\Facades\Admin as AdminFacade;
use LaravelAdminPanel\FormFields\After\DescriptionHandler;
use LaravelAdminPanel\Http\Middleware\AdminAdminMiddleware;
use LaravelAdminPanel\Models\MenuItem;
use LaravelAdminPanel\Models\Setting;
use LaravelAdminPanel\Policies\BasePolicy;
use LaravelAdminPanel\Policies\MenuItemPolicy;
use LaravelAdminPanel\Policies\SettingPolicy;
use LaravelAdminPanel\Providers\AdminEventServiceProvider;
use LaravelAdminPanel\Translator\Collection as TranslatorCollection;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Setting::class  => SettingPolicy::class,
        MenuItem::class => MenuItemPolicy::class,
    ];

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->register(AdminEventServiceProvider::class);
        $this->app->register(ImageServiceProvider::class);
        $this->app->register(WidgetServiceProvider::class);
        $this->app->register(DoctrineSupportServiceProvider::class);

        $loader = AliasLoader::getInstance();
        $loader->alias('Admin', AdminFacade::class);

        $this->app->singleton('admin', function () {
            return new Admin();
        });

        $this->loadHelpers();

        $this->registerAlertComponents();
        $this->registerFormFields();
        $this->registerWidgets();

        $this->registerConfigs();

        if ($this->app->runningInConsole()) {
            $this->registerPublishableResources();
            $this->registerConsoleCommands();
        }

        if (!$this->app->runningInConsole() || config('app.env') == 'testing') {
            $this->registerAppCommands();
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router, Dispatcher $event)
    {
        if (config('admin.user.add_default_role_on_register')) {
            $app_user = config('admin.user.namespace');
            $app_user::created(function ($user) {
                if (is_null($user->role_id)) {
                    AdminFacade::model('User')->findOrFail($user->id)
                        ->setRole(config('admin.user.default_role'))
                        ->save();
                }
            });
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'admin');

        if (app()->version() >= 5.4) {
            $router->aliasMiddleware('admin.user', AdminAdminMiddleware::class);

            if (config('app.env') == 'testing') {
                $this->loadMigrationsFrom(realpath(__DIR__.'/migrations'));
            }
        } else {
            $router->middleware('admin.user', AdminAdminMiddleware::class);
        }

        $this->registerGates();

        $this->registerViewComposers();

        $event->listen('admin.alerts.collecting', function () {
            $this->addStorageSymlinkAlert();
        });

        $this->bootTranslatorCollectionMacros();
    }

    /**
     * Load helpers.
     */
    protected function loadHelpers()
    {
        foreach (glob(__DIR__.'/Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }

    /**
     * Register view composers.
     */
    protected function registerViewComposers()
    {
        // Register alerts
        View::composer('admin::*', function ($view) {
            $view->with('alerts', AdminFacade::alerts());
        });
    }

    /**
     * Add storage symlink alert.
     */
    protected function addStorageSymlinkAlert()
    {
        if (app('router')->current() !== null) {
            $currentRouteAction = app('router')->current()->getAction();
        } else {
            $currentRouteAction = null;
        }
        $routeName = is_array($currentRouteAction) ? array_get($currentRouteAction, 'as') : null;

        if ($routeName != 'admin.dashboard') {
            return;
        }

        $storage_disk = (!empty(config('admin.storage.disk'))) ? config('admin.storage.disk') : 'public';

        if (request()->has('fix-missing-storage-symlink') && !file_exists(public_path('storage'))) {
            $this->fixMissingStorageSymlink();
        } elseif (!file_exists(public_path('storage')) && $storage_disk == 'public') {
            $alert = (new Alert('missing-storage-symlink', 'warning'))
                ->title(__('admin.error.symlink_missing_title'))
                ->text(__('admin.error.symlink_missing_text'))
                ->button(__('admin.error.symlink_missing_button'), '?fix-missing-storage-symlink=1');

            AdminFacade::addAlert($alert);
        }
    }

    protected function fixMissingStorageSymlink()
    {
        app('files')->link(storage_path('app/public'), public_path('storage'));

        if (file_exists(public_path('storage'))) {
            $alert = (new Alert('fixed-missing-storage-symlink', 'success'))
                ->title(__('admin.error.symlink_created_title'))
                ->text(__('admin.error.symlink_created_text'));
        } else {
            $alert = (new Alert('failed-fixing-missing-storage-symlink', 'danger'))
                ->title(__('admin.error.symlink_failed_title'))
                ->text(__('admin.error.symlink_failed_text'));
        }

        AdminFacade::addAlert($alert);
    }

    /**
     * Register alert components.
     */
    protected function registerAlertComponents()
    {
        $components = ['title', 'text', 'button'];

        foreach ($components as $component) {
            $class = 'LaravelAdminPanel\\Alert\\Components\\'.ucfirst(camel_case($component)).'Component';

            $this->app->bind("admin.alert.components.{$component}", $class);
        }
    }

    protected function bootTranslatorCollectionMacros()
    {
        Collection::macro('translate', function () {
            $transtors = [];

            foreach ($this->all() as $item) {
                $transtors[] = call_user_func_array([$item, 'translate'], func_get_args());
            }

            return new TranslatorCollection($transtors);
        });
    }

    /**
     * Register widget.
     */
    protected function registerWidgets()
    {
        $default_widgets = ['LaravelAdminPanel\\Widgets\\UserDimmer', 'LaravelAdminPanel\\Widgets\\PostDimmer', 'LaravelAdminPanel\\Widgets\\PageDimmer'];
        $widgets = config('admin.dashboard.widgets', $default_widgets);

        foreach ($widgets as $widget) {
            Widget::group('admin::dimmers')->addWidget($widget);
        }
    }

    /**
     * Register the publishable files.
     */
    private function registerPublishableResources()
    {
        $publishablePath = dirname(__DIR__).'/publishable';

        $publishable = [
            'admin_assets' => [
                "{$publishablePath}/assets/" => public_path(config('admin.assets_path')),
            ],
            'migrations' => [
                "{$publishablePath}/database/migrations/" => database_path('migrations'),
            ],
            'seeds' => [
                "{$publishablePath}/database/seeds/" => database_path('seeds'),
            ],
            'demo_content' => [
                "{$publishablePath}/demo_content/" => storage_path('app/public'),
            ],
            'config' => [
                "{$publishablePath}/config/admin.php" => config_path('admin.php'),
            ],
            'lang' => [
                "{$publishablePath}/lang/" => base_path('resources/lang/'),
            ],
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }

    public function registerConfigs()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/publishable/config/admin.php', 'admin'
        );
    }

    public function registerGates()
    {
        // This try catch is necessary for the Package Auto-discovery
        // otherwise it will throw an error because no database
        // connection has been made yet.
        try {
            if (Schema::hasTable('data_types')) {
                $dataType = AdminFacade::model('DataType');
                $dataTypes = $dataType->get();

                foreach ($dataTypes as $dataType) {
                    $policyClass = BasePolicy::class;
                    if (isset($dataType->policy_name) && $dataType->policy_name !== ''
                        && class_exists($dataType->policy_name)) {
                        $policyClass = $dataType->policy_name;
                    }

                    $this->policies[$dataType->model_name] = $policyClass;
                }

                $this->registerPolicies();
            }
        } catch (\PDOException $e) {
            Log::error('No Database connection yet in AdminServiceProvider registerGates()');
        }
    }

    protected function registerFormFields()
    {
        $formFields = [
            'checkbox',
            'color',
            'date',
            'file',
            'image',
            'multiple_images',
            'number',
            'password',
            'radio_btn',
            'rich_text_box',
            'code_editor',
            'markdown_editor',
            'select_dropdown',
            'select_multiple',
            'text',
            'text_area',
            'timestamp',
            'hidden',
            'coordinates',
        ];

        foreach ($formFields as $formField) {
            $class = studly_case("{$formField}_handler");

            AdminFacade::addFormField("LaravelAdminPanel\\FormFields\\{$class}");
        }

        AdminFacade::addAfterFormField(DescriptionHandler::class);

        event(new FormFieldsRegistered($formFields));
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        $this->commands(Commands\InstallCommand::class);
        $this->commands(Commands\ControllersCommand::class);
        $this->commands(Commands\AdminCommand::class);
    }

    /**
     * Register the commands accessible from the App.
     */
    private function registerAppCommands()
    {
        $this->commands(Commands\MakeModelCommand::class);
    }
}
