<?php

/**
 * Tutarı para birimi formatına çevirir (Örn: 1500.5 -> $1,500.50)
 */
function formatCurrency($amount, $currencySymbol = '$') {
    if (!is_numeric($amount)) return $currencySymbol . '0.00';
    return $currencySymbol . number_format($amount, 2, '.', ',');
}

/**
 * Bayt cinsinden dosya boyutunu okunabilir formata çevirir (KB, MB, GB)
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}

/**
 * Uzun metinleri belirli bir karakter sınırında keser ve sonuna "..." ekler
 */
function truncateText($text, $maxLength = 100) {
    if (mb_strlen($text) > $maxLength) {
        return mb_substr($text, 0, $maxLength) . '...';
    }
    return $text;
}

/**
 * Veritabanı tarihini (Y-m-d H:i:s) daha okunabilir yapar (Örn: 12 Mayıs 2026)
 */
function formatDate($dateString) {
    if (empty($dateString)) return 'Bilinmiyor';
    $timestamp = strtotime($dateString);
    // Türkçe aylar için formatlama (Basit versiyon)
    $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    $monthIndex = (int)date('n', $timestamp);
    
    return date('d', $timestamp) . ' ' . $months[$monthIndex] . ' ' . date('Y', $timestamp);
}
?>