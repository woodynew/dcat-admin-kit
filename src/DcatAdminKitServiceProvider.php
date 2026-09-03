<?php

namespace Woodynew\DcatAdminKit;

use Dcat\Admin\Extend\ServiceProvider;
use Woodynew\DcatAdminKit\Console\InstallCommand;

class DcatAdminKitServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom($this->path('config/dcat-admin-kit.php'), 'dcat-admin-kit');

        // Public column APIs must remain available even before the Dcat extension
        // record is installed or enabled. Dcat skips init() for disabled extensions.
        (new Bootstrapper())->registerColumnDisplayers();

        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);

            $this->publishes([
                $this->path('config/dcat-admin-kit.php') => config_path('dcat-admin-kit.php'),
            ], 'dcat-admin-kit-config');

            $this->publishes([
                $this->path('resources/assets') => public_path('vendor/dcat-admin-extensions/woodynew/dcat-admin-kit'),
            ], 'dcat-admin-kit-assets');
        }
    }

    public function init()
    {
        parent::init();

        (new Bootstrapper())->boot();
    }

    public function update($currentVersion, $stopOnVersion)
    {
        // Version scripts are handled by Dcat's extension manager. This kit owns no menu rows.
    }

    public function uninstall()
    {
        // The kit does not own application data or menu rows.
    }
}
