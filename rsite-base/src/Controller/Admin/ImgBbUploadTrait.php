<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Core\Configure;
use Cake\Http\Client;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Uploads gallery photos to ImgBB (https://api.imgbb.com) instead of
 * webroot/img/ — a public photo gallery can accumulate far more images
 * than this host's disk quota comfortably holds, so the actual image bytes
 * live on ImgBB and only the returned URL (plus its delete_url, needed to
 * remove the image later) are stored in the galleries table.
 *
 * File validation (type/size) still goes through ImageUploadTrait's
 * imageUploadError() — only where the validated bytes end up differs.
 */
trait ImgBbUploadTrait
{
    private const IMGBB_UPLOAD_URL = 'https://api.imgbb.com/1/upload';

    /**
     * @return array{url: string, deleteUrl: string}
     */
    private function uploadToImgBb(UploadedFileInterface $file): array
    {
        $apiKey = Configure::read('ImgBB.apiKey');
        if (!$apiKey) {
            throw new \RuntimeException(
                __('Image hosting is not configured (missing ImgBB.apiKey in app_local.php).'),
            );
        }

        // No 'type' option here — Cake\Http\Client only recognizes the
        // 'json'/'xml' type aliases (or a full mime-type string containing
        // '/'), so 'form' throws "Unknown type alias `form`." Leaving it
        // unset makes Client encode this plain array as multipart/form-data
        // via Cake\Http\FormData automatically, which is exactly what
        // ImgBB's upload endpoint expects.
        $client = new Client();
        $response = $client->post(self::IMGBB_UPLOAD_URL, [
            'key' => $apiKey,
            'image' => base64_encode($file->getStream()->getContents()),
        ], ['timeout' => 20]);

        if (!$response->isOk()) {
            throw new \RuntimeException(__('The image could not be uploaded to the image host.'));
        }

        $data = $response->getJson();
        $url = $data['data']['url'] ?? null;
        $deleteUrl = $data['data']['delete_url'] ?? null;

        if (!is_string($url) || $url === '') {
            throw new \RuntimeException(__('The image host returned an unexpected response.'));
        }

        return ['url' => $url, 'deleteUrl' => is_string($deleteUrl) ? $deleteUrl : ''];
    }

    /**
     * Best-effort cleanup — losing an orphaned image on ImgBB's side isn't
     * worth failing the admin's own delete/replace action over.
     */
    private function deleteFromImgBb(string $deleteUrl): void
    {
        if ($deleteUrl === '') {
            return;
        }

        try {
            (new Client())->get($deleteUrl, [], ['timeout' => 10]);
        } catch (\Throwable) {
            // Ignore — the database row is already gone/updated either way.
        }
    }
}
