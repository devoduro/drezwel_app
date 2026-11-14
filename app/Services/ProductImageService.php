<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Saves product photos. Every upload is decoded and re-encoded as a JPEG no wider than 1200px,
 * which strips metadata and any non-image payload, and keeps pages fast on phones.
 */
class ProductImageService
{
    public const MAX_PER_PRODUCT = 8;
    private const MAX_EDGE = 1200;

    public function add(Product $product, UploadedFile $file): ProductImage
    {
        if ($product->images()->count() >= self::MAX_PER_PRODUCT) {
            throw ValidationException::withMessages(['images' => 'A product can have at most ' . self::MAX_PER_PRODUCT . ' photos.']);
        }

        $path = $this->store($file, 'products', self::MAX_EDGE, 'images');

        return $product->images()->create(['path' => $path, 'sort_order' => (int) $product->images()->max('sort_order') + 1]);
    }

    /**
     * Decode, shrink and re-encode an upload as a JPEG, then save it in public/uploads/{dir}.
     * Returns the stored path (relative to public/uploads). Used for product photos and the website's hero picture.
     */
    public function store(UploadedFile $file, string $dir, int $maxEdge, string $errorField = 'images'): string
    {
        $img = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $img) {
            throw ValidationException::withMessages([$errorField => "\"{$file->getClientOriginalName()}\" is not a readable image."]);
        }

        [$w, $h] = [imagesx($img), imagesy($img)];
        $scale = min(1, $maxEdge / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $canvas = imagecreatetruecolor($nw, $nh);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255)); // transparent PNGs get a white background
        imagecopyresampled($canvas, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        imagejpeg($canvas, null, 85);
        $bytes = ob_get_clean();
        imagedestroy($img);
        imagedestroy($canvas);

        $path = $dir . '/' . Str::random(24) . '.jpg';
        Storage::disk('uploads')->put($path, $bytes);

        return $path;
    }

    public function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('uploads')->delete($path);
        }
    }

    public function delete(ProductImage $image): void
    {
        Storage::disk('uploads')->delete($image->path);
        $image->delete();
    }

    /** Make this photo the first one shown. */
    public function makeMain(ProductImage $image): void
    {
        $min = (int) $image->product->images()->min('sort_order');
        $image->update(['sort_order' => $min - 1]);
    }
}
