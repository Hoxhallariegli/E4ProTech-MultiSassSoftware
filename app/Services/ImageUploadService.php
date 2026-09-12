<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ImageUploadService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Upload and compress image. Returns relative path to public folder.
     */
    public function upload($file, $folder, $maxWidth = 1000, $quality = 75)
    {
        if (!$file) return null;

        try {
            // 1. Ensure target directory exists in public/
            $targetDir = public_path($folder);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            // 2. Generate unique name with .webp extension for better compression
            $name = Str::random(30) . '.webp';
            $targetPath = $targetDir . '/' . $name;

            // 3. Read and Process image
            $image = $this->manager->read($file->getRealPath());

            // Auto-rotate based on EXIF
            // $image->orient(); // v3 does this differently or by default depending on driver

            // Scale down if too large
            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            // 4. Encode to WebP and save
            $encoded = $image->toWebp($quality);
            $encoded->save($targetPath);

            return $folder . '/' . $name;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Image compression failed: " . $e->getMessage());

            // Fallback stays public as well. Never move an image back to storage/app.
            $fallbackExtension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $fallbackExtension = in_array($fallbackExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)
                ? $fallbackExtension
                : 'jpg';
            $fallbackName = Str::random(30) . '.' . $fallbackExtension;
            $fallbackPath = $targetDir . '/' . $fallbackName;

            $file->move($targetDir, $fallbackName);

            return $folder . '/' . $fallbackName;
        }
    }

    public function delete($path)
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }
}
