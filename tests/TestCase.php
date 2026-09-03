<?php

namespace Woodynew\DcatAdminKit\Tests;

use Dcat\Admin\AdminServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Woodynew\DcatAdminKit\DcatAdminKitServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            AdminServiceProvider::class,
            DcatAdminKitServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $admin = require dirname(__DIR__).'/vendor/woodynew/dcat-laravel-admin/config/admin.php';

        $app['config']->set('admin', $admin);
        $app['config']->set('admin.auth.enable', false);
        $app['config']->set('admin.permission.enable', false);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
