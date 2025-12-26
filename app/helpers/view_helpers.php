<?php

/**
 * Helper functions cho views
 * Load file này trong BaseController để views có thể dùng
 */

/**
 * Lấy URL hiển thị file (Cloudinary hoặc local)
 * 
 * @param string $fileInfo - Tên file (local) hoặc JSON string (Cloudinary)
 * @return string
 */
function getFileUrl($fileInfo)
{
    if (empty($fileInfo)) {
        return '';
    }

    // Nếu là Cloudinary file (JSON format)
    $decoded = json_decode($fileInfo, true);
    if ($decoded && isset($decoded['url'])) {
        return $decoded['url'];
    }

    // Local file
    return '/Quan_ly_trung_tam/public/uploads/' . $fileInfo;
}

/**
 * Kiểm tra xem file có phải từ Cloudinary không
 */
function isCloudinaryFile($fileInfo)
{
    if (empty($fileInfo)) {
        return false;
    }
    
    $decoded = json_decode($fileInfo, true);
    return $decoded && isset($decoded['id']) && isset($decoded['url']);
}
