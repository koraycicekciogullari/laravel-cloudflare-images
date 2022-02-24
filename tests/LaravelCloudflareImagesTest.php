<?php

namespace Koraycicekciogullari\LaravelCloudflareImages\Tests;

use Koraycicekciogullari\LaravelCloudflareImages\LaravelCloudflareImagesAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\FilesystemAdapter;

class LaravelCloudflareImagesTest extends FilesystemAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $config = [
            'CLOUDFLARE_IMAGES_ACCOUNT' => env('CLOUDFLARE_IMAGES_ACCOUNT'),
            'CLOUDFLARE_IMAGES_KEY' => env('CLOUDFLARE_IMAGES_KEY'),
            'CLOUDFLARE_IMAGES_DELIVERY_URL' => env('CLOUDFLARE_IMAGES_DELIVERY_URL'),
            'CLOUDFLARE_IMAGES_DEFAULT_VARIATION' => env('CLOUDFLARE_IMAGES_DEFAULT_VARIATION'),
        ];
        return new LaravelCloudflareImagesAdapter($config);
    }
}
