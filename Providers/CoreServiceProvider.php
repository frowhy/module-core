<?php

namespace Modules\Core\Providers;


use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Prettus\Repository\Events\RepositoryEventBase;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->setLocale();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->registerObservers();
        $this->registerTelescope();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->publish();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ControllerServiceProvider::class);
        $this->app->register(ServiceServiceProvider::class);
        $this->app->register(RepositoryServiceProvider::class);

        $this->app->register(RepositoryFilterContainerProvider::class);

        $this->mergeConfigFrom(
            dirname(__DIR__).'/config.php', 'modules'
        );
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('core.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'core'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/core');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path.'/modules/core';
        }, Config::get('view.paths')), [$sourcePath]), 'core');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/core');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'core');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'core');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (!app()->/** @scrutinizer ignore-call */ environment('production')) {
            app(Factory::class)->load(__DIR__.'/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    public function setLocale()
    {
        Carbon::setLocale('zh');
    }

    public function registerObservers()
    {
        Event::/** @scrutinizer ignore-call */ listen(RepositoryEventBase::class, function (
            RepositoryEventBase $repositoryEntityCreated
        ) {
            $model = $repositoryEntityCreated->getModel();
            $method = $repositoryEntityCreated->getAction();
            $class = get_class($model);
            $namespace = Str::before($class, 'Entities');
            $basename = class_basename($model);
            $observerClass = "{$namespace}Observers\\{$basename}Observer";
            if (class_exists($observerClass)) {
                $observer = app("{$namespace}Observers\\{$basename}Observer");
                $observer->$method($model);
            }
        });
    }

    public function registerTelescope()
    {
        if ($this->app->isLocal() && class_exists('Laravel\\Telescope\\TelescopeApplicationServiceProvider')) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function publish()
    {
        $this->publishes([
            dirname(__DIR__).'/config.php' => config_path('module.php'),
        ], 'modular-config');
    }
}
