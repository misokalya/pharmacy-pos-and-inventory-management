<?php

/**
 * Handles a single image upload.
 *
 * @param array|null $file  $_FILES['field']
 * @param string     $subdir  e.g. 'products'
 * @return array{path:?string,error:?string}
 */
function handle_image_upload(?array $file, string $subdir = 'products'): array
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null]; // no file — caller keeps old value
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Upload failed (code ' . $file['error'] . ').'];
    }

    // Max 2 MB
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['path' => null, 'error' => 'Image must be 2 MB or smaller.'];
    }

    // Validate MIME from actual file contents
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']) ?: '';
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        return ['path' => null, 'error' => 'Only JPG, PNG, WEBP or GIF images are allowed.'];
    }

    $ext      = $allowed[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dir      = APP_PATH . '/public/uploads/' . $subdir;

    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        return ['path' => null, 'error' => 'Could not create upload directory.'];
    }

    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['path' => null, 'error' => 'Failed to store uploaded file.'];
    }

    // Return web-relative path (no leading slash)
    return ['path' => 'uploads/' . $subdir . '/' . $filename, 'error' => null];
}

/**
 * Deletes a previously stored image (safe: only within /public/uploads/).
 */
function delete_upload(?string $relativePath): void
{
    if (!$relativePath) return;
    $relativePath = ltrim($relativePath, '/');
    if (!str_starts_with($relativePath, 'uploads/')) return;

    $full = APP_PATH . '/public/' . $relativePath;
    $real = realpath($full);
    $base = realpath(APP_PATH . '/public/uploads');
    if ($real && $base && str_starts_with($real, $base) && is_file($real)) {
        @unlink($real);
    }
}