<?php

namespace DigitalTunnel\Invoice;

use Illuminate\Support\ServiceProvider;

class InvoiceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $packageConfigPath = __DIR__.'/Config/config.php';
        $appConfigPath = config_path('invoice.php');

        $this->mergeConfigFrom($packageConfigPath, 'invoice');

        $this->publishes([
            $packageConfigPath => $appConfigPath,
        ], 'config');
    }
}
