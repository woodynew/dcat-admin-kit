<?php

namespace Woodynew\DcatAdminKit\Tests;

use Dcat\Admin\Http\Middleware\Authenticate;
use Dcat\Admin\Http\Middleware\Permission;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Woodynew\DcatAdminKit\Actions\Form\TopGoBack;
use Woodynew\DcatAdminKit\Actions\Form\TopSubmit;
use Woodynew\DcatAdminKit\DcatAdminKitServiceProvider;
use Woodynew\DcatAdminKit\Grid\Tools\AdminGridHrefTool;
use Woodynew\DcatAdminKit\Grid\Tools\GridFormTool;
use Woodynew\DcatAdminKit\Http\Middleware\SetLocale;

class LocaleTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        $app['config']->set('session.driver', 'array');
        $app['config']->set('app.locale', 'zh_CN');
        $app['config']->set('dcat-admin-kit.features.locale_switcher', true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(DcatAdminKitServiceProvider::class)->init();
        $this->app['router']->middleware(['web', 'admin'])->get('admin/locale-probe', function () {
            return response()->json([
                'locale' => app()->getLocale(),
                'config' => config('app.locale'),
                'copy' => trans('woodynew.dcat-admin-kit::kit.copy'),
            ]);
        });
    }

    public function test_switch_persists_and_applies_to_subsequent_ajax_requests(): void
    {
        $this->postJson('/admin/kit/locale', ['locale' => 'en'])->assertOk()
            ->assertSessionHas('dcat-admin-kit.locale', 'en');
        $this->getJson('/admin/locale-probe')->assertExactJson([
            'locale' => 'en', 'config' => 'en', 'copy' => 'Copy',
        ]);
        $this->assertSame('zh_CN', app()->getLocale());

        $this->postJson('/admin/kit/locale', ['locale' => 'zh_TW'])->assertOk();
        $this->getJson('/admin/locale-probe')->assertJson(['copy' => '複製']);
    }

    public function test_invalid_values_do_not_replace_preference(): void
    {
        $this->withSession(['dcat-admin-kit.locale' => 'en']);
        foreach (['../en', 'fr', ['en'], null] as $locale) {
            $this->postJson('/admin/kit/locale', ['locale' => $locale])->assertStatus(422)
                ->assertSessionHas('dcat-admin-kit.locale', 'en');
        }
        $this->get('/admin/kit/locale')->assertStatus(405);
    }

    public function test_disabled_feature_ignores_preference_and_rejects_switch(): void
    {
        config(['dcat-admin-kit.features.locale_switcher' => false]);
        $this->withSession(['dcat-admin-kit.locale' => 'en'])
            ->getJson('/admin/locale-probe')->assertJson(['locale' => 'zh_CN']);
        $this->postJson('/admin/kit/locale', ['locale' => 'en'])->assertNotFound();
    }

    public function test_removed_locale_falls_back_to_application_locale(): void
    {
        $this->withSession(['dcat-admin-kit.locale' => 'fr'])
            ->getJson('/admin/locale-probe')->assertJson(['locale' => 'zh_CN']);
    }

    public function test_switch_keeps_authentication_and_csrf_but_not_menu_permission(): void
    {
        $this->getJson('/admin/locale-probe')->assertOk();
        $route = $this->app['router']->getRoutes()->match(Request::create('/admin/kit/locale', 'POST'));
        $this->assertSame(admin_route_name('kit.locale'), $route->getName());
        $middleware = $this->app['router']->gatherRouteMiddleware($route);
        $this->assertContains(Authenticate::class, $middleware);
        $this->assertNotContains(Permission::class, $middleware);
        $this->assertTrue((bool) array_filter($middleware, function ($name) {
            return is_a($name, \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, true);
        }), implode(', ', $middleware));
        config(['admin.auth.enable' => true]);
        config(['auth.guards.admin' => config('admin.auth.guards.admin')]);
        config(['auth.providers.admin' => config('admin.auth.providers.admin')]);
        $request = Request::create('/admin/kit/locale', 'POST');
        $request->setRouteResolver(function () use ($route) { return $route; });
        $response = (new Authenticate())->handle($request, function () {
            $this->fail('Guests must not reach the preference controller.');
        });
        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_request_locale_is_restored_on_failure_and_without_session(): void
    {
        $request = Request::create('/admin');
        $session = new Store('test', new ArraySessionHandler(120));
        $session->put('dcat-admin-kit.locale', 'en');
        $request->setLaravelSession($session);
        try {
            (new SetLocale())->handle($request, function () {
                $this->assertSame('en', app()->getLocale());
                throw new \RuntimeException('downstream');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('downstream', $exception->getMessage());
        }
        $this->assertSame('zh_CN', app()->getLocale());
        (new SetLocale())->handle(Request::create('/admin'), function () {
            $this->assertSame('zh_CN', app()->getLocale());
        });
    }

    public function test_tools_translate_defaults_and_preserve_custom_titles(): void
    {
        app()->setLocale('en');
        $this->assertStringContainsString('Back', (new TopGoBack())->title());
        $this->assertStringContainsString('Submit', (new TopSubmit())->title());
        $this->assertSame('Open link', (new AdminGridHrefTool())->title());
        $this->assertSame('Form', (new GridFormTool())->title());
        $this->assertSame('Custom', (new TopGoBack('Custom'))->title());
        $this->assertSame('Custom', (new TopSubmit('Custom'))->title());
    }

    public function test_switcher_renders_current_language_and_escaped_labels(): void
    {
        app()->setLocale('en');
        $html = view('woodynew.dcat-admin-kit::partials.locale-switcher', [
            'locales' => ['en' => '<English>'], 'locale' => 'en',
        ])->render();
        $this->assertStringContainsString('aria-label="Language"', $html);
        $this->assertStringContainsString('value="en" selected', $html);
        $this->assertStringContainsString('&lt;English&gt;', $html);
    }
}
