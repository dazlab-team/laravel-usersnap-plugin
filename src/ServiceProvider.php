<?php namespace DazLab\Usersnap;

use DazLab\Usersnap\Middleware\InjectUsersnap;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/usersnap.php';
        $this->mergeConfigFrom($configPath, 'usersnap');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/usersnap.php';
        $this->publishConfig($configPath);

        $this->registerMiddleware(InjectUsersnap::class);
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return config_path('usersnap.php');
    }

    /**
     * Publish the config file
     *
     * @param string $configPath
     */
    protected function publishConfig($configPath)
    {
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
    }

    /**
     * Register the Debugbar Middleware
     *
     * @param string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', $middleware);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['usersnap'];
    }
}
