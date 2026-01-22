<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesFileUploads
{
    protected function storeUploadedFile(UploadedFile $file, string $disk = 'public', string $dir = '', ?string $filename = null): array
    {
        $this->ensureDirectory($disk, $dir);

        $name = $filename ?? (Str::uuid()->toString() . '.' . $file->getClientOriginalExtension());
        $path = $file->storeAs($dir, $name, $disk);

        return [
            'path' => $path,
            'disk' => $disk,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'original' => $file->getClientOriginalName(),
        ];
    }

    /**
     * @param array<int, UploadedFile> $files
     */
    protected function storeManyUploadedFiles(array $files, string $disk = 'public', string $dir = ''): array
    {
        $stored = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $stored[] = $this->storeUploadedFile($file, $disk, $dir);
        }

        return $stored;
    }

    protected function deleteFileIfExists(?string $disk, ?string $path): void
    {
        if (!$disk || !$path) {
            return;
        }

        $storage = Storage::disk($disk);

        if ($storage->exists($path)) {
            $storage->delete($path);
        }
    }

    protected function ensureDirectory(string $disk, string $dir): void
    {
        if (empty($dir)) {
            return;
        }

        $storage = Storage::disk($disk);

        if (!$storage->exists($dir)) {
            $storage->makeDirectory($dir);
        }
    }
}
