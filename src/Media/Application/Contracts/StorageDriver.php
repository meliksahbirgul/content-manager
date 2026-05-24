<?php

declare(strict_types=1);

namespace Source\Media\Application\Contracts;

use Illuminate\Http\UploadedFile;

interface StorageDriver
{
    public function storeAs(UploadedFile $file, string $directory, string $uuid): string;

    public function url(string $path): string;

    public function delete(string $path): void;
}
