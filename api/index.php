<?php

declare(strict_types=1);

/**
 * Vercel entrypoint for Laravel.
 *
 * - Serves files from /public directly when requested.
 * - Falls back to Laravel's public/index.php for all dynamic routes.
 */

$publicPath = realpath(__DIR__.'/../public');
$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$path = urldecode((string) parse_url($requestUri, PHP_URL_PATH));

if ($publicPath !== false && $path !== '' && $path !== '/') {
    $candidate = realpath($publicPath.$path);

    $isInsidePublic = $candidate !== false
        && str_starts_with($candidate, $publicPath.DIRECTORY_SEPARATOR);

    if ($isInsidePublic && is_file($candidate)) {
        $mime = mime_content_type($candidate) ?: 'application/octet-stream';
        header('Content-Type: '.$mime);
        readfile($candidate);
        exit;
    }
}

if ($publicPath !== false) {
    $_SERVER['DOCUMENT_ROOT'] = $publicPath;
    $_SERVER['SCRIPT_FILENAME'] = $publicPath.'/index.php';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

require __DIR__.'/../public/index.php';
