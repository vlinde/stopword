<?php

namespace Vlinde\StopWord;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class StopWordServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'vlinde');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'vlinde');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/stopword.php', 'stopword');

        // Register the service the package provides.
        $this->app->singleton('stopword', function ($app) {
            return new StopWord;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['stopword'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/stopword.php' => config_path('stopword.php'),
        ], 'stopword.config');

        if (!Schema::hasTable('keywords')) {
            $this->publishes([
                __DIR__ . '/../migrations/create_keywords_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_keywords_table.php'),
            ], 'migrations');
        }

        $this->publishes([
            __DIR__ . '/../migrations/add_index_key_to_keywords_table.php' => database_path('migrations/' . date('Y_m_d_His', strtotime(date('Y-m-d H:i:s')) + 1) . '_add_index_key_to_keywords_table.php'),
        ], 'migrations');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/vlinde'),
        ], 'stopword.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/vlinde'),
        ], 'stopword.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/vlinde'),
        ], 'stopword.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
