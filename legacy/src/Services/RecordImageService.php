<?php
declare(strict_types=1);

namespace Krate\Services;

use Exception;
use InvalidArgumentException;
use RuntimeException;

class RecordImageService
{
    private const UPLOAD_SUBDIR = 'uploads';
    private const MAX_FILE_BYTES = 5242880;
    private const MIME_TO_EXTENSION = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly string $publicRootPath
    ) {
    }

    /**
     * Store an uploaded image and return the relative web path.
     *
     * @param array<string, mixed> $file
     * @param string $prefix
     * @return string|null
     * @throws InvalidArgumentException|RuntimeException
     */
    public function storeUploadedImage(array $file, string $prefix = 'record'): ?string
    {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->getUploadErrorMessage($error));
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException('Invalid uploaded file.');
        }

        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
            throw new InvalidArgumentException('Uploaded image must be between 1 byte and 5 MB.');
        }

        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo === false || empty($imageInfo['mime'])) {
            throw new InvalidArgumentException('Uploaded file must be a valid image.');
        }

        $mimeType = $this->detectMimeType($tmpName);
        $extension = self::MIME_TO_EXTENSION[$mimeType] ?? null;
        if ($extension === null) {
            throw new InvalidArgumentException('Only JPEG, PNG, GIF, and WebP images are allowed.');
        }

        $uploadDir = $this->publicRootPath . DIRECTORY_SEPARATOR . self::UPLOAD_SUBDIR;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Unable to create upload directory.');
        }

        $safePrefix = $this->normalizePrefix($prefix);
        try {
            $randomSuffix = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            throw new RuntimeException('Unable to generate a safe upload filename.', 0, $e);
        }

        $filename = sprintf('%s_%s.%s', $safePrefix, $randomSuffix, $extension);

        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmpName, $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return self::UPLOAD_SUBDIR . '/' . $filename;
    }

    /**
     * Remove a stored image if it exists under the managed upload directory.
     */
    public function deleteUploadedImage(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if (!preg_match('#^' . self::UPLOAD_SUBDIR . '/[a-z0-9_-]+_[a-f0-9]{32}\.(?:jpg|png|gif|webp)$#', $normalizedPath)) {
            return;
        }

        $fullPath = $this->publicRootPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    private function normalizePrefix(string $prefix): string
    {
        $prefix = strtolower(trim($prefix));
        $prefix = preg_replace('/[^a-z0-9_-]+/', '-', $prefix) ?? '';
        $prefix = trim($prefix, '-_');

        return $prefix !== '' ? $prefix : 'record';
    }

    private function getUploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Uploaded image is too large.',
            UPLOAD_ERR_PARTIAL => 'Uploaded image was only partially received.',
            UPLOAD_ERR_NO_FILE => 'No file uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload directory.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write uploaded image to disk.',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
            default => 'Unknown upload error.',
        };
    }

    private function detectMimeType(string $filePath): string
    {
        if (!function_exists('finfo_open') || !function_exists('finfo_file') || !function_exists('finfo_close')) {
            throw new RuntimeException('File type inspection is unavailable.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException('Unable to inspect uploaded file type.');
        }

        try {
            $mimeType = finfo_file($finfo, $filePath);
        } finally {
            finfo_close($finfo);
        }

        if (!is_string($mimeType) || $mimeType === '') {
            throw new InvalidArgumentException('Uploaded file must be a valid image.');
        }

        return $mimeType;
    }
}
