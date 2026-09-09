<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Utility\Text;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Shared handling for document (PDF) uploads on admin forms — same shape as
 * ImageUploadTrait, generalized to a single allowed mime type and a
 * separate storage root (webroot/files/ instead of webroot/img/) so
 * documents don't mix in with photos on disk. The uploaded file is
 * validated and moved outside the entity marshaller — a Psr
 * UploadedFileInterface can't be cast to a plain string column, so it must
 * never reach patchEntity()/newEntity().
 */
trait FileUploadTrait
{
    private const FILE_UPLOAD_ALLOWED_TYPES = ['application/pdf'];
    private const FILE_UPLOAD_MAX_SIZE = 10 * 1024 * 1024;

    private function fileUploadError(?UploadedFileInterface $file, bool $required): ?string
    {
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $required ? __('Please choose a PDF file.') : null;
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            return __('The uploaded file could not be processed.');
        }

        if (!in_array($file->getClientMediaType(), self::FILE_UPLOAD_ALLOWED_TYPES, true)) {
            return __('The file must be a PDF.');
        }

        if ($file->getSize() > self::FILE_UPLOAD_MAX_SIZE) {
            return __('The file must be smaller than 10 MB.');
        }

        return null;
    }

    /**
     * Moves a validated uploaded PDF into webroot/files/{subdir} and
     * returns the generated filename. The extension is fixed ('pdf'),
     * never taken from the client-supplied filename.
     */
    private function storeFileUpload(UploadedFileInterface $file, string $subdir): string
    {
        $targetDir = WWW_ROOT . 'files' . DS . $subdir;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = Text::uuid() . '.pdf';
        $file->moveTo($targetDir . DS . $filename);

        return $filename;
    }

    private function deleteFileUpload(string $subdir, ?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $file = WWW_ROOT . 'files' . DS . $subdir . DS . $filename;
        if (is_file($file)) {
            unlink($file);
        }
    }
}
