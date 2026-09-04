<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Cake\Http\Exception\BadRequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * When an upload exceeds PHP's post_max_size, PHP empties $_POST/$_FILES
 * before Cake sees the request — including the CSRF token — which then
 * surfaces as a confusing InvalidCsrfTokenException. Catch that case
 * first and return a clear "file too large" error instead.
 */
class RejectOversizedUploadMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request->is(['post', 'put', 'patch'])) {
            return $handler->handle($request);
        }

        $contentType = $request->getHeaderLine('Content-Type');
        if (!str_contains(strtolower($contentType), 'multipart/form-data')) {
            return $handler->handle($request);
        }

        $contentLength = (int)$request->getHeaderLine('Content-Length');
        if ($contentLength > 0 && $_POST === [] && $_FILES === []) {
            $limit = ini_get('post_max_size') ?: '2M';
            throw new BadRequestException(
                __('The uploaded file is too large for the server (limit {0}). Please choose a smaller image.', $limit)
            );
        }

        return $handler->handle($request);
    }
}
