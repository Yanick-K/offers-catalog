<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('session.driver', 'array');

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
            VerifyCsrfToken::class,
        ]);

        $this->ensureViteManifest();
    }

    private function ensureViteManifest(): void
    {
        $path = public_path('build/manifest.json');
        if (is_file($path)) {
            return;
        }

        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $hash = Str::random(8);
        $manifest = [
            'resources/css/app.css' => [
                'file' => "assets/app-{$hash}.css",
                'src' => 'resources/css/app.css',
            ],
            'resources/js/app.js' => [
                'file' => "assets/app-{$hash}.js",
                'src' => 'resources/js/app.js',
                'isEntry' => true,
                'css' => ["assets/app-{$hash}.css"],
            ],
        ];

        file_put_contents($path, json_encode($manifest));
    }
}
