<?php

declare(strict_types=1);

namespace Source\Media\Infrastructure\Storage;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Source\Media\Application\Contracts\StorageDriver;

class S3StorageDriver implements StorageDriver
{
    public function storeAs(UploadedFile $file, string $directory, string $uuid): string
    {
        $filename = $uuid.'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($directory, $filename, 's3');

        if ($path === false) {
            throw new RuntimeException('Failed to store file on S3.');
        }

        return $path;
    }

    public function url(string $path): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        return $disk->url($path);
    }

    public function delete(string $path): void
    {
        Storage::disk('s3')->delete($path);
    }
}
