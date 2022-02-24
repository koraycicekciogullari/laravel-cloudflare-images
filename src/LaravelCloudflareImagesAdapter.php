<?php

namespace Koraycicekciogullari\LaravelCloudflareImages;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckDirectoryExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToSetVisibility;

/**
 * Class LaravelCloudflareImagesAdapter
 */
class LaravelCloudflareImagesAdapter implements FilesystemAdapter
{

    protected $client;

    public function __construct(
        $client,
    ) {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getClient(): string
    {
        return $this->client;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        $response = Http::withToken($this->client['CLOUDFLARE_IMAGES_KEY'])
            ->get('https://api.cloudflare.com/client/v4/accounts/'.$this->client['CLOUDFLARE_IMAGES_ACCOUNT'].'/images/v1/'.$path)
            ->json();
        return (bool)$response;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function directoryExists(string $path): bool
    {
        throw UnableToCheckDirectoryExistence::forLocation($path);
    }

    /**
     * @param string $path
     * @param string $contents
     * @param Config $config
     * @return void
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $tmpFile = tmpfile();
        if (fwrite($tmpFile, $contents)) {
            $this->writeStream($path, $tmpFile, $config);
        }
    }

    /**
     * @param string $path
     * @param $contents
     * @param Config $config
     * @return void
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $metadata = stream_get_meta_data($contents);
        Http::attach('file', file_get_contents($metadata['uri']), $path)
            ->withToken($this->client['CLOUDFLARE_IMAGES_KEY'])
            ->post('https://api.cloudflare.com/client/v4/accounts/'.$this->client['CLOUDFLARE_IMAGES_ACCOUNT'].'/images/v1')
            ->json();
    }

    /**
     * @param string $path
     * @return string
     */
    public function read(string $path): string
    {
        return $this->readStream($path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function readStream(string $path): string
    {
        $response = Http::withToken($this->client['CLOUDFLARE_IMAGES_KEY'])
            ->get('https://api.cloudflare.com/client/v4/accounts/'.$this->client['CLOUDFLARE_IMAGES_ACCOUNT'].'/images/v1/'.$path)
            ->json();
        return $response['result']['variants'][0];
    }

    /**
     * @param string $path
     * @return void
     */
    public function delete(string $path): void
    {
        Http::withToken($this->client['CLOUDFLARE_IMAGES_KEY'])
            ->delete('https://api.cloudflare.com/client/v4/accounts/'.$this->client['CLOUDFLARE_IMAGES_ACCOUNT'].'/images/v1/'.$path)
            ->json();
    }

    /**
     * @param string $path
     * @return void
     */
    public function deleteDirectory(string $path): void
    {
        throw UnableToDeleteDirectory::atLocation($path);
    }

    /**
     * @param string $path
     * @param Config $config
     * @return void
     */
    public function createDirectory(string $path, Config $config): void
    {
        throw UnableToCreateDirectory::atLocation($path);
    }

    /**
     * @param string $path
     * @param string $visibility
     * @return void
     */
    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path, 'Adapter does not support visibility controls.');
    }

    /**
     * @param string $path
     * @return FileAttributes
     */
    public function visibility(string $path): FileAttributes
    {
        throw UnableToSetVisibility::atLocation($path, 'Adapter does not support visibility controls.');
    }

    /**
     * @param string $path
     * @return FileAttributes
     */
    public function mimeType(string $path): FileAttributes
    {
        return new FileAttributes(
            $path,
            null,
            null,
            null,
            null
        );
    }

    /**
     * @param string $path
     * @return FileAttributes
     */
    public function lastModified(string $path): FileAttributes
    {
        return new FileAttributes(
            $path,
            null,
            null,
            null
        );
    }

    /**
     * @param string $path
     * @return FileAttributes
     */
    public function fileSize(string $path): FileAttributes
    {
        return new FileAttributes(
            $path,
            null,
            null,
            null
        );
    }

    /**
     * @param string $path
     * @param bool $deep
     * @return iterable
     */
    public function listContents(string $path, bool $deep): iterable
    {
        $resources = [];
        $response = Http::withToken($this->client['CLOUDFLARE_IMAGES_KEY'])
            ->get('https://api.cloudflare.com/client/v4/accounts/'.$this->client['CLOUDFLARE_IMAGES_ACCOUNT'].'/images/v1')->json();
        foreach ($response['result']['images'] as $i => $resource) {
            $resources[$i] = new FileAttributes(
                $resource['variants'][0] ?? null,
                $resource['size'] ?? null,
                'visible',
                Carbon::createFromTimeString($resource['uploaded'])->timestamp ?? null,
                $resource['mime_type'] ?? null
            );
        }
        return $resources;
    }

    /**
     * @param string $source
     * @param string $destination
     * @param Config $config
     * @return void
     */
    public function move(string $source, string $destination, Config $config): void
    {
        throw UnableToMoveFile::fromLocationTo($source, $destination);
    }

    /**
     * @param string $source
     * @param string $destination
     * @param Config $config
     * @return void
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        throw UnableToCopyFile::fromLocationTo($source, $destination);
    }

}
