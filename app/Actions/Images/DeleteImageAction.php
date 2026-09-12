<?php

declare(strict_types=1);

namespace App\Actions\Images;

use Illuminate\Support\Facades\Storage;

class DeleteImageAction
{
    public function __invoke(string $path, string $disk = 'public'): void
    {
        if (\Illuminate\Support\Facades\File::exists(public_path($path))) {
            \Illuminate\Support\Facades\File::delete(public_path($path));
        }

        // Also check storage just in case old files are there
        \Illuminate\Support\Facades\Storage::disk($disk)->delete($path);
    }
}
