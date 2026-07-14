<?php

namespace App\Helpers;

/**
 * Resolve a user-supplied filename/path into an absolute path inside a known
 * storage directory, rejecting path traversal attempts (e.g. "../") and
 * ensuring the resolved real path is still contained within the base dir.
 *
 * @param  string  $baseDir  Absolute path to the allowed directory.
 * @param  string  $userPath User-supplied filename or relative path.
 * @return string|null Absolute path if safe and exists, null otherwise.
 */
function resolveSafePath(string $baseDir, string $userPath): ?string
{
    // Reject obvious traversal patterns and NUL bytes up front.
    if ($userPath === '' || str_contains($userPath, "\0")) {
        return null;
    }

    // Normalize the base directory to a real, canonical path when it exists.
    $realBase = is_dir($baseDir) ? realpath($baseDir) : rtrim($baseDir, DIRECTORY_SEPARATOR);

    // Build the candidate path and resolve it. realpath() collapses "../",
    // symlinks, and redundant separators, returning false when the file
    // does not exist. We then verify the canonical path starts with the
    // canonical base directory so traversal outside the base is impossible.
    $candidate = $realBase . DIRECTORY_SEPARATOR . ltrim($userPath, DIRECTORY_SEPARATOR);

    // For files that exist, realpath() gives us the canonical path.
    $resolved = realpath($candidate);

    if ($resolved === false) {
        return null;
    }

    // Ensure the resolved path is strictly inside the base directory.
    if (!str_starts_with($resolved . DIRECTORY_SEPARATOR, $realBase . DIRECTORY_SEPARATOR)) {
        return null;
    }

    return $resolved;
}

/**
 * Build a conservative CORS header set for file-serving routes.
 *
 * Instead of reflecting any Origin or using a wildcard, we allow only origins
 * configured via the FILE_CORS_ORIGINS env var (comma-separated). When none is
 * configured we fall back to the APP_URL host so the route is never wide open.
 *
 * @return array<string,string>
 */
function fileCorsHeaders(): array
{
    $allowed = array_filter(array_map('trim', explode(',', (string) env('FILE_CORS_ORIGINS', env('APP_URL', '')))));

    $origin = request()->headers->get('Origin');
    $allowOrigin = '';
    if ($origin && in_array($origin, $allowed, true)) {
        $allowOrigin = $origin;
    } elseif (in_array('*', $allowed, true)) {
        // Explicit opt-in via env only; never the default.
        $allowOrigin = '*';
    }

    return [
        'Access-Control-Allow-Origin'      => $allowOrigin,
        'Access-Control-Allow-Methods'     => 'GET, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, ngrok-skip-browser-warning',
        'Access-Control-Allow-Credentials' => 'true',
        'Vary'                             => 'Origin',
    ];
}
