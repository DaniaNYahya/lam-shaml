<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HttpException;

class ImageUploadService
{
    public function store(?array $file): ?array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new HttpException('Image upload failed', 422);
        }

        $config = require BASE_PATH . '/config/config.php';
        if ($file['size'] > $config['uploads']['max_bytes']) {
            throw new HttpException('Image is too large', 422);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            throw new HttpException('Unsupported image type', 422);
        }

        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $target = rtrim($config['uploads']['dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new HttpException('Could not store image', 500);
        }

        return [
            'file_type' => $mime,
            'file_url' => $config['uploads']['public_prefix'] . $name,
        ];
    }
}
