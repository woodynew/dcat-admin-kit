<?php

namespace Woodynew\DcatAdminKit\Console;

use Dcat\Admin\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Woodynew\DcatAdminKit\DcatAdminKitServiceProvider;

class InstallCommand extends Command
{
    protected $signature = 'dcat-admin-kit:install';

    protected $description = 'Install and enable Dcat Admin Kit';

    public function handle()
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $table = config('admin.database.extensions_table') ?: 'admin_extensions';

        if (! Schema::connection($connection)->hasTable($table)) {
            $this->error("Dcat Admin table [{$table}] does not exist. Run php artisan admin:install after confirming the target database.");

            return 1;
        }

        $manager = Admin::extension();
        $name = 'woodynew/dcat-admin-kit';

        if (! $manager->has($name)) {
            $this->error('Dcat Admin Kit is not registered. Run composer dump-autoload and try again.');

            return 1;
        }

        Artisan::call('vendor:publish', [
            '--provider' => DcatAdminKitServiceProvider::class,
            '--tag' => 'dcat-admin-kit-config',
        ]);

        $manager->updateManager()
            ->setOutPut($this->output)
            ->update($name);

        $manager->enable($name);

        $this->info('Dcat Admin Kit installed and enabled. Global features remain disabled until configured.');

        return 0;
    }
}
