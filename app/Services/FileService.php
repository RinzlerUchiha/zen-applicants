<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class FileService
{
    /**
     * Serve an asset (like a logo or background image) from storage.
     *
     * @param string $filename
     * @param string $disk
     * @return \Illuminate\Http\Response
     */
    public static function serveFile($filename)
    {
        $disk = app()->environment('production') ? 's3' : 'public';

        $f_path = dirname($filename);
        $f_base = pathinfo($filename, PATHINFO_FILENAME);

        $exists = collect(Storage::disk($disk)->files($f_path))
            ->filter(function ($file) use ($f_base) {
                return strcasecmp(
                    pathinfo($file, PATHINFO_FILENAME),
                    $f_base
                ) === 0;
            });

        if($exists->first()){
            $filename = $exists->first();
        }

        // Check if the file exists on the specified disk (local or S3)
        if (!Storage::disk($disk)->exists($filename)) {
            // If file does not exist, return 404
            // abort(404, "Not found.");
            return response()->file(public_path('no-file.png'));
        }

        $storage = Storage::disk($disk);

        $stream = $storage->readStream($filename);
        if (! $stream) return response()->file(public_path('no-file.png'));

        $mime = $storage->mimeType($filename) ?: 'application/octet-stream';

        return response()->stream(function () use ($stream) {
            // fpassthru($stream);
            // is_resource($stream) && fclose($stream);
            try {
                if (is_resource($stream)) {
                    fpassthru($stream);
                }
            } finally {
                // Ensures the stream is closed even if an exception occurs
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

        }, 200, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400', // cache in browser for a day
            'ETag'          => md5($filename),               // cheap validator; or store real ETag in cache
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',  // Optional: for inline display
        ]);

        return $response;
    }

    /**
     * Upload an asset to the given disk.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $destination
     * @param string $disk
     * @return string
     */
    public static function uploadFile($file, $destination, $disk = 'public')
    {
        // Store the file in the designated disk and folder
        $path = $file->storeAs($destination, $file->getClientOriginalName(), $disk);

        return $path;
    }

    /**
     * Delete an asset from the disk.
     *
     * @param string $filename
     * @param string $disk
     * @return bool
     */
    public static function deleteFile($filename, $disk = 'public')
    {
        $exists = Storage::disk($disk)->exists($filename);

        if ($exists) {
            return Storage::disk($disk)->delete($filename);
        }

        return false;
    }

    /**
     * Reduce the size of an image file and convert it to WebP format if possible.
     *
     * This function loads an image from the given file path, optionally resizes it to fit within the specified 
     * width and height limits, and then attempts to reduce the file size to meet the target size in kilobytes.
     * If WebP format is supported, the image is converted to WebP and saved to the specified output path.
     * If WebP is not supported or the conversion is not required, the image will be saved in its original format
     * with reduced quality to meet the target size.
     *
     * @param string $imagePath The path to the input image file. This is the image to be resized and compressed.
     * @param string|null $outputPath The path where the resulting image should be saved. If null, the image is returned as a binary string.
     * @param string $disk The storage disk where the resulting image will be saved. Defaults to 'public'.
     * @param int $targetSizeKB The target file size in kilobytes. The image will be compressed until it reaches this size or the quality is reduced to a minimum. Defaults to 100KB.
     * @param int|null $maxWidth The maximum width for resizing the image. If null, no width resizing is applied.
     * @param int|null $maxHeight The maximum height for resizing the image. If null, no height resizing is applied.
     * 
     * @return string|null The path to the saved image file (if $outputPath is provided), or the encoded image as a binary string.
     * 
     * @throws \Exception If the image format is not supported or any other error occurs during processing.
     */
    public static function reduceImageFileSizeToWebP($imagePath, $outputPath = null, $disk = 'public', $targetSizeKB = 100, $maxWidth = null, $maxHeight = null, $keepRatio = true)
    {
        $isConvertible = in_array(mime_content_type($imagePath), [
            'image/jpeg',
            'image/png'
        ]);

        $encodedImage = null; // Variable to hold the encoded image

        // Create an instance of ImageManager (this replaces Image::make() in v3)
        $manager = new ImageManager(Driver::class);
        $image = $manager->read($imagePath); // Load the image

        if (!$isConvertible) {
            $encodedImage = $image->encode();
            if ($outputPath) {
                Storage::disk($disk)->put($outputPath, $encodedImage);
                return $outputPath;
            }
            return $encodedImage;
        }

        // Check if WebP is supported by the current driver
        $webpSupported = $manager->driver()->supports('webp');
        if ($webpSupported) {
            $outputPath = pathinfo($outputPath, PATHINFO_DIRNAME) . '/' . pathinfo($outputPath, PATHINFO_FILENAME) . '.webp';
        }

        // Resize the image if it exceeds the max dimensions
        if (($maxWidth && $image->width() > $maxWidth) || ($maxHeight && $image->height() > $maxHeight)) {
            if($keepRatio){
                $image->scale($maxWidth, $maxHeight);
            }else{
                $image->resize($maxWidth, $maxHeight);
            }
        }

        // Get the current image size in kilobytes
        $currentSizeKB = filesize($imagePath) / 1024;

        // If the image is already smaller than the target size, no need to compress
        if ($currentSizeKB <= $targetSizeKB) {
            // If an output path is provided, save the WebP directly
            if ($outputPath) {
                if ($webpSupported) {
                    $encodedImage = $image->encode(new WebpEncoder());
                    Storage::disk($disk)->put($outputPath, $encodedImage); // Save as WebP with 90% quality
                } else {
                    $encodedImage = $image->encode();
                    Storage::disk($disk)->put($outputPath, $encodedImage); // Save as original format (e.g., JPEG/PNG)
                }
                return $outputPath;
            }
            return $encodedImage;
        }

        // Set initial quality for the WebP image (you can adjust this to balance quality vs. size)
        $quality = 90;

        // If WebP is supported, try to reduce the size to target by converting to WebP
        if ($webpSupported) {
            while ($currentSizeKB > $targetSizeKB && $quality > 20) {
                // Encode the image in WebP format with the current quality
                $encodedImage = $image->encode(new WebpEncoder(quality: $quality));

                // Check the size of the encoded image in memory
                $currentSizeKB = strlen($encodedImage) / 1024; // in KB

                // Reduce the quality further
                $quality -= 5;
            }

            // If an output path is provided, save the final WebP encoded image to that location
            if ($outputPath) {
                Storage::disk($disk)->put($outputPath, $encodedImage);
                return $outputPath;
            }
        } else {
            // If WebP is not supported, just save it in the original format and reduce size if necessary
            while ($currentSizeKB > $targetSizeKB && $quality > 20) {
                // Encode the image in its original format (e.g., JPEG or PNG) with the current quality
                $encodedImage = $image->encode(new AutoEncoder(quality: $quality));

                // Check the size of the encoded image in memory
                $currentSizeKB = strlen($encodedImage) / 1024; // in KB

                // Reduce the quality further
                $quality -= 10;
            }

            // If an output path is provided, save the final encoded image to that location
            if ($outputPath) {
                Storage::disk($disk)->put($outputPath, $encodedImage);
                return $outputPath;
            }
        }

        return $encodedImage;
    }
}
