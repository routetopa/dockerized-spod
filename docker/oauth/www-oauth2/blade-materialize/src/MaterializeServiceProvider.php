<?php

namespace LucaVicidomini\BladeMaterialize;

use Illuminate\Support\ServiceProvider;

class MaterializeServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
	    $this->publishes( [
		    __DIR__ . '/config/blade-materialize.php' => config_path( 'blade-materialize.php' ),
	    ], 'config' );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHtmlBuilder();
        $this->registerFormBuilder();

	    $this->mergeConfigFrom(
		    __DIR__ . '/config/blade-materialize.php', 'blade-materialize'
	    );
    }

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder()
    {
        $this->app->singleton('mhtml', function ($app) {
            return new HtmlBuilder($app['url'], $app['view']);
        });
    }
    /**
     * Register the form builder instance.
     *
     * @return void
     */
    protected function registerFormBuilder()
    {
        $this->app->singleton('mform', function ($app) {
            $form = new FormBuilder($app['mhtml'], $app['url'], $app['view'], $app['session.store']->getToken());
            return $form;
            return $form->setSessionStore($app['session.store']);
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['mhtml', 'mform', 'LucaVicidomini\BladeMaterialize\HtmlBuilder', 'LucaVicidomini\BladeMaterialize\FormBuilder'];
    }

}
