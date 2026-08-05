<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Utility\Text;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Shared handling for image uploads on admin forms (Banners, News...).
 * The uploaded file is validated and moved outside the entity marshaller —
 * a Psr UploadedFileInterface can't be cast to a plain string column, so it
 * must never reach patchEntity()/newEntity().
 */
trait ImageUploadTrait
{
    private const IMAGE_UPLOAD_ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const IMAGE_UPLOAD_MAX_SIZE = 5 * 1024 * 1024;

    private function imageUploadError(?UploadedFileInterface $file, bool $required): ?string
    {
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $required ? __('Please choose an image.') : null;
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            return __('The uploaded file could not be processed.');
        }

        if (!in_array($file->getClientMediaType(), self::IMAGE_UPLOAD_ALLOWED_TYPES, true)) {
            return __('The image must be a JPEG, PNG or WEBP file.');
        }

        if ($file->getSize() > self::IMAGE_UPLOAD_MAX_SIZE) {
            return __('The image must be smaller than 5 MB.');
        }

        return null;
    }

    /**
     * Moves a validated uploaded image into webroot/img/{subdir} and returns
     * the generated filename. The extension is derived from the validated
     * mime type, never from the client-supplied filename.
     */
    private function storeImageUpload(UploadedFileInterface $file, string $subdir): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $extension = $extensions[$file->getClientMediaType()] ?? 'jpg';

        $targetDir = WWW_ROOT . 'img' . DS . $subdir;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = Text::uuid() . '.' . $extension;
        $file->moveTo($targetDir . DS . $filename);

        return $filename;
    }

    private function deleteImageUpload(string $subdir, string $filename): void
    {
        $file = WWW_ROOT . 'img' . DS . $subdir . DS . $filename;
        if (is_file($file)) {
            unlink($file);
        }
    }
}