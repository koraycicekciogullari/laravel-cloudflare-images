<?php

namespace Koraycicekciogullari\LaravelCloudflareImages;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/laravel-cloudflare-images.php';

    public function boot()
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('laravel-cloudflare-images.php'),
        ], 'config');

        Storage::extend('cloudflare', function ($app, $config) {
            $adapter = new LaravelCloudflareImagesAdapter($config);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });

    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-cloudflare-images.php',
            'filesystems.disks'
        );
        $this->app->bind(Filesystem::class, LaravelCloudflareImagesAdapter::class);
    }
}
