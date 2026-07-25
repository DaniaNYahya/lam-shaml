<?php
declare(strict_types=1);

namespace LamShaml\Services;

use LamShaml\Core\Config;
use LamShaml\Core\HttpException;

final class ImageUploadService
{
    public function store(?array $file): ?array
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new HttpException(422, 'تعذر رفع الصورة.');
        }
        if (($file['size'] ?? 0) > Config::get('upload_max_bytes')) {
            throw new HttpException(422, 'حجم الصورة أكبر من الحد المسموح.');
        }
        $info = @getimagesize($file['tmp_name']);
        if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new HttpException(422, 'يسمح برفع صور JPG أو PNG أو WEBP فقط.');
        }
        $source = match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'image/png' => imagecreatefrompng($file['tmp_name']),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file['tmp_name']) : false,
            default => false,
        };
        if (!$source) {
            throw new HttpException(422, 'امتداد GD لا يدعم هذه الصورة في البيئة الحالية.');
        }
        [$width, $height] = $info;
        $max = 1280;
        $ratio = min(1, $max / max($width, $height));
        $newWidth = max(1, (int)round($width * $ratio));
        $newHeight = max(1, (int)round($height * $ratio));
        $dest = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $dir = BASE_PATH . '/public/uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = bin2hex(random_bytes(16)) . '.jpg';
        imagejpeg($dest, $dir . '/' . $name, 76);
        imagedestroy($source);
        imagedestroy($dest);
        return ['file_type' => 'image/jpeg', 'file_path' => 'uploads/' . $name];
    }
}
