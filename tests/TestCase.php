<?php

namespace NotificationChannels\Pushwoosh\Tests;

use NotificationChannels\Pushwoosh\PushwooshServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Get the package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            PushwooshServiceProvider::class,
        ];
    }
}
