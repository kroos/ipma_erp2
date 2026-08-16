<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        // PHPUnit applies phpunit.xml <env> to getenv()/$_ENV, but this project's
        // old-style bootstrap loads .env into $_SERVER first (Dotenv default
        // adapters) and Laravel's env() helper reads $_SERVER — so .env values
        // (e.g. SESSION_DRIVER=database) would override the test config. Mirror
        // the phpunit-applied values into $_SERVER so tests never hit the real DB.
        foreach (['APP_ENV', 'BCRYPT_ROUNDS', 'CACHE_DRIVER', 'MAIL_MAILER', 'QUEUE_CONNECTION', 'SESSION_DRIVER', 'TELESCOPE_ENABLED'] as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $_SERVER[$key] = $value;
            }
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
