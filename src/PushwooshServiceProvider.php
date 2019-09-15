<?php

namespace NotificationChannels\Pushwoosh;

use GuzzleHttp\Client;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class PushwooshServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->afterResolving(ChannelManager::class, function (ChannelManager $channels) {
            $channels->extend('pushwoosh', function ($app) {
                return $app[PushwooshChannel::class];
            });
        });

        $this->app->bindIf(Pushwoosh::class, function ($app) {
            return new Pushwoosh(
                new Client(),
                $app['config']['services.pushwoosh.application'],
                $app['config']['services.pushwoosh.token']
            );
        });
    }
}
