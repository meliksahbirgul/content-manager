<?php

declare(strict_types=1);

namespace Source\Media\Infrastructure\Storage;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Source\Media\Application\Contracts\StorageDriver;

class LocalStorageDriver implements StorageDriver
{
    public function storeAs(UploadedFile $file, string $directory, string $uuid): string
    {
        $filename = $uuid.'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($directory, $filename, 'public');

        if ($path === false) {
            throw new RuntimeException('Failed to store file on local disk.');
        }

        return $path;
    }

    public function url(string $path): string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($path);
    }

    public function delete(string $path): void
    {
        Storage::disk('public')->delete($path);
    }
}
