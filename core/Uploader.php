<?php
/**
 * STONE ENERGY INT'L LTD
 * Secure Media & Document Upload Service
 */

declare(strict_types=1);

class Uploader {
    private const ALLOWED_MIMES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'pdf'  => 'application/pdf',
    ];

    private const MAX_SIZE = 10 * 1024 * 1024; // 10 Megabytes

    /**
     * Process file upload securely
     */
    public static function upload(array $file, string $altText = ''): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'Invalid file upload parameter.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'error' => 'No file was uploaded.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'error' => 'Exceeded maximum permitted file upload size.'];
            default:
                return ['success' => false, 'error' => 'An unknown file upload error occurred.'];
        }

        if ($file['size'] > self::MAX_SIZE) {
            return ['success' => false, 'error' => 'File exceeds the 10MB limit.'];
        }

        // Validate MIME type with PHP finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Check extension match and MIME match
        if (!array_key_exists($extension, self::ALLOWED_MIMES) || self::ALLOWED_MIMES[$extension] !== $realMime) {
            return ['success' => false, 'error' => 'Unsupported file format. Permitted types: JPG, PNG, WEBP, PDF.'];
        }

        // Ensure upload directory exists and is writable
        if (!is_dir(UPLOADS_PATH)) {
            mkdir(UPLOADS_PATH, 0755, true);
        }

        // Generate unguessable randomized filename
        $safeFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = UPLOADS_PATH . $safeFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file to destination directory.'];
        }

        // Path relative to root
        $relativePath = 'assets/uploads/' . $safeFilename;

        // Record in media table
        $mediaId = 0;
        try {
            $mediaId = Database::insert('media', [
                'filename' => $safeFilename,
                'original_filename' => htmlspecialchars($originalName, ENT_QUOTES, 'UTF-8'),
                'mime_type' => $realMime,
                'file_size' => (int)$file['size'],
                'file_path' => $relativePath,
                'alt_text' => htmlspecialchars($altText, ENT_QUOTES, 'UTF-8'),
                'uploaded_by' => Auth::id()
            ]);
            Audit::log('upload_media', 'media', (string)$mediaId, "Uploaded media file {$safeFilename}");
        } catch (Exception $e) {
            error_log("Media record insertion failed: " . $e->getMessage());
        }

        return [
            'success' => true,
            'media_id' => $mediaId,
            'filename' => $safeFilename,
            'file_path' => $relativePath,
            'full_url' => url($relativePath),
            'mime_type' => $realMime,
            'file_size' => $file['size']
        ];
    }

    /**
     * Delete media record and remove physical file
     */
    public static function delete(int $mediaId): bool {
        $media = Database::fetchOne("SELECT * FROM `media` WHERE `id` = :id", [':id' => $mediaId]);
        if (!$media) {
            return false;
        }

        $fullPath = ROOT_PATH . str_replace('/', DIRECTORY_SEPARATOR, $media['file_path']);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        Database::delete('media', '`id` = :id', [':id' => $mediaId]);
        Audit::log('delete_media', 'media', (string)$mediaId, "Deleted media file {$media['filename']}");
        return true;
    }
}
