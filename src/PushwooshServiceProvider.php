<?php

namespace NotificationChannels\Pushwoosh;

use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class PushwooshServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->afterResolving(ChannelManager::class, function (ChannelManager $channels) {
            $channels->extend('pushwoosh', function ($app) {
                return $app[PushwooshChannel::class];
            });
        });

        $this->app->bindIf(Pushwoosh::class, function (Application $app) {
            return new Pushwoosh(
                $app->make(Client::class),
                $app['events'],
                $app['config']['services.pushwoosh.application'],
                $app['config']['services.pushwoosh.token'],
                $app['config']['services.pushwoosh.enabled'] ?? true
            );
        });
    }
}
