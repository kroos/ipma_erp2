<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\TestCase;

class AuthorizationGuardTest extends TestCase
{

    public function test_login_model_has_no_hardcoded_magic_values(): void
    {
        $loginPath = base_path('app/Models/Login.php');
        $loginContent = file_get_contents($loginPath);

        // Must NOT contain the literal [117, 72]
        $this->assertStringNotContainsString('[117, 72]', $loginContent,
            'Login model must not contain literal [117, 72]');

        // Must NOT contain 'authorise_id', 1 (numeric 1 adjacent to column)
        $this->assertStringNotContainsString("authorise_id', 1", $loginContent,
            'Login model must not contain authorise_id with hardcoded 1');

        // Must contain config('auth.admins.authorise_id')
        $this->assertStringContainsString("config('auth.admins.authorise_id')", $loginContent,
            'Login model must contain config("auth.admins.authorise_id")');

        // Must contain config('auth.admins.login_ids') - may have default default arg
        $this->assertStringContainsString("config('auth.admins.login_ids", $loginContent,
            'Login model must contain config("auth.admins.login_ids")');
    }

    public function test_auth_config_has_admin_markers(): void
    {
        $configPath = base_path('config/auth.php');
        $configContent = file_get_contents($configPath);

        $this->assertStringContainsString("'admins' =>", $configContent,
            'auth.php must have admins section');

        $this->assertStringContainsString("'authorise_id' => 1", $configContent,
            'auth.php must have authorise_id => 1');

        $this->assertStringContainsString("'login_ids' => [117, 72]", $configContent,
            'auth.php must have login_ids => [117, 72]');
    }

    public function test_auth_service_provider_has_admin_gate(): void
    {
        $serviceProviderPath = base_path('app/Providers/AuthServiceProvider.php');
        $serviceProviderContent = file_get_contents($serviceProviderPath);

        $this->assertStringContainsString("Gate::define('admin'", $serviceProviderContent,
            'AuthServiceProvider must define admin gate');
    }

    public function test_system_admin_middleware_uses_can(): void
    {
        $middlewarePath = base_path('app/Http/Middleware/SystemAccess/RedirectIfNotSystemAdmin.php');
        $middlewareContent = file_get_contents($middlewarePath);

        // Must use can('admin')
        $this->assertStringContainsString("can('admin')", $middlewareContent,
            'Middleware must use can("admin")');

        // Must NOT contain a raw isAdmin() call
        $this->assertStringNotContainsString('->isAdmin()', $middlewareContent,
            'Middleware must not contain raw isAdmin() call');
    }
}