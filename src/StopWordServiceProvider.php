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
    }
}
